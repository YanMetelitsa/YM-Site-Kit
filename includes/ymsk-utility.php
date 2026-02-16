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
	 * Utility title.
	 * 
	 * @var string
	 */
	public string $title;

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
	 * 		@type string  $title       Utility title.
	 * 		@type string  $description Utility description.
	 * 		@type Closure $callback    Utility callback.
	 * }
	 */
	public function __construct ( string $slug, array $args = [] ) {
		// Set default arguments.
		$args = wp_parse_args( $args, [
			'title'       => '',
			'description' => '',
			'callback'    => fn () => null,
		]);

		// Set Utility parameters.
		$this->slug        = $slug;
		$this->title       = $args[ 'title' ];
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
					'title'       => $this->title,
					'slug'        => $this->slug,
					'label_for'   => $option_id,
					'is_enabled'  => $this->is_enabled(),
					'description' => $this->description,
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