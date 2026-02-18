<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'hide-user-fields', [
	'title'       => __( 'Hide User Fields', 'ym-site-kit' ),
	'label'       => __( 'Hide fields on the Edit User screen', 'ym-site-kit' ),
	'description' => __( 'Hides rarely used fields on the Edit User screen to simplify the interface.', 'ym-site-kit' ),
	'callback'    => function () {
		// Adds custom styles to hide fields.
		add_action( 'admin_enqueue_scripts', function () {
			$screen = get_current_screen();

			if ( ! $screen ) {
				return;
			}

			if ( 'user-edit' == $screen->base || ( 'profile' == $screen->base && ! current_user_can( 'administrator' ) ) ) {
				$style = sprintf( '%s { display: none }',
					implode( ', ', [
						'.form-table tr.user-admin-color-wrap',
						'.form-table tr.user-comment-shortcuts-wrap',
						'.application-passwords',
					])
				);

				wp_add_inline_style( 'ymsk-admin-style', $style );
			}
		});
	},
]);