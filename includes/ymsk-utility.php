<?php

defined( 'ABSPATH' ) || exit;

/**
 * YM Site Kit Utility class.
 */
class YMSK_Utility {
	/**
	 * Utility slug.
	 * 
	 * @var string
	 */
	public string $slug;

	/**
	 * Is beta version.
	 * 
	 * @since 0.1.4
	 * 
	 * @var bool
	 */
	public string $is_beta;

	/**
	 * Utility section on settings screen.
	 * 
	 * @since 0.1.6
	 * 
	 * @var bool
	 */
	public string $section;

	/**
	 * Utility title.
	 * 
	 * @var string
	 */
	public string $title;

	/**
	 * Utility label.
	 * 
	 * @since 0.1.4
	 * 
	 * @var string
	 */
	public string $label;

	/**
	 * Utility description.
	 * 
	 * @var string
	 */
	public string $description;

	/**
	 * Utility callback.
	 * 
	 * @var Closure
	 */
	public Closure $callback;

	/**
	 * Creates new YM Site Kit Utility.
	 * 
	 * @param string $slug Utility slug.
	 * @param array  $args {
	 * 		Utility arguments.
	 * 
	 * 		@type bool    $is_beta     Is beta version.
	 * 		@type string  $section     Utility section on settings screen.
	 * 		@type string  $title       Utility title.
	 * 		@type string  $label       Utility label.
	 * 		@type string  $description Utility description.
	 * 		@type Closure $callback    Utility callback.
	 * }
	 */
	public function __construct ( string $slug, array $args = [] ) {
		// Set default arguments.
		$args = wp_parse_args( $args, [
			'is_beta'     => false,
			'section'     => 'default',
			'title'       => '',
			'label'       => '',
			'description' => '',
			'callback'    => fn () => null,
		]);

		// Set Utility parameters.
		$this->slug        = $slug;
		$this->is_beta     = $args[ 'is_beta' ];
		$this->section     = $args[ 'section' ];
		$this->title       = $args[ 'title' ] . ( $this->is_beta ? ' <sup>&beta;</sup>' : '' );
		$this->label       = $args[ 'label' ];
		$this->description = $args[ 'description' ];
		$this->callback    = $args[ 'callback' ];

		// Push Utility to Plugin static list.
		YMSK_Plugin::$utilities[ $this->slug ] = $this;

		// Call Utility if enabled.
		if ( $this->is_enabled() ) {
			// Connects site styles and scripts.
			add_action( 'wp_enqueue_scripts', function () {
				$frontend_styles_path  = "assets/css/ymsk-{$this->slug}.css";
				$frontend_scripts_path = "assets/js/ymsk-{$this->slug}.js";

				if ( file_exists( YMSK_ROOT_DIR . $frontend_styles_path ) ) {
					wp_enqueue_style( "ymsk-{$this->slug}-style", YMSK_ROOT_URI . $frontend_styles_path, [], YMSK_PLUGIN_DATA[ 'Version' ] );
				}

				if ( file_exists( YMSK_ROOT_DIR . $frontend_scripts_path ) ) {
					wp_enqueue_script( "ymsk-{$this->slug}-script", YMSK_ROOT_URI . $frontend_scripts_path, [], YMSK_PLUGIN_DATA[ 'Version' ], true );
				}
			});

			// Connects admin styles and scripts.
			add_action( 'admin_enqueue_scripts', function () {
				$admin_styles_path  = "assets/css/ymsk-{$this->slug}-admin.css";
				$admin_scripts_path = "assets/js/ymsk-{$this->slug}-admin.js";

				if ( file_exists( YMSK_ROOT_DIR . $admin_styles_path ) ) {
					wp_enqueue_style( "ymsk-{$this->slug}-admin-style", YMSK_ROOT_URI . $admin_styles_path, [], YMSK_PLUGIN_DATA[ 'Version' ] );
				}

				if ( file_exists( YMSK_ROOT_DIR . $admin_scripts_path ) ) {
					wp_enqueue_script( "ymsk-{$this->slug}-admin-script", YMSK_ROOT_URI . $admin_scripts_path, [], YMSK_PLUGIN_DATA[ 'Version' ], true );
				}
			});

			$args[ 'callback' ]();
		}

		// Add Utility enable/disable checkbox.
		$this->add_toggle_checkbox();
	}

	/**
	 * Adds new Utility switcher field.
	 */
	private function add_toggle_checkbox () {
		// Adds toggle checkbox field.
		add_action( 'admin_init', function () {
			$option_id = "ymsk-{$this->slug}";
			
			add_settings_field(
				$option_id,
				$this->title,
				fn ( $args ) => load_template( YMSK_ROOT_DIR . 'parts/checkbox.php', false, $args ),
				'ymsk-utilities',
				$this->section,
				[
					'slug'        => $this->slug,
					'title'       => $this->title,
					'label'       => $this->label,
					'label_for'   => $option_id,
					'description' => $this->description,
					'is_enabled'  => $this->is_enabled(),
				],
			);
		});
	}

	/**
	 * Returns `true` if Utility enabled.
	 * 
	 * @return bool
	 */
	public function is_enabled () : bool {
		return in_array( $this->slug, YMSK_Plugin::get_enabled_utilities() );
	}
}