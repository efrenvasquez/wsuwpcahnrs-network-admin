<?php namespace WSUWP\Plugin\NetworkInfo;

class WSUWP_Multisite_Info {

    public function __construct() {
        add_action( 'network_admin_menu', array( $this, 'add_menu_page' ) );
    }

    public function add_menu_page() {
        add_menu_page(
            'WSU Multisite Info',
            'WSU Multisite Info',
            'manage_network',
            'wsuwp-multisite-info',
            array( $this, 'render_info_table' ),
            'dashicons-networking',
            1
        );
    }

    public function generate_network_accessibility_report($site_id) {
        $total_errors   = 0;
        $total_alerts   = 0;
        $total_warnings = 0;
        $total_correct  = 0;
        $total_no_data  = 0;

        $selected_issue_types = array( 'errors', 'alerts', 'warnings' );

        switch_to_blog($site_id);

        $args = array(
            'post_type'      => array( 'page', 'post' ),
            'posts_per_page' => -1,
            'post_status'    => 'any',
        );

        $query = new \WP_Query($args);

        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();

                $custom_meta = get_post_meta( $post_id, '_wsuwp_accessibility_report', true );

                if ( empty( $custom_meta ) ) {
                    $custom_meta = get_post_meta( $post_id, 'wsuwp_accessibility_report', true );
                }

                if ( empty( $custom_meta ) ) {
                    $total_no_data++;
                    continue;
                }

                $report = is_string( $custom_meta ) ? json_decode( $custom_meta ) : $custom_meta;

                if ( ! is_object( $report ) ) {
                    $total_no_data++;
                    continue;
                }

                $errors_count   = ( isset( $report->errors ) && is_array( $report->errors ) ) ? count( $report->errors ) : 0;
                $alerts_count   = ( isset( $report->alerts ) && is_array( $report->alerts ) ) ? count( $report->alerts ) : 0;
                $warnings_count = ( isset( $report->warnings ) && is_array( $report->warnings ) ) ? count( $report->warnings ) : 0;

                if ( in_array( 'errors', $selected_issue_types, true ) ) {
                    $total_errors += $errors_count;
                }

                if ( in_array( 'alerts', $selected_issue_types, true ) ) {
                    $total_alerts += $alerts_count;
                }

                if ( in_array( 'warnings', $selected_issue_types, true ) ) {
                    $total_warnings += $warnings_count;
                }

                if ( 0 === $errors_count && 0 === $alerts_count && 0 === $warnings_count ) {
                    $total_correct++;
                }
            }
        }

        wp_reset_postdata();
        restore_current_blog();

        return array(
            'errors'   => $total_errors,
            'alerts'   => $total_alerts,
            'warnings' => $total_warnings,
            'correct'  => $total_correct,
            'no_data'  => $total_no_data,
        );
    }

    // Creates the table on Admin dashboard.
    public function render_info_table() {
        $per_page = isset($_GET['per_page']) ? max( 1, (int) $_GET['per_page'] ) : 25;
        $paged    = isset($_GET['paged']) ? max( 1, (int) $_GET['paged'] ) : 1;

        $total_sites = (int) get_sites( [ 'count' => true ] );
        $total_pages = (int) ceil( $total_sites / $per_page );

        $sites = get_sites( [
        'number' => $per_page,
        'offset' => ($paged - 1) * $per_page,
        ] );

        include Plugin::get('dir') . 'assets/templates/wsuwp-multisite-info-table.php';
    }

    // Retrieve the registration date for a site.
    public function get_registration_date( $site_id ) {
        $site_details = get_blog_details( $site_id );
        $registration_date = $site_details->registered;

        return $registration_date;
    }


    // Retrieve the last content update date for a site.
    public function get_last_content_update( $site_id ) {
        switch_to_blog( $site_id );

        $args = array(
            'post_type'      => 'any',
            'posts_per_page' => 1,
            'orderby'        => 'modified',
            'order'          => 'DESC',
        );

        $query = new \WP_Query( $args );
        $last_updated = '';

        if ( $query->have_posts() ) {
            $query->the_post();
            $last_updated = get_the_modified_date();
        }

        wp_reset_postdata();
        restore_current_blog();

        return $last_updated;
    }


     // Retrieve the count of users for a site.
    public function get_user_count( $site_id ) {
        switch_to_blog( $site_id );
        $users_count = count_users();
        restore_current_blog();

        return $users_count;
    }


    // Retrieve the count of pages for a site.
    public function get_page_count( $site_id ) {
        switch_to_blog( $site_id );
        $count = wp_count_posts( 'page' )->publish;
        restore_current_blog();

        return $count;
    }

    // Retrieve the count of posts for a site.
    public function get_post_count( $site_id ) {
        switch_to_blog( $site_id );
        $count = wp_count_posts( 'post' )->publish;
        restore_current_blog();

        return $count;
    }

    // Retrieve the count of a events for a site.
    public function get_custom_post_type_count( $site_id ) {
        switch_to_blog( $site_id );

        $args = array(
            'post_type'      => 'tribe_events',
            'posts_per_page' => -1,
        );

        $query = new \WP_Query( $args );
        $count = $query->post_count;

        restore_current_blog();

        return $count;
    }

    
    // Retrieve the GA4 code for a specific website.
    public function get_input_field_setting_GA4( $site_id ) {
        switch_to_blog( $site_id );

        $setting_value = get_option( 'wsuwp_ga4_id' );

        restore_current_blog();

        return $setting_value;
    }

    // Check to see if the website is indexed or not.
    public function get_input_field_setting_index_site( $site_id ) {
        switch_to_blog( $site_id );

        $setting_value = get_option( 'blog_public' );

        restore_current_blog();

        return $setting_value;
    }

    //Retrieve total documents on subsites
    public function get_media_document_count( $site_id, $document_mime_types = array() ){
    switch_to_blog( $site_id );

        if( empty( $document_mime_types )){
            $document_mime_types = array(
                'application/pdf',
                'application/msword',
                'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                'application/vnd.ms-powerpoint',
                'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                'application/vnd.ms-excel',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'application/csv',
                'text/csv'
            );
        }
        

        $query = new \WP_Query( array(
            'post_type'         => 'attachment',
            'post_status'       => 'inherit',
            'posts_per_page'    => 1,
            'fields'            => 'ids',
            'post_mime_type'    => $document_mime_types,
        ));

        $count = (int) $query->found_posts;

        restore_current_blog();

        return $count;
    }

    // Retrieve total media files and breakdown by file type
    // public function get_media_file_info( $site_id ) {
    //     switch_to_blog( $site_id );

    //     // Query all media attachments
    //     $args = array(
    //         'post_type'      => 'attachment',
    //         'post_status'    => 'inherit',
    //         'posts_per_page' => -1,
    //         'fields'         => 'ids', // more efficient
    //     );

    //     $attachments = get_posts( $args );

    //     $total_files = count( $attachments );
    //     $file_type_counts = array();

    //     foreach ( $attachments as $attachment_id ) {
    //         $file_path = get_attached_file( $attachment_id );
    //         $file_ext  = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );

    //         if ( ! empty( $file_ext ) ) {
    //             if ( isset( $file_type_counts[ $file_ext ] ) ) {
    //                 $file_type_counts[ $file_ext ]++;
    //             } else {
    //                 $file_type_counts[ $file_ext ] = 1;
    //             }
    //         }
    //     }

    //     restore_current_blog();

    //     // Create a readable breakdown string like "jpg (10), pdf (3)"
    //     $breakdown_parts = array();
    //     foreach ( $file_type_counts as $ext => $count ) {
    //         $breakdown_parts[] = "{$ext} ({$count})";
    //     }

    //     $breakdown = ! empty( $breakdown_parts ) ? implode( ', ', $breakdown_parts ) : '—';

    //     return array(
    //         'total'     => $total_files,
    //         'breakdown' => $breakdown,
    //     );
    // }

    // Calculate totals across the entire multisite network (fixed)
    // public function get_network_totals() {
    //     $sites = get_sites();
    //     $total_sites       = count( $sites );
    //     $total_users       = 0;
    //     $total_pages       = 0;
    //     $total_posts       = 0;
    //     $total_events      = 0;
    //     $total_media_files = 0;
    //     $file_type_counts  = array();

    //     foreach ( $sites as $site ) {
    //         $site_id = $site->blog_id;

    //         // Users (this method already switches context internally)
    //         $users_count = $this->get_user_count( $site_id );
    //         $total_users += isset( $users_count['total_users'] ) ? (int) $users_count['total_users'] : 0;

    //         // Pages, posts, events
    //         $total_pages  += (int) $this->get_page_count( $site_id );
    //         $total_posts  += (int) $this->get_post_count( $site_id );
    //         $total_events += (int) $this->get_custom_post_type_count( $site_id );

    //         // --- MEDIA: switch to the site and process attachments in that context ---
    //         switch_to_blog( $site_id );

    //         $attachments = get_posts( array(
    //             'post_type'      => 'attachment',
    //             'post_status'    => 'inherit',
    //             'posts_per_page' => -1,
    //             'fields'         => 'ids',
    //         ) );

    //         $count_attachments = is_array( $attachments ) ? count( $attachments ) : 0;
    //         $total_media_files += $count_attachments;

    //         if ( ! empty( $attachments ) ) {
    //             foreach ( $attachments as $attachment_id ) {
    //                 // Get the real file path while still switched to the site's blog
    //                 $file_path = get_attached_file( $attachment_id );

    //                 // Prefer filepath extension (more accurate); fall back to mime-type if needed
    //                 $ext = '';
    //                 if ( $file_path ) {
    //                     $ext = strtolower( pathinfo( $file_path, PATHINFO_EXTENSION ) );
    //                 }

    //                 if ( empty( $ext ) ) {
    //                     $mime = get_post_mime_type( $attachment_id );
    //                     if ( $mime ) {
    //                         $parts = explode( '/', $mime );
    //                         $ext = end( $parts );
    //                         // strip +xml etc (e.g. svg+xml)
    //                         $ext = preg_replace( '/\+.*$/', '', $ext );
    //                     }
    //                 }

    //                 if ( $ext ) {
    //                     // Normalize jpeg->jpg
    //                     if ( $ext === 'jpeg' ) {
    //                         $ext = 'jpg';
    //                     }

    //                     if ( isset( $file_type_counts[ $ext ] ) ) {
    //                         $file_type_counts[ $ext ]++;
    //                     } else {
    //                         $file_type_counts[ $ext ] = 1;
    //                     }
    //                 }
    //             }
    //         }

    //         restore_current_blog();
    //     }

    //     // Format file type breakdown
    //     ksort( $file_type_counts );
    //     $file_type_breakdown = array();
    //     foreach ( $file_type_counts as $ext => $count ) {
    //         $file_type_breakdown[] = "{$ext} ({$count})";
    //     }
    //     $file_type_breakdown_str = ! empty( $file_type_breakdown ) ? implode( ', ', $file_type_breakdown ) : '—';

    //     return array(
    //         'sites'        => $total_sites,
    //         'users'        => $total_users,
    //         'pages'        => $total_pages,
    //         'posts'        => $total_posts,
    //         'events'       => $total_events,
    //         'media_total'  => $total_media_files,
    //         'media_types'  => $file_type_breakdown_str,
    //     );
    // }



}

new WSUWP_Multisite_Info();