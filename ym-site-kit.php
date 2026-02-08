<?php

/*
 * Plugin Name:       YM Site Kit
 * Description:       Enhance your website with powerful mini utilities.
 * Version:           0.1.1
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

// Defines Plugin constants.
define( 'YMSK_ROOT_DIR', plugin_dir_path( __FILE__ ) );

// Includes Plugin components.
require_once YMSK_ROOT_DIR . 'includes/ymsk-utility.php';

/**
 * YM Site Kit main class.
 */
class YM_Site_Kit {
	/**
	 * Enabled Utilities option name.
	 * 
	 * @var string
	 */
	public static string $enabled_utilities_option_name = 'ymsk-enabled-utilities';

	/**
	 * Inits YM Site Kit Plugin.
	 */
	public function __construct () {
		// Adds custom Plugin action links.
		add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), function ( array $actions ) : array {
			$utilities_link = sprintf( '<a href="%s" id="utilities-ym-site-kit" aria-label="%s">%s</a>',
				esc_url( admin_url( 'tools.php?page=ym-site-kit' ) ),
				esc_html__( 'Open YM Site Kit Utilities', 'ym-site-kit' ),
				esc_html__( 'Utilities', 'ym-site-kit' ),
			);

			array_splice( $actions, 1, 0, $utilities_link );

			return $actions;
		});

		// Adds YM Site Kit (Utilities) page.
		add_action( 'admin_menu', function (){
			add_management_page(
				__( 'Utilities', 'ym-site-kit' ),
				__( 'Utilities', 'ym-site-kit' ),
				'manage_options',
				'ym-site-kit',
				fn () => include YMSK_ROOT_DIR . 'parts/page.php',
				1,
			);
		});
		
		// Registers YM Site Kit settings.
		add_action( 'admin_init', function () {
			add_settings_section( 'default', '', fn () => null, 'ym-site-kit', );

			register_setting( 'ym-site-kit', YM_Site_kit::$enabled_utilities_option_name, [
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

		YM_Site_Kit::register_utilities();
	}

	/**
	 * Registers Utilities.
	 */
	private function register_utilities () {
		// Registers Utilities.
		add_action( 'after_setup_theme', function () {
			new YM_Utility( 'comments-deactivator', [
				'title'       => __( 'Comments Deactivator', 'ym-site-kit' ),
				'description' => __( 'Disables discussion features', 'ym-site-kit' ),
				'callback'    => function () {
					add_action( 'init', function () {
						remove_post_type_support( 'post', 'comments' );
						remove_post_type_support( 'page', 'comments' );
					});
					add_action( 'admin_menu', function () {
						remove_menu_page( 'edit-comments.php' );
					});
					add_action( 'wp_before_admin_bar_render', function () {
						global $wp_admin_bar;
					
						$wp_admin_bar->remove_menu( 'comments' );
					});
				},
			]);

			new YM_Utility( 'media-converter', [
				'title'       => __( 'Media Converter', 'ym-site-kit' ),
				'description' => __( 'Converts and compresses JPG and PNG files into WebP format', 'ym-site-kit' ),
				'callback'    => function () {
					add_filter( 'wp_handle_upload', function ( $upload ) {
						$allowed_types = [ 'image/jpeg', 'image/png', 'image/webp' ];
						$max_side_px   = 2048;

						// Checks.
						if ( ! empty( $upload[ 'error' ] ) ) {
							return $upload;
						}
						
						if ( ! in_array( $upload[ 'type' ], $allowed_types, true ) ) {
							return $upload;
						}
						
						// Creates Editor.
						$editor = wp_get_image_editor( $upload[ 'file' ] );
						
						if ( is_wp_error( $editor ) ) {
							return $upload;
						}
						
						// Resizes big image.
						$upload_size = $editor->get_size();
						
						if ( max( $upload_size ) > $max_side_px ) {
							$editor->resize( $max_side_px, $max_side_px, false );
						}
						
						$editor->set_quality( 85 );
						
						// Collects data.
						$upload_path_info = pathinfo( $upload[ 'file' ] );
						$new_upload_name  = wp_unique_filename( $upload_path_info[ 'dirname' ], "{$upload_path_info[ 'filename' ]}.webp" );
						$new_upload_path  = trailingslashit( $upload_path_info[ 'dirname' ] ) . $new_upload_name;
						
						// Saves new upload.
						$saved = $editor->save( $new_upload_path, 'image/webp' );
						
						if ( is_wp_error( $saved ) ) {
							return $upload;
						}
						
						// Deletes old upload.
						if ( 'image/webp' !== $upload[ 'type' ] && file_exists( $upload[ 'file' ] ) ) {
							wp_delete_file( $upload[ 'file' ] );
						}
						
						// Retrieves new data.
						$upload[ 'file' ] = $saved[ 'path' ]; 
						$upload[ 'type' ] = 'image/webp';
						$upload[ 'url' ]  = trailingslashit( dirname( $upload[ 'url' ] ) ) . basename( $new_upload_path );
						
						return $upload;
					}, 5 );
				},
			]);

			new YM_Utility( 'hide-user-fields', [
				'title'       => __( 'Hide User Fields', 'ym-site-kit' ),
				'description' => __( 'Hides specific fields on the User Edit page', 'ym-site-kit' ),
				'callback'    => function () {
					add_action( 'admin_head-user-edit.php', function () {
						printf( '<style>%s { display: none }</style>', implode( ', ', [
							'.form-table tr.user-admin-color-wrap',
							'.form-table tr.user-comment-shortcuts-wrap',
							'.application-passwords',
						]));
					});
				},
			]);

			new YM_Utility( 'maintenance-mode', [
				'title'       => __( 'Maintenance Mode', 'ym-site-kit' ),
				'description' => __( 'Allows only logged-in Users to access the site', 'ym-site-kit' ),
				'callback'    => function () {
					add_filter( 'template_redirect', function () {
						if ( ! is_user_logged_in() ) {
							status_header( 503 );
							header( 'Retry-After: 3600' );
							nocache_headers();
					
							printf( '<h1>%s</h1>', esc_html__( 'Maintenance', 'ym-site-kit' ) );
							exit;
						}
					});
				},
			]);
		});
	}

	/**
	 * Retrieves list of enabled Utilities.
	 * 
	 * @return array
	 */
	public static function get_enabled_utilities () : array {
		$value = get_option( YM_Site_kit::$enabled_utilities_option_name, [] );

		return array_values( $value ?: [] );
	}
}

new YM_Site_Kit();