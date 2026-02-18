<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'comments-deactivator', [
	'title'       => __( 'Comments Deactivator', 'ym-site-kit' ),
	'label'       => __( 'Disable discussion features', 'ym-site-kit' ),
	'description' => __( 'Turns off the discussion system and hides comment-related elements in the admin interface.', 'ym-site-kit' ),
	'callback'    => function () {
		// Updates Discussion settings after Utility activation.
		add_action( 'ymsk_utilities_saved', function () {
			update_option( 'default_ping_status', 'closed' );
			update_option( 'default_comment_status', 'closed' );

			update_option( 'require_name_email', true );
			update_option( 'comment_registration', true );
			update_option( 'close_comments_for_old_posts', true );
			update_option( 'close_comments_days_old', 0 );

			update_option( 'comment_moderation', true );
			update_option( 'comment_previously_approved', true );
		});


		// Disables supports and hides some admin elements.
		add_action( 'admin_init', function () {
			// Hides Comments metabox from dashboard.
			remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' );
			
			// Disables support for Comments and Trackbacks in Post Types.
			foreach ( get_post_types() as $post_type ) {
				if ( post_type_supports( $post_type, 'comments' ) ) {
					remove_post_type_support( $post_type, 'comments' );
					remove_post_type_support( $post_type, 'trackbacks' );
				}
			}
		});

		// Hides admin menu Comments item.
		add_action( 'admin_menu', function () {
			remove_menu_page( 'edit-comments.php' );
		});

		// Hides admin bar Comments item.
		add_action( 'wp_before_admin_bar_render', function () {
			global $wp_admin_bar;
		
			$wp_admin_bar->remove_menu( 'comments' );
		});

		// Adds notification on Comments and Discussion pages.
		add_action( 'admin_notices', function () {
			$screen = get_current_screen();

			if ( $screen && in_array( $screen->id, [ 'edit-comments', 'options-discussion' ] ) ) {
				$utilities_url = sprintf( '<a href="%s">YM Site Kit</a>',
					esc_url( YMSK_Plugin::get_utilities_page_url( '#ymsk-comments-deactivator' ) ),
				);

				/* translators: %s: Utility name */
				$message = sprintf( __( 'The comments and discussion system has been deactivated by %s.', 'ym-site-kit' ),
					$utilities_url,
				);

				printf( '<div class="%s"><p>%s</p></div>',
					esc_attr( 'notice notice-warning' ),
					wp_kses_post( $message ),
				);
			}
		});


		// Closes Comments on the front-end.
		add_filter( 'comments_open', '__return_false', 20 );
		add_filter( 'pings_open', '__return_false', 20 );

		// Hides existing comments.
		add_filter( 'comments_array', '__return_empty_array' );
	},
]);