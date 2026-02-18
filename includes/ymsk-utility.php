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
			'title'       => '',
			'label'       => '',
			'description' => '',
			'callback'    => fn () => null,
		]);

		// Set Utility parameters.
		$this->slug        = $slug;
		$this->is_beta     = $args[ 'is_beta' ];
		$this->title       = $args[ 'title' ] . ( $this->is_beta ? ' <sup>&beta;</sup>' : '' );
		$this->label       = $args[ 'label' ];
		$this->description = $args[ 'description' ];
		$this->callback    = $args[ 'callback' ];

		// Push Utility to Plugin static list.
		YMSK_Plugin::$utilities[ $this->slug ] = $this;

		// Call Utility if enabled.
		if ( $this->is_enabled() ) {
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
				'default',
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
	 * Retrieves `true` if Utility enabled.
	 * 
	 * @return bool
	 */
	public function is_enabled () : bool {
		return in_array( $this->slug, YMSK_Plugin::get_enabled_utilities() );
	}
}