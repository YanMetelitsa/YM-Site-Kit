<?php

defined( 'ABSPATH' ) || exit;

new YMSK_Utility( 'media-converter', [
	'title'       => __( 'Media Converter', 'ym-site-kit' ),
	'label'       => __( 'Enable conversion', 'ym-site-kit' ),
	'description' => __( 'Automatically compresses and converts images to WebP upon upload.', 'ym-site-kit' ),
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