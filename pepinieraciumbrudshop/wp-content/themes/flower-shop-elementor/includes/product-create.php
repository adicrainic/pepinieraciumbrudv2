<?php

class Whizzie {

	public function __construct() {
		$this->init();
	}

	public function init()
	{
	
	}

	public static function flower_shop_elementor_setup_widgets(){

	$flower_shop_elementor_product_image_gallery = array();
	$flower_shop_elementor_product_ids = array();

	$flower_shop_elementor_product_category= array(
		'Product Category'       => array(
			'Blossom Bliss Bouquet 01',
			'Blossom Bliss Bouquet 02',
			'Blossom Bliss Bouquet 03',
			'Blossom Bliss Bouquet 04',
		),
	);

	$flower_shop_elementor_k = 1;
	foreach ( $flower_shop_elementor_product_category as $flower_shop_elementor_product_cats => $flower_shop_elementor_products_name ) { 
	// Insert porduct cats Start
	$content = 'This is sample product category';
	$flower_shop_elementor_parent_category	=	wp_insert_term(
	$flower_shop_elementor_product_cats, // the term
	'product_cat', // the taxonomy
		array(
		'description'=> $content,
		'slug' => str_replace( ' ', '-', $flower_shop_elementor_product_cats)
		)
	);

// -------------- create subcategory END -----------------

	$flower_shop_elementor_n=1;
	// create Product START
	foreach ( $flower_shop_elementor_products_name as $key => $flower_shop_elementor_product_title ) {
	$content = '
		<div class="main_content">
		<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>
		</div>';

	// Create post object
	$flower_shop_elementor_my_post = array(
		'post_title'    => wp_strip_all_tags( $flower_shop_elementor_product_title ),
		'post_content'  => $content,
		'post_status'   => 'publish',
		'post_type'     => 'product',
		'post_category' => [$flower_shop_elementor_parent_category['term_id']]
	);

	// Insert the post into the database

	$flower_shop_elementor_uqpost_id = wp_insert_post($flower_shop_elementor_my_post);
	wp_set_object_terms( $flower_shop_elementor_uqpost_id, str_replace( ' ', '-', $flower_shop_elementor_product_cats), 'product_cat', true );

	$flower_shop_elementor_product_price = array('$20.46','$20.46','$20.46','$20.46');
	$flower_shop_elementor_product_regular_price = array('$20.46','$20.46','$20.46','$20.46');
	// $flower_shop_elementor_product_sale_price = array('49','49','49','49');
	
	update_post_meta( $flower_shop_elementor_uqpost_id, '_regular_price', $flower_shop_elementor_product_regular_price[$flower_shop_elementor_n-1] );
	update_post_meta( $flower_shop_elementor_uqpost_id, '_price', $flower_shop_elementor_product_price[$flower_shop_elementor_n-1] );
	// update_post_meta( $flower_shop_elementor_uqpost_id, '_sale_price', $flower_shop_elementor_product_sale_price[$flower_shop_elementor_n-1] );
	array_push( $flower_shop_elementor_product_ids,  $flower_shop_elementor_uqpost_id );

	// Now replace meta w/ new updated value array
	$flower_shop_elementor_image_url = get_template_directory_uri().'/assets/images/product/'.$flower_shop_elementor_product_cats.'/' . str_replace(' ', '_', strtolower($flower_shop_elementor_product_title)).'.png';
	$flower_shop_elementor_image_name  = $flower_shop_elementor_product_title.'.png';
	$flower_shop_elementor_upload_dir = wp_upload_dir();
	// Set upload folder
	$flower_shop_elementor_image_data = file_get_contents(esc_url($flower_shop_elementor_image_url));
	// Get image data
	$unique_file_name = wp_unique_filename($flower_shop_elementor_upload_dir['path'], $flower_shop_elementor_image_name);
	// Generate unique name
	$flower_shop_elementor_filename = basename($unique_file_name);
	// Create image file name
	// Check folder permission and define file location
	if (wp_mkdir_p($flower_shop_elementor_upload_dir['path'])) {
	$flower_shop_elementor_file = $flower_shop_elementor_upload_dir['path'].'/'.$flower_shop_elementor_filename;
	} else {
	$flower_shop_elementor_file = $flower_shop_elementor_upload_dir['basedir'].'/'.$flower_shop_elementor_filename;
	}
	
	file_put_contents($flower_shop_elementor_file, $flower_shop_elementor_image_data);
	// Check image file type
	$wp_filetype = wp_check_filetype($flower_shop_elementor_filename, null);
	// Set attachment data
	$attachment = array(
		'post_mime_type' => $wp_filetype['type'],
		'post_title'     => sanitize_file_name($flower_shop_elementor_filename),
		'post_type'      => 'product',
		'post_status'    => 'inherit',
	);

	// Create the attachment
	$flower_shop_elementor_attach_id = wp_insert_attachment($attachment, $flower_shop_elementor_file, $flower_shop_elementor_uqpost_id);

	// Define attachment metadata
	$attach_data = wp_generate_attachment_metadata($flower_shop_elementor_attach_id, $flower_shop_elementor_file);

	// Assign metadata to attachment
	wp_update_attachment_metadata($flower_shop_elementor_attach_id, $attach_data);
	if ( count( $flower_shop_elementor_product_image_gallery ) < 3 ) {
		array_push( $flower_shop_elementor_product_image_gallery, $flower_shop_elementor_attach_id );
	}
	// // And finally assign featured image to post
	set_post_thumbnail($flower_shop_elementor_uqpost_id, $flower_shop_elementor_attach_id);
	++$flower_shop_elementor_n;
	}
	// Create product END
	++$flower_shop_elementor_k;
	}
	// Add Gallery in first simple product and second variable product START
	$flower_shop_elementor_product_image_gallery = implode( ',', $flower_shop_elementor_product_image_gallery );
	foreach ( $flower_shop_elementor_product_ids as $flower_shop_elementor_product_id ) {
	update_post_meta( $flower_shop_elementor_product_id, 'flower_shop_elementor_product_image_gallery', $flower_shop_elementor_product_image_gallery );
	}

	/* Create Menu */
            $flower_shop_elementor_menuname  = 'Main Menus';
            $flower_shop_elementor_location  = 'main-menu';

            $flower_shop_elementor_menu = wp_get_nav_menu_object( $flower_shop_elementor_menuname );

            if ( ! $flower_shop_elementor_menu ) {
            $flower_shop_elementor_menu_id = wp_create_nav_menu( $flower_shop_elementor_menuname );

           // Home Page 
			wp_update_nav_menu_item( $flower_shop_elementor_menu_id, 0, array(
				'menu-item-title'     => __( 'Home', 'flower-shop-elementor' ),
				'menu-item-url'       => home_url( '/' ),
				'menu-item-type'      => 'custom',
				'menu-item-status'    => 'publish',
			) );

			// About Us Page 
			$flower_shop_elementor_about_id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_content' => 'We are committed to providing reliable, professional, and result-oriented solutions tailored to meet individual needs. Our goal is to empower people with the right guidance, knowledge, and support to help them make informed decisions for a better future. <br><br> Our mission is to deliver high-quality services with honesty, transparency, and dedication. We focus on understanding client requirements and offering practical solutions that create long-term value. <br><br> With a client-centric approach, experienced professionals, and a commitment to excellence, we ensure every individual receives the attention and guidance they deserve. We believe in building trust through quality service and consistent results.',
			'post_title'  => 'About Us',
			'post_status' => 'publish',
			) );

			if ( $flower_shop_elementor_about_id ) {
			wp_update_nav_menu_item( $flower_shop_elementor_menu_id, 0, array(
			'menu-item-title'     => 'About Us',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $flower_shop_elementor_about_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			) );
			}

			// Services Page 
			$flower_shop_elementor_about_id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_content' => 'We are committed to providing reliable, professional, and result-oriented solutions tailored to meet individual needs. Our goal is to empower people with the right guidance, knowledge, and support to help them make informed decisions for a better future. <br><br> Our mission is to deliver high-quality services with honesty, transparency, and dedication. We focus on understanding client requirements and offering practical solutions that create long-term value. <br><br> With a client-centric approach, experienced professionals, and a commitment to excellence, we ensure every individual receives the attention and guidance they deserve. We believe in building trust through quality service and consistent results.',
			'post_title'  => 'Services',
			'post_status' => 'publish',
			) );

			if ( $flower_shop_elementor_about_id ) {
			wp_update_nav_menu_item( $flower_shop_elementor_menu_id, 0, array(
			'menu-item-title'     => 'Services',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $flower_shop_elementor_about_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			) );
			}

			// Contact Us Page 
			$flower_shop_elementor_about_id = wp_insert_post( array(
			'post_type'   => 'page',
			'post_content' => 'We are committed to providing reliable, professional, and result-oriented solutions tailored to meet individual needs. Our goal is to empower people with the right guidance, knowledge, and support to help them make informed decisions for a better future. <br><br> Our mission is to deliver high-quality services with honesty, transparency, and dedication. We focus on understanding client requirements and offering practical solutions that create long-term value. <br><br> With a client-centric approach, experienced professionals, and a commitment to excellence, we ensure every individual receives the attention and guidance they deserve. We believe in building trust through quality service and consistent results.',
			'post_title'  => 'Contact Us',
			'post_status' => 'publish',
			) );

			if ( $flower_shop_elementor_about_id ) {
			wp_update_nav_menu_item( $flower_shop_elementor_menu_id, 0, array(
			'menu-item-title'     => 'Contact Us',
			'menu-item-object'    => 'page',
			'menu-item-object-id' => $flower_shop_elementor_about_id,
			'menu-item-type'      => 'post_type',
			'menu-item-status'    => 'publish',
			) );
			}

            // Create Shop Page
                $flower_shop_elementor_about_title = 'Shop';
                $flower_shop_elementor_about_content = 'Lorem ipsum dolor sit amet...';

                $shop_page = get_page_by_path('shop');
                if ( !$shop_page ) {
                    $flower_shop_elementor_about = array(
                        'post_type'   => 'page',
                        'post_title'  => $flower_shop_elementor_about_title,
                        'post_content'=> $flower_shop_elementor_about_content,
                        'post_status' => 'publish',
                        'post_author' => 1,
                        'post_name'   => 'shop' 
                        );
                        $flower_shop_elementor_about_id = wp_insert_post($flower_shop_elementor_about);
                    } else {
                        $flower_shop_elementor_about_id = $shop_page->ID;
                    }

                    wp_update_nav_menu_item($flower_shop_elementor_menu_id, 0, array(
                        'menu-item-title'   => __('Shop', 'flower-shop-elementor'),
                        'menu-item-classes' => 'shop',
                        'menu-item-object-id' => $flower_shop_elementor_about_id,
                        'menu-item-object'  => 'page',
                        'menu-item-type'    => 'post_type',
                        'menu-item-status'  => 'publish'
                    ));

			/* Assign Menu Location */
			$flower_shop_elementor_locations = get_theme_mod( 'nav_menu_locations', array() );
			$flower_shop_elementor_locations[ $flower_shop_elementor_location ] = $flower_shop_elementor_menu_id;
			set_theme_mod( 'nav_menu_locations', $flower_shop_elementor_locations );
		}
    }
}
 