<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'media-converter', [
	'section'     => 'media',
	'title'       => _x( 'Media Converter', 'Utility Title', 'ym-site-kit' ),
	'label'       => __( 'Enable conversion', 'ym-site-kit' ),
	'description' => __( 'Automatically compresses and converts images to WebP upon upload.', 'ym-site-kit' ),
	'callback'    => function () {
		if ( wp_image_editor_supports([ 'mime_type' => 'image/webp' ]) ) {
			// Sets output image formats.
			add_filter( 'image_editor_output_format', function ( array $formats ) : array {
				$formats[ 'image/jpeg' ] = 'image/webp';
				$formats[ 'image/png' ]  = 'image/webp';

				return $formats;
			});

			// Sets output image quality.
			add_filter( 'wp_editor_set_quality', fn () : int => 85 );

			// Converts uploaded JPG and PNG to WebP.
			add_filter( 'wp_handle_upload', function ( array $upload ) : array {
				// Check upload MIME-type.
				if ( ! in_array( $upload[ 'type' ], [ 'image/jpeg', 'image/png' ], true ) ) {
					return $upload;
				}

				// Get Editor.
				$editor = wp_get_image_editor( $upload[ 'file' ] );

				if ( is_wp_error( $editor ) ) {
					return $upload;
				}

				// Config Editor.
				$editor->set_quality( 85 );

				// Process file.
				$path      = $upload[ 'file' ];
				$directory = dirname( $path );
				$filename  = pathinfo( $path, PATHINFO_FILENAME ) . '.webp';

				$filename  = wp_unique_filename( $directory, $filename );
				$webp_path = path_join( $directory, $filename );

				$saved = $editor->save( $webp_path, 'image/webp' );

				if ( is_wp_error( $saved ) ) {
					return $upload;
				}

				// Delete original JPG/PNG.
				wp_delete_file( $path );

				// Update upload data.
				$upload[ 'file' ] = $webp_path;
				$upload[ 'url' ]  = trailingslashit( dirname( $upload[ 'url' ] ) ) . $filename;
				$upload[ 'type' ] = 'image/webp';

				return $upload;
			});
		}
	},
]);