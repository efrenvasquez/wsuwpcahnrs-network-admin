<?php
/**
 * Template: wsuwp-multisite-info-table.php
 * Variables expected:
 *   - $sites
 *   - $paged
 *   - $per_page
 *   - $total_sites
 *   - $total_pages
 */
?>

<div class="wrap">
    <h1>WSUWP Multisite Info Table</h1>

    <?php
    $base_url = add_query_arg(
        [ 'page' => 'wsuwp-multisite-info' ],
        network_admin_url('admin.php')
    );

    $per_page_options = [ 10, 25, 50, 100 ];
    ?>

    <div class="tablenav top" style="display:flex; justify-content:space-between; align-items:center; margin: 12px 0;">
        <div class="alignleft actions">
            <label for="wsuwp-per-page" style="margin-right:6px;">Show</label>
            <select id="wsuwp-per-page">
                <?php foreach ( $per_page_options as $opt ) : ?>
                    <option value="<?php echo (int) $opt; ?>" <?php echo selected( (int) $per_page, (int) $opt, false ); ?>>
                        <?php echo (int) $opt; ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="tablenav-pages">
            <?php
            $pagination_base = add_query_arg(
                [
                    'page'     => 'wsuwp-multisite-info',
                    'per_page' => $per_page,
                    'paged'    => '%#%',
                ],
                network_admin_url('admin.php')
            );

            echo paginate_links([
                'base'      => $pagination_base,
                'format'    => '',
                'current'   => (int) $paged,
                'total'     => (int) max( 1, $total_pages ),
                'prev_text' => '&laquo; Previous',
                'next_text' => 'Next &raquo;',
                'type'      => 'plain',
            ]);

            echo '<span class="displaying-num" style="margin-left:10px;">'
                . esc_html( (int) $total_sites ) . ' total sites'
                . '</span>';
            ?>
        </div>
    </div>

    <table class="wsuwp-multisite-information wp-list-table widefat display">
        <thead>
            <tr>
                <th>Website</th>
                <th>Accessibility &amp; Usability</th>
                <th>GA4</th>
                <th>Indexed</th>
                <th>Theme</th>
                <th>Plugins (bold are network activated)</th>
                <th>Users</th>
                <th>Pages</th>
                <th>Posts</th>
                <th>Events</th>
                <!-- <th>Media Files</th> -->
                <th>Registered</th>
                <th>Last Updated</th>
                <th>Documents</th>
            </tr>
        </thead>

        <tbody>
            <?php
            if ( ! empty( $sites ) ) :
                foreach ( $sites as $site ) {

                    $site_id  = (int) $site->blog_id;
                    $site_url = get_home_url( $site_id );

                    switch_to_blog( $site_id );
                    $theme_obj = wp_get_theme();
                    $theme     = $theme_obj->get( 'Name' );
                    restore_current_blog();

                    $site_plugins = get_blog_option( $site_id, 'active_plugins', array() );
                    $network_plugins = array_keys( (array) get_site_option( 'active_sitewide_plugins', array() ) );

                    $all_plugins = array_unique( array_merge( $site_plugins, $network_plugins ) );

                    $active_plugins = array();

                    foreach ( $all_plugins as $plugin ) {
                        $plugin_file = WP_PLUGIN_DIR . '/' . $plugin;

                        if ( file_exists( $plugin_file ) ) {
                            $plugin_data = get_plugin_data( $plugin_file, false, false );
                            $plugin_name = ! empty( $plugin_data['Name'] ) ? $plugin_data['Name'] : $plugin;

                            if ( in_array( $plugin, $network_plugins, true ) ) {
                                $active_plugins[] = '<strong>' . esc_html( $plugin_name ) . '</strong>';
                            } else {
                                $active_plugins[] = esc_html( $plugin_name );
                            }
                        } else {
                            if ( in_array( $plugin, $network_plugins, true ) ) {
                                $active_plugins[] = '<strong>' . esc_html( $plugin ) . '</strong>';
                            } else {
                                $active_plugins[] = esc_html( $plugin );
                            }
                        }
                    }

                    $users_count = $this->get_user_count( $site_id );
                    $users       = isset($users_count['total_users']) ? (int) $users_count['total_users'] : 0;

                    $pages_count            = (int) $this->get_page_count( $site_id );
                    $posts_count            = (int) $this->get_post_count( $site_id );
                    $custom_post_type_count = (int) $this->get_custom_post_type_count( $site_id );
                    $document_count = $this->get_media_document_count( $site_id );

                    $pdf_count = $this->get_media_document_count( $site_id, array(
                        'application/pdf',
                    ) );

                    $word_count = $this->get_media_document_count( $site_id, array(
                        'application/msword',
                        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    ) );

                    $powerpoint_count = $this->get_media_document_count( $site_id, array(
                        'application/vnd.ms-powerpoint',
                        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                    ) );

                    $excel_count = $this->get_media_document_count( $site_id, array(
                        'application/vnd.ms-excel',
                        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        'application/csv',
                        'text/csv'
                    ) );

                    $document_summary =
                        'PDF: ' . esc_html( $pdf_count ) . '<br>' .
                        'Word: ' . esc_html( $word_count ) . '<br>' .
                        'PowerPoint: ' . esc_html( $powerpoint_count ) . '<br>' .
                        'Excel: ' . esc_html( $excel_count ) . '<br>' .
                        'Total Documents: ' . esc_html( $document_count );


                    $input_field_setting_GA4 = $this->get_input_field_setting_GA4( $site_id );

                    $input_field_settings_index_site = $this->get_input_field_setting_index_site( $site_id );
                    $input_field_settings_index_site = $input_field_settings_index_site ? "Yes" : "No";

                    // Registration date
                    $registration_date_raw = $this->get_registration_date( $site_id );
                    $registration_dt = $registration_date_raw ? new \DateTime($registration_date_raw) : null;
                    $formatted_registration_date = $registration_dt ? $registration_dt->format('m-d-y') : '';
                    $registration_order = $registration_dt ? $registration_dt->getTimestamp() : 0;

                    // Last updated
                    $last_updated_raw = $this->get_last_content_update( $site_id );
                    $last_updated_dt = $last_updated_raw ? new \DateTime($last_updated_raw) : null;
                    $formatted_last_updated = $last_updated_dt ? $last_updated_dt->format('m-d-y') : '';
                    $last_updated_order = $last_updated_dt ? $last_updated_dt->getTimestamp() : 0;

                    // Accessibility totals
                    $accessibility_totals  = $this->generate_network_accessibility_report( $site_id );
                    $network_total_errors  = (int) ($accessibility_totals['errors'] ?? 0);
                    $network_total_alerts  = (int) ($accessibility_totals['alerts'] ?? 0);
                    $network_total_warnings = (int) ($accessibility_totals['warnings'] ?? 0);
                    $network_total_correct  = (int) ($accessibility_totals['correct'] ?? 0);
                    $network_total_no_data  = (int) ($accessibility_totals['no_data'] ?? 0);

                    echo '<tr>';
                    echo '<td><a href="' . esc_url( $site_url ) . '" target="_blank" rel="noopener noreferrer">' . esc_html( $site_url ) . '</a></td>';
                    echo '<td>Errors: ' . esc_html( $network_total_errors ) . '<br> Alerts: ' . esc_html( $network_total_alerts ) . '<br> Warnings: ' . esc_html( $network_total_warnings ) . '<br>' . 'Correct: ' . esc_html( $network_total_correct ) . '<br> No Data: ' . esc_html( $network_total_no_data ) .'</td>';
                    echo '<td>' . esc_html( $input_field_setting_GA4 ) . '</td>';
                    echo '<td>' . esc_html( $input_field_settings_index_site ) . '</td>';
                    echo '<td>' . esc_html( $theme ) . '</td>';
                    echo '<td>' . wp_kses_post( implode( '<br>', $active_plugins ) ) . '</td>';
                    echo '<td><a href="' . esc_url( $site_url . '/wp-admin/users.php' ) . '">' . esc_html( $users ) . '</a></td>';
                    echo '<td><a href="' . esc_url( $site_url . '/wp-admin/edit.php?post_type=page' ) . '">' . esc_html( $pages_count ) . '</a></td>';
                    echo '<td><a href="' . esc_url( $site_url . '/wp-admin/edit.php' ) . '">' . esc_html( $posts_count ) . '</a></td>';
                    echo '<td><a href="' . esc_url( $site_url . '/wp-admin/edit.php?post_type=tribe_events' ) . '">' . esc_html( $custom_post_type_count ) . '</a></td>';
                    echo '<td data-order="' . esc_attr( $registration_order ) . '">' . esc_html( $formatted_registration_date ) . '</td>';
                    echo '<td data-order="' . esc_attr( $last_updated_order ) . '">' . esc_html( $formatted_last_updated ) . '</td>';
                    echo '<td>' . wp_kses_post ( $document_summary ) . '</td>';
                    echo '</tr>';
                }
            else :
                echo '<tr><td colspan="13">No sites found.</td></tr>';
            endif;
            ?>
        </tbody>
    </table>
</div>
