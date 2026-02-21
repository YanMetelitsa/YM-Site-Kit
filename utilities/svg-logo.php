<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'svg-logo', [
	'title'       => __( 'SVG Logo', 'ym-site-kit' ),
	'label'       => __( 'Output logo as inline SVG', 'ym-site-kit' ),
	/* translators: %1$s – <img>, %2$s – <svg>, %3$s – get_custom_logo() */
	'description' => sprintf( __( 'Replaces the %1$s element with %2$s when using %3$s function and the logo file is an SVG.', 'ym-site-kit' ),
		'<code>&lt;img&gt;</code>',
		'<code>&lt;svg&gt;</code>',
		'<code>get_custom_logo()</code>',
	),
	'callback'    => function () {
		// Replaces the `<img>` element with `<svg>` in `get_custom_logo()` output.
		add_filter( 'get_custom_logo', function ( string $html ) : string {
			$custom_logo_id = get_theme_mod( 'custom_logo' );

			if ( ! $custom_logo_id ) {
				return $html;
			}

			$custom_logo_path = get_attached_file( $custom_logo_id );

			if ( ! $custom_logo_path ) {
				return $html;
			}

			if ( 'svg' !== strtolower( pathinfo( $custom_logo_path, PATHINFO_EXTENSION ) ) ) {
				return $html;
			}

			$svg = file_get_contents( $custom_logo_path );

			if ( ! $svg ) {
				return $html;
			}

			$svg = preg_replace( '/<\?xml.*?\?>/', '', $svg );
			
			return preg_replace( '/<img\b[^>]*>/i', $svg, $html );
		});
	},
]);