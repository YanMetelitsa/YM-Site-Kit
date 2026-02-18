<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'maintenance-mode', [
	'title'       => __( 'Maintenance Mode', 'ym-site-kit' ),
	'label'       => __( 'Enable maintenance mode', 'ym-site-kit' ),
	'description' => __( 'Allows only administrators to access the public part of the site.', 'ym-site-kit' ),
	'callback'    => function () {
		// Redirects non-admin User to Maintenance template.
		add_filter( 'template_redirect', function () {
			if ( ! current_user_can( 'manage_options' ) ) {
				status_header( 503 );
				header( 'Retry-After: 3600' );
				nocache_headers();

				load_template( YMSK_ROOT_DIR . 'parts/maintenance.php' );

				exit;
			}
		}, 1 );

		// Adds admin bar notification.
		add_action( 'admin_bar_menu', function ( WP_Admin_Bar $wp_admin_bar ) {
			if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$wp_admin_bar->add_node([
				'id'    => 'ymsk-maintenance',
				'title' => sprintf( '⚠️ %s', esc_html__( 'Maintenance', 'ym-site-kit' ) ),
				'href'  => YMSK_Plugin::get_utilities_page_url( '#ymsk-maintenance-mode' ),
			]);
		}, 100 );
	},
]);