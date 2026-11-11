<?php
/**
 * Media management routes — local file upload and gallery listing.
 *
 * POST /wp-json/pcs/v1/admin/media/upload
 * GET  /wp-json/pcs/v1/admin/media
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Media_Routes extends PCS_Base_Routes {

    public function register(): void {
        register_rest_route( $this->namespace, '/admin/media/upload', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'upload_media' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/admin/media', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'list_media' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );
    }

    /**
     * Upload an image file locally and register it as a WordPress attachment.
     */
    public function upload_media( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $file_key = null;

        if ( ! empty( $_FILES['file']['name'] ) ) {
            $file_key = 'file';
        } elseif ( ! empty( $_FILES['image']['name'] ) ) {
            $file_key = 'image';
        } else {
            $files = $request->get_file_params();
            if ( ! empty( $files['file']['name'] ) ) {
                $file_key = 'file';
            } elseif ( ! empty( $files['image']['name'] ) ) {
                $file_key = 'image';
            }
        }

        if ( ! $file_key || empty( $_FILES[ $file_key ]['name'] ) ) {
            return $this->error( 'pcs_no_file', 'Nu a fost selectat niciun fișier pentru încărcare.', 400 );
        }

        $file = $_FILES[ $file_key ];

        // Validate upload error code
        if ( ! empty( $file['error'] ) ) {
            $error_message = match ( (int) $file['error'] ) {
                UPLOAD_ERR_INI_SIZE   => 'Fișierul depășește limita maximă permisă de server.',
                UPLOAD_ERR_FORM_SIZE  => 'Fișierul depășește dimensiunea maximă specificată.',
                UPLOAD_ERR_PARTIAL    => 'Fișierul a fost încărcat doar parțial.',
                UPLOAD_ERR_NO_FILE    => 'Nu a fost trimis niciun fișier.',
                UPLOAD_ERR_NO_TMP_DIR => 'Lipsește directorul temporar pe server.',
                UPLOAD_ERR_CANT_WRITE => 'Nu s-a putut scrie fișierul pe disc.',
                default               => 'Eroare la încărcarea fișierului pe server.',
            };
            return $this->error( 'pcs_upload_error', $error_message, 400 );
        }

        // Validate image mime type and extension
        $file_name = sanitize_file_name( $file['name'] );
        $file_info = wp_check_filetype( $file_name );
        $ext       = strtolower( $file_info['ext'] ?? '' );
        $mime      = strtolower( $file_info['type'] ?? '' );

        $allowed_extensions = [ 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg' ];
        $allowed_mimes      = [
            'image/jpeg',
            'image/png',
            'image/gif',
            'image/webp',
            'image/svg+xml',
        ];

        if ( ! in_array( $ext, $allowed_extensions, true ) || ( $mime && ! in_array( $mime, $allowed_mimes, true ) ) ) {
            return $this->error(
                'pcs_invalid_image',
                'Formatul fișierului nu este suportat. Sunt permise doar imagini (JPG, JPEG, PNG, GIF, WEBP, SVG).',
                400
            );
        }

        // Include WordPress core admin file helpers
        if ( ! function_exists( 'media_handle_upload' ) ) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        // Optional post_id to associate attachment with (e.g. product ID)
        $post_id = (int) ( $request->get_param( 'post_id' ) ?? 0 );

        // Handle upload and attachment creation in local WordPress uploads directory
        $attachment_id = media_handle_upload( $file_key, $post_id );

        if ( is_wp_error( $attachment_id ) ) {
            return $this->error( 'pcs_upload_failed', $attachment_id->get_error_message(), 500 );
        }

        // Set alt text if provided
        $alt_text = $request->get_param( 'alt' );
        if ( $alt_text ) {
            update_post_meta( $attachment_id, '_wp_attachment_image_alt', sanitize_text_field( $alt_text ) );
        }

        $url  = wp_get_attachment_url( $attachment_id );
        $meta = wp_get_attachment_metadata( $attachment_id );

        $response_data = [
            'id'        => $attachment_id,
            'url'       => $url,
            'src'       => $url,
            'title'     => get_the_title( $attachment_id ),
            'alt'       => get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) ?: '',
            'mime_type' => get_post_mime_type( $attachment_id ),
            'width'     => $meta['width'] ?? null,
            'height'    => $meta['height'] ?? null,
        ];

        return $this->success( $response_data, 201 );
    }

    /**
     * List existing image attachments from the media library.
     */
    public function list_media( WP_REST_Request $request ): WP_REST_Response|WP_Error {
        $per_page = min( (int) ( $request->get_param( 'per_page' ) ?? 24 ), 100 );
        $page     = max( (int) ( $request->get_param( 'page' ) ?? 1 ), 1 );
        $search   = sanitize_text_field( $request->get_param( 'search' ) ?? '' );

        $query_args = [
            'post_type'      => 'attachment',
            'post_status'    => 'inherit',
            'post_mime_type' => [ 'image/jpeg', 'image/png', 'image/gif', 'image/webp', 'image/svg+xml' ],
            'posts_per_page' => $per_page,
            'paged'          => $page,
            'orderby'        => 'date',
            'order'          => 'DESC',
        ];

        if ( ! empty( $search ) ) {
            $query_args['s'] = $search;
        }

        $query = new WP_Query( $query_args );
        $items = [];

        foreach ( $query->posts as $post ) {
            $url  = wp_get_attachment_url( $post->ID );
            $meta = wp_get_attachment_metadata( $post->ID );
            $items[] = [
                'id'        => $post->ID,
                'url'       => $url,
                'src'       => $url,
                'title'     => $post->post_title,
                'alt'       => get_post_meta( $post->ID, '_wp_attachment_image_alt', true ) ?: '',
                'mime_type' => $post->post_mime_type,
                'width'     => $meta['width'] ?? null,
                'height'    => $meta['height'] ?? null,
                'date'      => $post->post_date,
            ];
        }

        return $this->success( [
            'items'       => $items,
            'total'       => (int) $query->found_posts,
            'total_pages' => (int) $query->max_num_pages,
            'page'        => $page,
            'per_page'    => $per_page,
        ] );
    }
}

