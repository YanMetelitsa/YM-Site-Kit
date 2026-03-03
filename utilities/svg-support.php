<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'svg-support', [
	'section'     => 'media',
	'title'       => __( 'SVG Support', 'ym-site-kit' ),
	'label'       => __( 'Enable SVG support', 'ym-site-kit' ),
	'description' => __( 'Allows administrators to safely upload SVG files to the media library and use them.', 'ym-site-kit' ),
	'callback'    => function () {
		// Allows administrators to upload SVG files.
		add_filter( 'upload_mimes', function ( array $mimes ) : array {
			if ( current_user_can( 'manage_options' ) ) {
				$mimes[ 'svg' ] = 'image/svg+xml';
			}

			return $mimes;
		});

		// SVG mime type correction.
		add_filter( 'wp_check_filetype_and_ext', function ( array $data, string $file, string $filename, ?array $mimes, string|false $real_mime = '' ) : array {
			$is_svg = version_compare( $GLOBALS[ 'wp_version' ], '5.1.0', '>=' )
				? in_array( $real_mime, [ 'image/svg', 'image/svg+xml' ] )
				: ( '.svg' === strtolower( substr( $filename, -4 ) ) );

			if ( $is_svg ){
				if ( current_user_can( 'manage_options' ) ){
					$data[ 'ext' ]  = 'svg';
					$data[ 'type' ] = 'image/svg+xml';
				} else {
					$data[ 'ext' ]  = false;
					$data[ 'type' ] = false;
				}
			}

			return $data;
		}, 10, 5 );
	},
]);