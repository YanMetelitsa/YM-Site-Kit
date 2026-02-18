<?php

/*
 * Plugin Name:       YM Site Kit
 * Description:       Enhance your website with powerful mini‑utilities.
 * Plugin URI:        https://yanmet.com/blog/ym-site-kit-wordpress-plugin-documentation
 * Version:           0.1.4
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

		// Adds Help tab on the Utilities page.
		add_action( 'current_screen', function ( $screen ) {
			if ( ! $screen || 'tools_page_ymsk-utilities' !== $screen->id ) {
				return;
			}

			$screen->add_help_tab([
				'id'      => 'ymsk-help',
				'title'   => 'YM Site Kit',
				'content' => sprintf( '<p>%s</p>',
					/* translators: %s: Documentation URL */
					sprintf( __( 'See the <a href="%s">official documentation</a> for more details.', 'ym-site-kit' ),
						esc_url( 'https://yanmet.com/blog/ym-site-kit-wordpress-plugin-documentation' ),
					),
				),
			]);
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
			foreach ( glob( YMSK_ROOT_DIR . 'utilities/*.php' ) as $utility_file ) {
				if ( file_exists( $utility_file ) ) {
					require_once $utility_file;
				}
			}
		});
	}

	/**
	 * Retrieves Utilities page URL.
	 * 
	 * @since 0.1.3
	 * 
	 * @param string $tale A string to add to the end of the link. Defaults to an empty string.
	 * 
	 * @return string
	 */
	public static function get_utilities_page_url ( string $tale = '' ) : string {
		return admin_url( "tools.php?page=ymsk-utilities{$tale}" );
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