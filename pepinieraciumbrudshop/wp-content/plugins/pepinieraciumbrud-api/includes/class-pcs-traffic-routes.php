<?php
/**
 * Storefront traffic & visitor analytics routes.
 *
 * Public:
 * POST /wp-json/pcs/v1/traffic/track
 *
 * Admin:
 * GET  /wp-json/pcs/v1/admin/traffic
 *
 * @package PCS_API
 */

defined( 'ABSPATH' ) || exit;

final class PCS_Traffic_Routes extends PCS_Base_Routes {

    private string $table_name;

    public function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'traffic_hits';
    }

    public function register(): void {
        // Public pageview tracking beacon from Nuxt storefront
        register_rest_route( $this->namespace, '/traffic/track', [
            [
                'methods'             => 'POST',
                'callback'            => [ $this, 'track_hit' ],
                'permission_callback' => '__return_true',
            ],
        ] );

        // Admin traffic statistics endpoint
        register_rest_route( $this->namespace, '/admin/traffic', [
            [
                'methods'             => 'GET',
                'callback'            => [ $this, 'get_traffic_stats' ],
                'permission_callback' => [ $this, 'require_admin' ],
            ],
        ] );
    }

    /**
     * Record a privacy-friendly pageview hit from the storefront.
     */
    public function track_hit( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '';

        // Ignore bots, crawlers, and automated checks
        if ( $this->is_bot( $user_agent ) ) {
            return $this->success( [
                'tracked' => false,
                'reason'  => 'bot',
            ] );
        }

        $params     = $request->get_json_params() ?: $request->get_params();
        $raw_path   = isset( $params['path'] ) ? (string) $params['path'] : '/';
        $raw_title  = isset( $params['title'] ) ? sanitize_text_field( (string) $params['title'] ) : '';
        $referrer   = isset( $params['referrer'] ) ? esc_url_raw( (string) $params['referrer'] ) : '';
        $utm_source = isset( $params['utm_source'] ) ? sanitize_text_field( (string) $params['utm_source'] ) : '';

        $page_path = $this->normalize_path( $raw_path );

        // Skip internal/technical paths
        if ( str_starts_with( $page_path, '/_nuxt' ) || str_starts_with( $page_path, '/wp-' ) || str_starts_with( $page_path, '/api/' ) ) {
            return $this->success( [
                'tracked' => false,
                'reason'  => 'internal_path',
            ] );
        }

        $visit_date      = current_time( 'Y-m-d' );
        $now_datetime    = current_time( 'mysql' );
        $ip              = $this->get_client_ip();
        $visitor_hash    = hash( 'sha256', $visit_date . '|' . $ip . '|' . $user_agent . '|' . wp_salt( 'auth' ) );
        $device_type     = $this->detect_device( $user_agent );
        $referrer_source = $this->classify_referrer( $referrer, $utm_source );
        $page_title      = mb_substr( trim( $raw_title ), 0, 250 );
        $referrer_url    = ! empty( $referrer ) ? mb_substr( $referrer, 0, 500 ) : null;

        // Deduplicate rapid identical hits within 5 seconds (e.g., SSR + hydration double trigger)
        $recent_duplicate = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT id FROM `{$this->table_name}`
                 WHERE visit_date = %s
                   AND visitor_hash = %s
                   AND page_path = %s
                   AND created_at >= DATE_SUB(%s, INTERVAL 5 SECOND)
                 LIMIT 1",
                $visit_date,
                $visitor_hash,
                $page_path,
                $now_datetime
            )
        );

        if ( $recent_duplicate ) {
            return $this->success( [
                'tracked' => false,
                'reason'  => 'deduplicated',
            ] );
        }

        $location = $this->resolve_location( $ip, $visit_date, $visitor_hash );

        $inserted = $wpdb->insert(
            $this->table_name,
            [
                'visit_date'      => $visit_date,
                'visitor_hash'    => $visitor_hash,
                'page_path'       => $page_path,
                'page_title'      => $page_title,
                'referrer_source' => $referrer_source,
                'referrer_url'    => $referrer_url,
                'device_type'     => $device_type,
                'country_code'    => $location['country_code'],
                'country_name'    => $location['country_name'],
                'region_name'     => $location['region_name'],
                'city_name'       => $location['city_name'],
                'created_at'      => $now_datetime,
            ],
            [ '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s' ]
        );

        return $this->success( [
            'tracked' => (bool) $inserted,
        ] );
    }

    /**
     * Return aggregated traffic statistics for the Admin Panel.
     */
    public function get_traffic_stats( WP_REST_Request $request ): WP_REST_Response {
        global $wpdb;

        $period = sanitize_key( (string) ( $request->get_param( 'period' ) ?: '30d' ) );
        $days_map = [
            'today' => 1,
            '7d'    => 7,
            '30d'   => 30,
            '90d'   => 90,
        ];

        if ( ! isset( $days_map[ $period ] ) ) {
            $period = '30d';
        }

        $days       = $days_map[ $period ];
        $today      = current_time( 'Y-m-d' );
        $end_date   = $today;
        $start_date = gmdate( 'Y-m-d', strtotime( $today . ' -' . ( $days - 1 ) . ' days' ) );

        // Previous period of identical length for trend comparison
        $prev_end_date   = gmdate( 'Y-m-d', strtotime( $start_date . ' -1 day' ) );
        $prev_start_date = gmdate( 'Y-m-d', strtotime( $prev_end_date . ' -' . ( $days - 1 ) . ' days' ) );

        // 1. Current period totals
        $totals = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    COUNT(*) AS total_pageviews,
                    COUNT(DISTINCT visitor_hash) AS unique_visitors
                 FROM `{$this->table_name}`
                 WHERE visit_date BETWEEN %s AND %s",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        $total_pageviews = (int) ( $totals['total_pageviews'] ?? 0 );
        $unique_visitors = (int) ( $totals['unique_visitors'] ?? 0 );

        // 2. Previous period totals
        $prev_totals = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    COUNT(*) AS total_pageviews,
                    COUNT(DISTINCT visitor_hash) AS unique_visitors
                 FROM `{$this->table_name}`
                 WHERE visit_date BETWEEN %s AND %s",
                $prev_start_date,
                $prev_end_date
            ),
            ARRAY_A
        );

        $prev_pageviews = (int) ( $prev_totals['total_pageviews'] ?? 0 );
        $prev_visitors  = (int) ( $prev_totals['unique_visitors'] ?? 0 );

        // 3. Today's live snapshot
        $today_totals = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT
                    COUNT(*) AS total_pageviews,
                    COUNT(DISTINCT visitor_hash) AS unique_visitors
                 FROM `{$this->table_name}`
                 WHERE visit_date = %s",
                $today
            ),
            ARRAY_A
        );

        $today_pageviews = (int) ( $today_totals['total_pageviews'] ?? 0 );
        $today_visitors  = (int) ( $today_totals['unique_visitors'] ?? 0 );

        // 4. Orders count in selected period for Conversion Rate
        $orders_count = $this->count_orders_in_range( $start_date, $end_date );

        $pages_per_visitor = $unique_visitors > 0
            ? round( $total_pageviews / $unique_visitors, 2 )
            : 0.0;

        $conversion_rate = $unique_visitors > 0
            ? min( 100.0, round( ( $orders_count / $unique_visitors ) * 100, 2 ) )
            : 0.0;

        // 5. Daily breakdown (zero-filled for every day in range)
        $daily_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    visit_date,
                    COUNT(*) AS pageviews,
                    COUNT(DISTINCT visitor_hash) AS unique_visitors
                 FROM `{$this->table_name}`
                 WHERE visit_date BETWEEN %s AND %s
                 GROUP BY visit_date
                 ORDER BY visit_date ASC",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        $daily_by_date = [];
        if ( is_array( $daily_rows ) ) {
            foreach ( $daily_rows as $row ) {
                $daily_by_date[ $row['visit_date'] ] = [
                    'pageviews'       => (int) $row['pageviews'],
                    'unique_visitors' => (int) $row['unique_visitors'],
                ];
            }
        }

        $daily = [];
        for ( $i = 0; $i < $days; $i++ ) {
            $d = gmdate( 'Y-m-d', strtotime( $start_date . " +{$i} days" ) );
            $daily[] = [
                'date'            => $d,
                'label'           => gmdate( 'd.m', strtotime( $d ) ),
                'unique_visitors' => $daily_by_date[ $d ]['unique_visitors'] ?? 0,
                'pageviews'       => $daily_by_date[ $d ]['pageviews'] ?? 0,
            ];
        }

        // 6. Top Pages & Products viewed
        $pages_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    page_path,
                    MAX(NULLIF(page_title, '')) AS page_title,
                    COUNT(*) AS pageviews,
                    COUNT(DISTINCT visitor_hash) AS unique_visitors
                 FROM `{$this->table_name}`
                 WHERE visit_date BETWEEN %s AND %s
                 GROUP BY page_path
                 ORDER BY pageviews DESC, unique_visitors DESC
                 LIMIT 15",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        $top_pages = [];
        if ( is_array( $pages_rows ) ) {
            foreach ( $pages_rows as $row ) {
                $views = (int) $row['pageviews'];
                $top_pages[] = [
                    'page_path'       => $row['page_path'],
                    'page_title'      => $this->format_page_title( (string) ( $row['page_title'] ?? '' ), (string) $row['page_path'] ),
                    'pageviews'       => $views,
                    'unique_visitors' => (int) $row['unique_visitors'],
                    'share_percent'   => $total_pageviews > 0 ? round( ( $views / $total_pageviews ) * 100, 1 ) : 0.0,
                ];
            }
        }

        // 7. Traffic Sources / Referrers
        $referrer_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    referrer_source AS source,
                    COUNT(*) AS pageviews,
                    COUNT(DISTINCT visitor_hash) AS unique_visitors
                 FROM `{$this->table_name}`
                 WHERE visit_date BETWEEN %s AND %s
                 GROUP BY referrer_source
                 ORDER BY unique_visitors DESC, pageviews DESC
                 LIMIT 10",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        $total_source_visitors = 0;
        if ( is_array( $referrer_rows ) ) {
            foreach ( $referrer_rows as $r ) {
                $total_source_visitors += (int) $r['unique_visitors'];
            }
        }

        $referrers = [];
        if ( is_array( $referrer_rows ) ) {
            foreach ( $referrer_rows as $row ) {
                $u_visitors = (int) $row['unique_visitors'];
                $referrers[] = [
                    'source'          => $row['source'] ?: 'Direct',
                    'pageviews'       => (int) $row['pageviews'],
                    'unique_visitors' => $u_visitors,
                    'share_percent'   => $total_source_visitors > 0 ? round( ( $u_visitors / $total_source_visitors ) * 100, 1 ) : 0.0,
                ];
            }
        }

        // 8. Device breakdown
        $device_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    device_type,
                    COUNT(*) AS pageviews,
                    COUNT(DISTINCT visitor_hash) AS unique_visitors
                 FROM `{$this->table_name}`
                 WHERE visit_date BETWEEN %s AND %s
                 GROUP BY device_type",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        $device_labels = [
            'desktop' => 'Desktop',
            'mobile'  => 'Mobil',
            'tablet'  => 'Tabletă',
        ];
        $device_map = [
            'desktop' => [ 'pageviews' => 0, 'unique_visitors' => 0 ],
            'mobile'  => [ 'pageviews' => 0, 'unique_visitors' => 0 ],
            'tablet'  => [ 'pageviews' => 0, 'unique_visitors' => 0 ],
        ];

        $total_device_visitors = 0;
        if ( is_array( $device_rows ) ) {
            foreach ( $device_rows as $row ) {
                $dtype = in_array( $row['device_type'], [ 'desktop', 'mobile', 'tablet' ], true )
                    ? $row['device_type']
                    : 'desktop';
                $device_map[ $dtype ]['pageviews']       += (int) $row['pageviews'];
                $device_map[ $dtype ]['unique_visitors'] += (int) $row['unique_visitors'];
                $total_device_visitors                   += (int) $row['unique_visitors'];
            }
        }

        $devices = [];
        foreach ( $device_map as $dtype => $dstats ) {
            $devices[] = [
                'device_type'     => $dtype,
                'label'           => $device_labels[ $dtype ],
                'pageviews'       => $dstats['pageviews'],
                'unique_visitors' => $dstats['unique_visitors'],
                'share_percent'   => $total_device_visitors > 0
                    ? round( ( $dstats['unique_visitors'] / $total_device_visitors ) * 100, 1 )
                    : 0.0,
            ];
        }

        // 9. Visitor Locations (City / County / Country)
        $location_rows = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    country_code,
                    country_name,
                    region_name,
                    city_name,
                    COUNT(*) AS pageviews,
                    COUNT(DISTINCT visitor_hash) AS unique_visitors
                 FROM `{$this->table_name}`
                 WHERE visit_date BETWEEN %s AND %s
                   AND (country_code != '' OR region_name != '' OR city_name != '')
                 GROUP BY country_code, country_name, region_name, city_name
                 ORDER BY unique_visitors DESC, pageviews DESC
                 LIMIT 12",
                $start_date,
                $end_date
            ),
            ARRAY_A
        );

        $total_loc_visitors = 0;
        if ( is_array( $location_rows ) ) {
            foreach ( $location_rows as $l ) {
                $total_loc_visitors += (int) $l['unique_visitors'];
            }
        }

        $locations = [];
        if ( is_array( $location_rows ) ) {
            foreach ( $location_rows as $row ) {
                $u_visitors = (int) $row['unique_visitors'];
                $c_code     = strtoupper( trim( (string) ( $row['country_code'] ?? '' ) ) );
                $c_name     = trim( (string) ( $row['country_name'] ?? '' ) );
                $r_name     = trim( (string) ( $row['region_name'] ?? '' ) );
                $city       = trim( (string) ( $row['city_name'] ?? '' ) );

                $parts = [];
                if ( '' !== $city ) {
                    $parts[] = $city;
                }
                if ( '' !== $r_name && mb_strtolower( $r_name ) !== mb_strtolower( $city ) ) {
                    $parts[] = $r_name;
                }
                if ( empty( $parts ) && '' !== $c_name ) {
                    $parts[] = $c_name;
                }

                $label = ! empty( $parts ) ? implode( ', ', $parts ) : ( $c_code ?: 'Necunoscut' );

                $locations[] = [
                    'country_code'    => $c_code,
                    'country_name'    => $c_name,
                    'region_name'     => $r_name,
                    'city_name'       => $city,
                    'label'           => $label,
                    'pageviews'       => (int) $row['pageviews'],
                    'unique_visitors' => $u_visitors,
                    'share_percent'   => $total_loc_visitors > 0
                        ? round( ( $u_visitors / $total_loc_visitors ) * 100, 1 )
                        : 0.0,
                ];
            }
        }

        return $this->success( [
            'period'     => $period,
            'date_range' => [
                'start_date' => $start_date,
                'end_date'   => $end_date,
                'days'       => $days,
            ],
            'summary'    => [
                'unique_visitors'          => $unique_visitors,
                'total_pageviews'          => $total_pageviews,
                'pages_per_visitor'        => $pages_per_visitor,
                'orders_count'             => $orders_count,
                'conversion_rate'          => $conversion_rate,
                'previous_unique_visitors' => $prev_visitors,
                'previous_total_pageviews' => $prev_pageviews,
                'today_visitors'           => $today_visitors,
                'today_pageviews'          => $today_pageviews,
            ],
            'daily'      => $daily,
            'top_pages'  => $top_pages,
            'referrers'  => $referrers,
            'devices'    => $devices,
            'locations'  => $locations,
        ] );
    }

    /**
     * Resolve visitor location (Country, Județ/Region, City) once per unique visitor.
     * Uses existing daily hit -> WP transient -> Cloudflare headers -> cached IP lookup.
     */
    private function resolve_location( string $ip, string $visit_date, string $visitor_hash ): array {
        global $wpdb;

        $empty = [
            'country_code' => '',
            'country_name' => '',
            'region_name'  => '',
            'city_name'    => '',
        ];

        // 1. Check if we already resolved location for this visitor today
        $existing = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT country_code, country_name, region_name, city_name
                 FROM `{$this->table_name}`
                 WHERE visit_date = %s
                   AND visitor_hash = %s
                   AND country_code != ''
                 LIMIT 1",
                $visit_date,
                $visitor_hash
            ),
            ARRAY_A
        );

        if ( is_array( $existing ) && ! empty( $existing['country_code'] ) ) {
            return [
                'country_code' => (string) $existing['country_code'],
                'country_name' => (string) $existing['country_name'],
                'region_name'  => (string) $existing['region_name'],
                'city_name'    => (string) $existing['city_name'],
            ];
        }

        // 2. Check 24h transient cache keyed by hashed IP
        $cache_key = 'pcs_geo_' . substr( hash( 'sha256', $ip . '|' . wp_salt( 'auth' ) ), 0, 24 );
        $cached    = get_transient( $cache_key );
        if ( is_array( $cached ) && isset( $cached['country_code'] ) ) {
            return $cached;
        }

        // 3. Check Cloudflare / Reverse-Proxy headers if present
        $cf_country = isset( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ? strtoupper( sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCOUNTRY'] ) ) ) : '';
        if ( '' !== $cf_country && ! in_array( $cf_country, [ 'XX', 'T1' ], true ) ) {
            $cf_region = isset( $_SERVER['HTTP_CF_REGION'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_REGION'] ) ) : '';
            $cf_city   = isset( $_SERVER['HTTP_CF_IPCITY'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_CF_IPCITY'] ) ) : '';

            if ( '' !== $cf_city || '' !== $cf_region ) {
                $loc = [
                    'country_code' => mb_substr( $cf_country, 0, 10 ),
                    'country_name' => $this->format_country_name( $cf_country, '' ),
                    'region_name'  => $this->normalize_region_name( $cf_region ),
                    'city_name'    => $this->normalize_city_name( $cf_city ),
                ];
                set_transient( $cache_key, $loc, DAY_IN_SECONDS );
                return $loc;
            }
        }

        // 4. Determine public IP to geolocate (supports localhost via WC_Geolocation)
        $lookup_ip = $ip;
        if ( $this->is_private_or_local_ip( $lookup_ip ) ) {
            if ( class_exists( 'WC_Geolocation' ) ) {
                $ext_ip = WC_Geolocation::get_external_ip_address();
                if ( is_string( $ext_ip ) && '' !== $ext_ip && '0.0.0.0' !== $ext_ip ) {
                    $lookup_ip = $ext_ip;
                }
            }
        }

        if ( $this->is_private_or_local_ip( $lookup_ip ) ) {
            return $empty;
        }

        // 5. Perform lightweight lookup via ipwho.is (with ipinfo.io fallback)
        $loc = $this->fetch_geo_from_api( $lookup_ip );

        // Cache for 24 hours (even if empty, cache for 1 hour to avoid retrying on every hit)
        set_transient(
            $cache_key,
            $loc,
            ! empty( $loc['country_code'] ) ? DAY_IN_SECONDS : HOUR_IN_SECONDS
        );

        // Backfill any earlier hits today from the same visitor_hash that had empty location
        if ( ! empty( $loc['country_code'] ) ) {
            $wpdb->update(
                $this->table_name,
                [
                    'country_code' => $loc['country_code'],
                    'country_name' => $loc['country_name'],
                    'region_name'  => $loc['region_name'],
                    'city_name'    => $loc['city_name'],
                ],
                [
                    'visit_date'   => $visit_date,
                    'visitor_hash' => $visitor_hash,
                    'country_code' => '',
                ],
                [ '%s', '%s', '%s', '%s' ],
                [ '%s', '%s', '%s' ]
            );
        }

        return $loc;
    }

    /**
     * Query geolocation API with short timeout so tracking never blocks.
     */
    private function fetch_geo_from_api( string $ip ): array {
        $empty = [
            'country_code' => '',
            'country_name' => '',
            'region_name'  => '',
            'city_name'    => '',
        ];

        // Primary: ipwho.is (HTTPS, no key required, returns country_code, country, region, city)
        $res = wp_safe_remote_get(
            'https://ipwho.is/' . rawurlencode( $ip ) . '?fields=success,country,country_code,region,city',
            [
                'timeout'    => 1.5,
                'user-agent' => 'PepinieraCiumbrud/1.0',
            ]
        );

        if ( ! is_wp_error( $res ) && 200 === wp_remote_retrieve_response_code( $res ) ) {
            $body = json_decode( wp_remote_retrieve_body( $res ), true );
            if ( is_array( $body ) && ! empty( $body['success'] ) && ! empty( $body['country_code'] ) ) {
                $code = strtoupper( sanitize_text_field( (string) $body['country_code'] ) );
                return [
                    'country_code' => mb_substr( $code, 0, 10 ),
                    'country_name' => $this->format_country_name( $code, (string) ( $body['country'] ?? '' ) ),
                    'region_name'  => $this->normalize_region_name( (string) ( $body['region'] ?? '' ) ),
                    'city_name'    => $this->normalize_city_name( (string) ( $body['city'] ?? '' ) ),
                ];
            }
        }

        // Fallback: ipinfo.io
        $res2 = wp_safe_remote_get(
            'https://ipinfo.io/' . rawurlencode( $ip ) . '/json',
            [
                'timeout'    => 1.5,
                'user-agent' => 'PepinieraCiumbrud/1.0',
            ]
        );

        if ( ! is_wp_error( $res2 ) && 200 === wp_remote_retrieve_response_code( $res2 ) ) {
            $body = json_decode( wp_remote_retrieve_body( $res2 ), true );
            if ( is_array( $body ) && ! empty( $body['country'] ) ) {
                $code = strtoupper( sanitize_text_field( (string) $body['country'] ) );
                return [
                    'country_code' => mb_substr( $code, 0, 10 ),
                    'country_name' => $this->format_country_name( $code, '' ),
                    'region_name'  => $this->normalize_region_name( (string) ( $body['region'] ?? '' ) ),
                    'city_name'    => $this->normalize_city_name( (string) ( $body['city'] ?? '' ) ),
                ];
            }
        }

        return $empty;
    }

    /**
     * Check if an IP is loopback or private LAN.
     */
    private function is_private_or_local_ip( string $ip ): bool {
        if ( '' === $ip || '0.0.0.0' === $ip || '127.0.0.1' === $ip || '::1' === $ip ) {
            return true;
        }
        return ! filter_var(
            $ip,
            FILTER_VALIDATE_IP,
            FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
        );
    }

    /**
     * Format country code into Romanian country name.
     */
    private function format_country_name( string $code, string $fallback ): string {
        $ro_names = [
            'RO' => 'România',
            'MD' => 'Republica Moldova',
            'HU' => 'Ungaria',
            'DE' => 'Germania',
            'IT' => 'Italia',
            'ES' => 'Spania',
            'FR' => 'Franța',
            'GB' => 'Marea Britanie',
            'UK' => 'Marea Britanie',
            'AT' => 'Austria',
            'BE' => 'Belgia',
            'NL' => 'Olanda',
            'BG' => 'Bulgaria',
            'US' => 'SUA',
            'CA' => 'Canada',
            'CH' => 'Elveția',
            'IE' => 'Irlanda',
        ];

        if ( isset( $ro_names[ $code ] ) ) {
            return $ro_names[ $code ];
        }

        if ( function_exists( 'WC' ) && isset( WC()->countries->countries[ $code ] ) ) {
            return html_entity_decode( (string) WC()->countries->countries[ $code ], ENT_QUOTES, 'UTF-8' );
        }

        return mb_substr( sanitize_text_field( $fallback ?: $code ), 0, 100 );
    }

    /**
     * Clean region/county name (e.g. "Cluj County" -> "Cluj", "Bucharest" -> "București").
     */
    private function normalize_region_name( string $region ): string {
        $clean = trim( sanitize_text_field( $region ) );
        if ( '' === $clean ) {
            return '';
        }
        $clean = preg_replace( '/\s+County$/i', '', $clean );
        $clean = preg_replace( '/^Jude[țt]ul\s+/iu', '', (string) $clean );
        if ( 'Bucharest' === $clean ) {
            $clean = 'București';
        }
        return mb_substr( trim( (string) $clean ), 0, 100 );
    }

    /**
     * Clean city name (e.g. "Bucharest" -> "București").
     */
    private function normalize_city_name( string $city ): string {
        $clean = trim( sanitize_text_field( $city ) );
        if ( 'Bucharest' === $clean ) {
            $clean = 'București';
        }
        return mb_substr( $clean, 0, 100 );
    }

    /**
     * Count WooCommerce orders placed within [start_date, end_date].
     */
    private function count_orders_in_range( string $start_date, string $end_date ): int {
        if ( ! function_exists( 'wc_get_orders' ) ) {
            return 0;
        }

        try {
            $order_ids = wc_get_orders( [
                'return'       => 'ids',
                'limit'        => -1,
                'status'       => [ 'pending', 'processing', 'prepared', 'shipped', 'completed', 'on-hold' ],
                'date_created' => $start_date . '...' . $end_date,
            ] );
            return is_array( $order_ids ) ? count( $order_ids ) : 0;
        } catch ( Throwable $e ) {
            return 0;
        }
    }

    /**
     * Normalize URL path.
     */
    private function normalize_path( string $raw_path ): string {
        $parsed = wp_parse_url( $raw_path, PHP_URL_PATH );
        $path   = is_string( $parsed ) && '' !== $parsed ? $parsed : '/';
        $path   = '/' . ltrim( $path, '/' );
        if ( '/' !== $path ) {
            $path = rtrim( $path, '/' );
        }
        return mb_substr( sanitize_text_field( $path ), 0, 255 );
    }

    /**
     * Format a friendly page title if none was stored or clean suffix.
     */
    private function format_page_title( string $title, string $path ): string {
        if ( '' !== trim( $title ) ) {
            // Strip repetitive site suffix for cleaner display
            $clean = preg_replace( '/\s*[–—|-]\s*Pepiniera Ciumbrud.*$/iu', '', $title );
            return '' !== trim( (string) $clean ) ? trim( (string) $clean ) : trim( $title );
        }

        $known = [
            '/'                   => 'Acasă (Pagina Principală)',
            '/produse'            => 'Catalog Produse',
            '/cos'                => 'Coș de Cumpărături',
            '/finalizare-comanda' => 'Finalizare Comandă (Checkout)',
            '/contact'            => 'Contact',
            '/despre-noi'         => 'Despre Noi',
        ];

        return $known[ $path ] ?? $path;
    }

    /**
     * Classify referrer URL or UTM source into a clean channel/domain name.
     */
    private function classify_referrer( string $referrer, string $utm_source ): string {
        if ( '' !== trim( $utm_source ) ) {
            $utm = strtolower( trim( $utm_source ) );
            $map = [
                'fb'        => 'Facebook',
                'facebook'  => 'Facebook',
                'ig'        => 'Instagram',
                'instagram' => 'Instagram',
                'google'    => 'Google',
                'adwords'   => 'Google',
                'tiktok'    => 'TikTok',
                'youtube'   => 'YouTube',
                'newsletter'=> 'Newsletter',
                'email'     => 'Email',
            ];
            return $map[ $utm ] ?? ucfirst( mb_substr( $utm, 0, 80 ) );
        }

        if ( '' === trim( $referrer ) ) {
            return 'Direct';
        }

        $host = strtolower( (string) wp_parse_url( $referrer, PHP_URL_HOST ) );
        if ( '' === $host ) {
            return 'Direct';
        }

        $host = preg_replace( '/^(www|m|l|lm)\./', '', $host );

        // Internal storefront or local dev domains count as Direct
        $internal_hosts = [
            'pepinieraciumbrud.ro',
            'pepinieraciumbrud.local',
            'admin.pepinieraciumbrud.local',
            'api.pepinieraciumbrud.local',
            'localhost',
            '127.0.0.1',
        ];

        foreach ( $internal_hosts as $internal ) {
            if ( $host === $internal || str_ends_with( $host, '.' . $internal ) ) {
                return 'Direct';
            }
        }

        if ( str_contains( $host, 'google.' ) ) {
            return 'Google';
        }
        if ( str_contains( $host, 'facebook.com' ) || str_contains( $host, 'fb.com' ) || str_contains( $host, 'fb.me' ) ) {
            return 'Facebook';
        }
        if ( str_contains( $host, 'instagram.com' ) ) {
            return 'Instagram';
        }
        if ( str_contains( $host, 'bing.com' ) ) {
            return 'Bing';
        }
        if ( str_contains( $host, 'yahoo.com' ) ) {
            return 'Yahoo';
        }
        if ( str_contains( $host, 'tiktok.com' ) ) {
            return 'TikTok';
        }
        if ( str_contains( $host, 'youtube.com' ) || str_contains( $host, 'youtu.be' ) ) {
            return 'YouTube';
        }
        if ( str_contains( $host, 'duckduckgo.com' ) ) {
            return 'DuckDuckGo';
        }
        if ( str_contains( $host, 'pinterest.' ) ) {
            return 'Pinterest';
        }

        return mb_substr( $host, 0, 100 );
    }

    /**
     * Detect device category from User-Agent.
     */
    private function detect_device( string $user_agent ): string {
        if ( '' === $user_agent ) {
            return 'desktop';
        }

        if ( preg_match( '/ipad|tablet|kindle|playbook|silk|(android(?!.*mobile))/i', $user_agent ) ) {
            return 'tablet';
        }

        if ( preg_match( '/mobile|iphone|ipod|android.*mobile|windows phone|blackberry|opera mini|iemobile/i', $user_agent ) ) {
            return 'mobile';
        }

        return 'desktop';
    }

    /**
     * Check if User-Agent belongs to a bot or crawler.
     */
    private function is_bot( string $user_agent ): bool {
        if ( '' === trim( $user_agent ) ) {
            return false;
        }

        return (bool) preg_match(
            '/bot|crawl|spider|slurp|mediapartners|adsbot|bingpreview|facebookexternalhit|whatsapp|telegrambot|discordbot|linkedinbot|embedly|quora link preview|showyoubot|outbrain|pinterest\/0\.|developers\.google\.com|www\.google\.com\/bot|ahrefs|semrush|mj12bot|dotbot|petalbot|bytespider|gptbot|claudebot|ccbot|headlesschrome|phantomjs|lighthouse|pagespeed|gtmetrix|pingdom|uptimerobot/i',
            $user_agent
        );
    }

    /**
     * Extract client IP address (used only in-memory for daily salted SHA-256 hash & cached city/region lookup).
     */
    private function get_client_ip(): string {
        $keys = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_X_REAL_IP',
            'HTTP_X_FORWARDED_FOR',
            'REMOTE_ADDR',
        ];

        foreach ( $keys as $key ) {
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $ip_list = explode( ',', sanitize_text_field( wp_unslash( $_SERVER[ $key ] ) ) );
                $ip      = trim( $ip_list[0] );
                if ( filter_var( $ip, FILTER_VALIDATE_IP ) ) {
                    return $ip;
                }
            }
        }

        return '0.0.0.0';
    }
}
