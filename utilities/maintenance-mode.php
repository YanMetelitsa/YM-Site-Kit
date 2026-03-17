<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'maintenance-mode', [
	'section'     => 'administration',
	'title'       => __( 'Maintenance Mode', 'ym-site-kit' ),
	'label'       => __( 'Enable maintenance mode', 'ym-site-kit' ),
	'description' => __( 'Allows only administrators to access the public part of the site.', 'ym-site-kit' ),
	'callback'    => function () {
		// Redirects non-admin User to Maintenance template.
		add_filter( 'template_redirect', function () {
			// Allow admins.
			if ( current_user_can( 'manage_options' ) ) {
				return;
			}

			// Allow AJAX.
			if ( wp_doing_ajax() ) {
				return;
			}

			// Allow REST.
			if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
				return;
			}

			// Allow CRON.
			if ( wp_doing_cron() ) {
				return;
			}

			// Allow WooCommerce API.
			if ( isset( $_SERVER[ 'REQUEST_URI' ] ) ) {
				$request_uri = sanitize_url( wp_unslash( $_SERVER[ 'REQUEST_URI' ] ) );

				if ( str_contains( $request_uri, '/wc-api/' ) ) {
					return;
				}
			}

			status_header( 503 );
			header( 'Cache-Control: no-store, no-cache, must-revalidate, max-age=0' );
			header( 'Retry-After: 3600' );
			nocache_headers();

			$template_path = YMSK_ROOT_DIR . 'parts/maintenance.php';

			if ( file_exists( get_theme_file_path( 'maintenance.php' ) ) ) {
				$template_path = get_theme_file_path( 'maintenance.php' );
			}

			load_template( $template_path );

			exit;
		}, 0 );

		// Adds admin bar notification.
		add_action( 'admin_bar_menu', function ( WP_Admin_Bar $wp_admin_bar ) {
			if ( ! is_admin_bar_showing() || ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$wp_admin_bar->add_node([
				'id'    => 'ymsk-maintenance',
				'title' => sprintf( '⚠️ <span class="ab-label">%s</span>', esc_html__( 'Maintenance', 'ym-site-kit' ) ),
				'href'  => YMSK_Plugin::get_utilities_page_url( '#ymsk-maintenance-mode' ),
				'meta'  => [
					'title' => __( 'The site is under maintenance mode', 'ym-site-kit' ),
				],
			]);
		}, 100 );
	},
]);