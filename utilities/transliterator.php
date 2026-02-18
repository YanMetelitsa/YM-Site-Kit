<?php

defined( 'ABSPATH' ) || exit;

/**
 * YM Site Kit "Transliterator" Utility class.
 * 
 * @since 0.1.4
 */
class YMSK_Transliterator_Utility extends YMSK_Utility {
	/**
	 * Retrieves transliteration map.
	 * 
	 * @return string[]
	 */
	public static function get_translit_map () : array {
		$translit_map = match ( get_locale() ) {
			'ru_RU' => [
				'а' => 'a',  'к' => 'k', 'х' => 'h',
				'б' => 'b',  'л' => 'l', 'ц' => 'c',
				'в' => 'v',  'м' => 'm', 'ч' => 'ch',
				'г' => 'g',  'н' => 'n', 'ш' => 'sh',
				'д' => 'd',  'о' => 'o', 'щ' => 'shch',
				'е' => 'e',  'п' => 'p', 'ъ' => '',
				'ё' => 'yo', 'р' => 'r', 'ы' => 'y',
				'ж' => 'zh', 'с' => 's', 'ь' => '',
				'з' => 'z',  'т' => 't', 'э' => 'e',
				'и' => 'i',  'у' => 'u', 'ю' => 'yu',
				'й' => 'j',  'ф' => 'f', 'я' => 'ya',
			],
			default => [],
		};

		ksort( $translit_map, SORT_LOCALE_STRING );

		return apply_filters( 'ymsk_transliterator_map', $translit_map );
	}
}

new YMSK_Transliterator_Utility( 'transliterator', [
	'is_beta'     => true,
	'title'       => __( 'Transliterator', 'ym-site-kit' ),
	'label'       => __( 'Enable transliteration', 'ym-site-kit' ),
	'description' => __( 'Automatically replaces certain symbols in Post and Term slugs on save.', 'ym-site-kit' ),
	'callback'    => function () {
		// Replaces some characters with Latin ones.
		add_filter( 'sanitize_title', function ( string $title, string $raw_title = '', string $context = '' ) : string {
			$title = urldecode( $title );
			$title = strtr( $title, YMSK_Transliterator_Utility::get_translit_map() );

			return $title;
		}, 10, 3 );
	},
]);