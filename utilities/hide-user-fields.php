<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'hide-user-fields', [
	'section'     => 'administration',
	'title'       => __( 'Hide User Fields', 'ym-site-kit' ),
	'label'       => __( 'Hide fields on the Edit User screen', 'ym-site-kit' ),
	'description' => __( 'Hides rarely used fields on the Edit User screen to simplify the interface.', 'ym-site-kit' ),
	'callback'    => function () {
		// Adds custom styles to hide fields.
		add_action( 'admin_enqueue_scripts', function () {
			$screen = get_current_screen();

			if ( $screen && in_array( $screen->base, [ 'profile', 'user-edit' ] ) ) {
				// CSS.
				wp_add_inline_style( 'ymsk-admin-style', sprintf( 'body:not( .ymsk-show-user-hidden-fields ) %s { display: none }',
					implode( ', body:not( .ymsk-show-user-hidden-fields ) ', [
						'.form-table tr.user-syntax-highlighting-wrap',
						'.form-table tr.user-admin-color-wrap',
						'.form-table tr.user-comment-shortcuts-wrap',
						'.form-table tr.show-admin-bar.user-admin-bar-front-wrap',
						'.form-table tr.user-language-wrap',
						'.form-table tr.user-url-wrap',
						'.form-table tr.user-description-wrap',
						'.application-passwords',
					]),
				));

				// Adds "Show Hidden Fields" button.
				add_action( 'personal_options', function ( WP_User $profile_user ) {
					?>

					<tr class="ym-site-kit-wrap">
						<th scope="row"><?php esc_html_e( 'Hidden Fields', 'ym-site-kit' ); ?></th>
						<td>
							<label for="ymsk-show-hidden-user-fields">
								<input type="checkbox" id="ymsk-show-hidden-user-fields" oninput="ymskShowHiddenUserFields( this )">
								<?php esc_html_e( 'Show Hidden Fields', 'ym-site-kit' ); ?>
							</label><br>
						</td>
					</tr>

					<script>
						function ymskShowHiddenUserFields ( checkbox ) {
							if ( checkbox.checked ) {
								document.body.classList.add( 'ymsk-show-user-hidden-fields' );
							} else {
								document.body.classList.remove( 'ymsk-show-user-hidden-fields' );
							}
						}
					</script>

					<?php
				}, 1 );
			}
		});
	},
]);