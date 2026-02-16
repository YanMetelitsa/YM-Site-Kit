<?php

/*
 * Plugin Name:       YM Site Kit
 * Description:       Enhance your website with powerful mini‑utilities.
 * Plugin URI:        https://yanmet.com/blog/ym-site-kit-wordpress-plugin-documentation
 * Version:           0.1.3
 * Requires PHP:      7.4
 * Requires at least: 6.0
 * Tested up to:      6.9
 * Author:            Yan Metelitsa
 * Author URI:        https://yanmet.com/
 * License:           GPLv3
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain:       ym-site-kit
 */

// Exits if accessed directly.
defined( 'ABSPATH' ) || exit;

// Gets plugin data.
if ( ! function_exists( 'get_plugin_data' ) ) {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
}

// Defines Plugin constants.
define( 'YMSK_PLUGIN_DATA', get_plugin_data( __FILE__, true, false ) );
define( 'YMSK_ROOT_DIR', plugin_dir_path( __FILE__ ) );
define( 'YMSK_ROOT_URI', plugin_dir_url( __FILE__ ) );

// Includes Plugin components.
require_once YMSK_ROOT_DIR . 'includes/ymsk-utility.php';

/**
 * YM Site Kit main class.
 */
class YMSK_Plugin {
	/**
	 * List of registered Utilities.
	 * 
	 * @var YMSK_Utility[]
	 */
	public static array $utilities = [];

	/**
	 * Inits YM Site Kit Plugin.
	 */
	public function __construct () {
		// Redirects to Utilities page after Plugin activation.
		register_activation_hook( __FILE__, function () {
			add_option( 'ymsk-is-activation', wp_create_nonce( 'ymsk-activation' ) );
		});
		add_action( 'admin_init', function () {
			if ( wp_verify_nonce( get_option( 'ymsk-is-activation', '' ), 'ymsk-activation' ) ) {
				delete_option( 'ymsk-is-activation' );

				if ( ! isset( $_GET[ 'activate-multi' ] ) ) {
					wp_safe_redirect( YMSK_Plugin::get_utilities_page_url() );
					exit;
				}
			}
		});

		// Adds custom Plugin action links.
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( array $actions ) : array {
			$utilities_link = sprintf( '<a href="%s">%s</a>',
				esc_url( YMSK_Plugin::get_utilities_page_url() ),
				esc_html__( 'Utilities', 'ym-site-kit' ),
			);

			array_unshift( $actions, $utilities_link );

			return $actions;
		});

		// Connects Plugin styles and scripts.
		add_action( 'admin_enqueue_scripts', function () {
			wp_enqueue_style( 'ymsk-admin-style', YMSK_ROOT_URI . 'assets/css/ymsk-admin.css', [], YMSK_PLUGIN_DATA[ 'Version' ] );
			wp_enqueue_script( 'ymsk-admin-script', YMSK_ROOT_URI . 'assets/js/ymsk-admin.js', [], YMSK_PLUGIN_DATA[ 'Version' ], true );
		});

		// Adds Utilities page.
		add_action( 'admin_menu', function (){
			add_management_page(
				__( 'Utilities', 'ym-site-kit' ),
				__( 'Utilities', 'ym-site-kit' ),
				'manage_options',
				'ymsk-utilities',
				fn () => load_template( YMSK_ROOT_DIR . 'parts/utilities.php' ),
				1,
			);
		});
		
		// Registers Plugin settings.
		add_action( 'admin_init', function () {
			add_settings_section( 'default', '', fn () => null, 'ymsk-utilities', );

			register_setting( 'ymsk-settings', 'ymsk-enabled-utilities', [
				'default'           => [],
				'sanitize_callback' => function ( $input ) : array {
					$output = [];

					if ( is_array( $input ) ) {
						foreach ( $input as $key => $value ) {
							$safe_key   = sanitize_key( $key );
							$safe_value = sanitize_text_field( $value );

							$output[ $safe_key ] = $safe_value;
						}
					}

					return $output;
				},
			]);
		});

		// Registers Utilities.
		add_action( 'after_setup_theme', function () {
			new YMSK_Utility( 'advanced-columns', [
				'title'       => __( 'Advanced Columns', 'ym-site-kit' ),
				'description' => __( 'Adds new and removes unnecessary columns in list tables', 'ym-site-kit' ),
				'callback'    => function () {
					// Post.
					add_filter( 'manage_post_posts_columns', function ( array $columns ) : array {
						return isset( $columns[ 'cb' ] ) ? [
							'cb'              => $columns[ 'cb' ],
							'ymsk-thumbnail'  => '<span class="dashicons dashicons-format-image"></span>',
							'title'           => $columns[ 'title' ],
							'author'          => $columns[ 'author' ],
							'categories'      => $columns[ 'categories' ],
							'tags'            => $columns[ 'tags' ],
							'date'            => $columns[ 'date' ],
						] : $columns;
					});
					add_action( 'manage_post_posts_custom_column' , function ( string $column, int $post_id ) {
						switch ( $column ) {
							case 'ymsk-thumbnail':
								if ( has_post_thumbnail( $post_id ) ) {
									echo get_the_post_thumbnail( $post_id, [ 40, 40 ] );
								} else {
									printf( '<span class="dashicons dashicons-%s"></span>',
										'publish' == get_post_status( $post_id ) ? 'format-image' : 'edit',
									);
								}
								break;
						}
					}, 10, 2 );
					
					// Media.
					add_filter( 'manage_media_columns', function ( array $columns ) : array {
						return isset( $columns[ 'cb' ] ) ? [
							'cb'            => $columns[ 'cb' ],
							'title'         => $columns[ 'title' ],
							'author'        => $columns[ 'author' ],
							'parent'        => $columns[ 'parent' ],
							'ymsk-filesize' => __( 'Size', 'ym-site-kit' ),
							'date'          => $columns[ 'date' ],
						] : $columns;
					});
					add_action( 'manage_media_custom_column', function ( string $column, int $attachment_id ) {
						switch ( $column ) {
							case 'ymsk-filesize':
								$file_path = get_attached_file( $attachment_id );

								if ( ! $file_path || ! file_exists( $file_path ) ) {
									return;
								}

								$file_size        = filesize( $file_path ) / 1024;
								$allowed_filesize = apply_filters( 'ymsk_advanced_columns_max_filesize', 400 );
								
								printf( '<%1$s>%2$s</%1$s>',
									$file_size <= $allowed_filesize ? 'span' : 'mark',
									sprintf( '%s %s',
										number_format( $file_size, 2 ),
										/* translators: Kilobytes */
										esc_html__( 'KB', 'ym-site-kit' ),
									),
								);
								
								break;
						}
					}, 10, 2 );

					// Page.
					add_filter( 'manage_page_posts_columns', function ( array $columns ) : array {
						return isset( $columns[ 'cb' ] ) ? [
							'cb'            => $columns[ 'cb' ],
							'title'         => $columns[ 'title' ],
							'author'        => $columns[ 'author' ],
							'ymsk-template' => __( 'Template', 'ym-site-kit' ),
						] : $columns;
					});
					add_action( 'manage_page_posts_custom_column', function ( string $column, int $page_id ) {
						switch ( $column ) {
							case 'ymsk-template':
								$template_slug = get_post_meta( $page_id, '_wp_page_template', true );
								$templates     = wp_get_theme()->get_page_templates();
								$template_name = $templates[ $template_slug ] ?? __( 'Default', 'ym-site-kit' );
								
								echo esc_html( $template_name );
								
								break;
						}
					}, 10, 2 );


					// Taxonomies.
					add_action( 'admin_init', function () {
						foreach ( get_taxonomies( [], 'names' ) as $taxonomy ) {
							add_filter( "manage_edit-{$taxonomy}_columns", function ( array $columns ) : array {
								unset( $columns[ 'description' ] );

								return $columns;
							}, 20 );
						}
					});
				},
			]);

			new YMSK_Utility( 'comments-deactivator', [
				'title'       => __( 'Comments Deactivator', 'ym-site-kit' ),
				'description' => __( 'Disables discussion features', 'ym-site-kit' ),
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
						// Removes Comments metabox from dashboard.
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
								esc_url( YMSK_Plugin::get_utilities_page_url() . '#ymsk-comments-deactivator' ),
							);

							/* translators: %s: Utility name */
							$message = sprintf( __( 'The comments/discussion functionality was deactivated via %s.', 'ym-site-kit' ),
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

			new YMSK_Utility( 'media-converter', [
				'title'       => __( 'Media Converter', 'ym-site-kit' ),
				'description' => __( 'Converts and compresses JPG and PNG files into WebP format', 'ym-site-kit' ),
				'callback'    => function () {
					if ( wp_image_editor_supports([ 'mime_type' => 'image/webp' ]) ) {
						// Converts JPG and PNG to WebP.
						add_filter( 'image_editor_output_format', function ( array $formats ) : array {
							$formats[ 'image/jpeg' ] = 'image/webp';
							$formats[ 'image/png' ]  = 'image/webp';

							return $formats;
						});

						// Deletes original file.
						add_filter( 'wp_generate_attachment_metadata', function ( array $metadata, int $attachment_id ) : array {
							if ( empty( $metadata[ 'original_image' ] ) ) {
								return $metadata;
							}

							$attached_file_path = get_attached_file( $attachment_id );

							if ( ! $attached_file_path || ! file_exists( $attached_file_path ) ) {
								return $metadata;
							}

							$attached_file_extension = strtolower( pathinfo( $attached_file_path, PATHINFO_EXTENSION ) );

							if ( 'webp' != $attached_file_extension ) {
								return $metadata;
							}

							$original_file_path = path_join( dirname( $attached_file_path ), $metadata[ 'original_image' ] );
							
							if ( file_exists ( $original_file_path ) && wp_delete_file( $original_file_path ) ) {
								unset( $metadata[ 'original_image' ] );

								wp_update_post([
									'ID'             => $attachment_id,
									'post_mime_type' => 'image/webp',
								]);
							}

							return $metadata;
						}, 20, 2 );

						// Sets output image quality.
						add_filter( 'wp_editor_set_quality', fn () : int => 85 );
					}
				},
			]);

			new YMSK_Utility( 'hide-user-fields', [
				'title'       => __( 'Hide User Fields', 'ym-site-kit' ),
				'description' => __( 'Hides specific fields on the User Edit page', 'ym-site-kit' ),
				'callback'    => function () {
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

			new YMSK_Utility( 'maintenance-mode', [
				'title'       => __( 'Maintenance Mode', 'ym-site-kit' ),
				'description' => __( 'Allows only logged-in Users to access the site', 'ym-site-kit' ),
				'callback'    => function () {
					// Redirects not logged-in User to Maintenance template.
					add_filter( 'template_redirect', function () {
						if ( ! is_user_logged_in() ) {
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
							'href'  => YMSK_Plugin::get_utilities_page_url() . '#ymsk-maintenance-mode',
						]);
					}, 100 );
				},
			]);
		});
	}

	/**
	 * Retrieves Utilities page URL.
	 * 
	 * @since 0.1.3
	 * 
	 * @return string
	 */
	public static function get_utilities_page_url () : string {
		return admin_url( 'tools.php?page=ymsk-utilities' );
	}

	/**
	 * Retrieves list of enabled Utilities.
	 * 
	 * @return array
	 */
	public static function get_enabled_utilities () : array {
		$value = get_option( 'ymsk-enabled-utilities', [] );

		return array_values( $value ?: [] );
	}

	/**
	 * Retrieves `true` if specific Utility enabled.
	 * 
	 * @since 0.1.3
	 * 
	 * @param string $slug Utility slug.
	 * 
	 * @return bool
	 */
	public static function is_utility_enabled ( string $slug = '' ) : bool {
		if ( ! isset( YMSK_Plugin::$utilities[ $slug ] ) ) {
			return false;
		}

		return YMSK_Plugin::$utilities[ $slug ]->is_enabled();
	}
}

new YMSK_Plugin();