<?php

defined( 'ABSPATH' ) || exit;

/**
 * YM Site Kit "Advanced Columns" Utility class.
 * 
 * @since 0.1.4
 */
class YMSK_Advanced_Columns_Utility extends YMSK_Utility {
	/**
	 * Inserts list table head thumbnail item in columns array.
	 * 
	 * @since 0.1.5
	 * 
	 * @return array
	 */
	public static function insert_thumbnail_th ( array $columns ) : array {
		$new_columns = [];

		foreach ( $columns as $key => $title ) {
			$new_columns[ $key ] = $title;

			if ( 'cb' == $key ) {
				$new_columns[ 'ymsk-thumbnail' ] = sprintf( '<span class="text">%s</span> <span class="dashicons dashicons-format-image"></span>',
					__( 'Thumbnail', 'ym-site-kit' ),
				);
			}
		}

		return $new_columns;
	}

	/**
	 * Prints list table body Post thumbnail item.
	 * 
	 * @since 0.1.5
	 * 
	 * @param int|WP_Post|null $post Optional. Post ID or `WP_Post` object. Default is global `$post`.
	 */
	public static function the_thumbnail_td ( int|WP_Post|null $post = null ) {
		if ( has_post_thumbnail( $post ) ) {
			echo get_the_post_thumbnail( $post, [ 40, 40 ] );
		} else {
			printf( '<span class="dashicons dashicons-%s"></span>',
				'publish' == get_post_status( $post ) ? 'format-image' : 'edit',
			);
		}
	}
}

new YMSK_Advanced_Columns_Utility( 'advanced-columns', [
	'title'       => __( 'Advanced Columns', 'ym-site-kit' ),
	'label'       => __( 'Display additional columns in list tables', 'ym-site-kit' ),
	'description' => __( 'Adds useful columns to Post, Page, Plugin, and other list tables, and hides some rarely used ones.', 'ym-site-kit' ),
	'callback'    => function () {
		// Post.
		add_filter( 'manage_post_posts_columns', function ( array $columns ) : array {
			return YMSK_Advanced_Columns_Utility::insert_thumbnail_th( $columns );
		});
		add_action( 'manage_post_posts_custom_column', function ( string $column, int $post_id ) {
			switch ( $column ) {
				case 'ymsk-thumbnail':
					YMSK_Advanced_Columns_Utility::the_thumbnail_td( $post_id );
					break;
			}
		}, 10, 2 );
		
		// Media.
		add_filter( 'manage_media_columns', function ( array $columns ) : array {
			if ( ! count( $columns ) ) {
				return $columns;
			}

			$rest = array_slice( $columns, 0, -1, true );
			$last = array_slice( $columns, -1, 1, true );
			
			/* translators: File size */
			return $rest + [ 'ymsk-filesize' => __( 'Size', 'ym-site-kit' ) ] + $last;
		});
		add_action( 'manage_media_custom_column', function ( string $column, int $attachment_id ) {
			switch ( $column ) {
				case 'ymsk-filesize':
					$file_path = get_attached_file( $attachment_id );

					if ( ! $file_path || ! file_exists( $file_path ) ) {
						return;
					}

					$file_size        = filesize( $file_path ) / 1024;
					$allowed_filesize = apply_filters( 'ymsk_advanced_columns_max_filesize', 400 );
					
					printf( '<%1$s>%2$s</%1$s>',
						$file_size <= $allowed_filesize ? 'span' : 'mark',
						sprintf( '%s %s',
							number_format( $file_size, 2 ),
							/* translators: Kilobytes */
							esc_html__( 'KB', 'ym-site-kit' ),
						),
					);
					
					break;
			}
		}, 10, 2 );

		// Page.
		add_filter( 'manage_page_posts_columns', function ( array $columns ) : array {
			if ( ! count( $columns ) ) {
				return $columns;
			}

			$rest = array_slice( $columns, 0, -1, true );
			$last = array_slice( $columns, -1, 1, true );
			
			/* translators: Page template */
			return $rest + [ 'ymsk-template' => __( 'Template', 'ym-site-kit' ) ] + $last;
		});
		add_action( 'manage_page_posts_custom_column', function ( string $column, int $page_id ) {
			switch ( $column ) {
				case 'ymsk-template':
					$template_slug = get_post_meta( $page_id, '_wp_page_template', true );
					$templates     = wp_get_theme()->get_page_templates();
					/* translators: Default page template */
					$template_name = $templates[ $template_slug ] ?? __( 'Default', 'ym-site-kit' );
					
					echo esc_html( $template_name );
					
					break;
			}
		}, 10, 2 );

		// All Taxonomies.
		add_action( 'admin_init', function () {
			foreach ( get_taxonomies( [], 'names' ) as $taxonomy ) {
				add_filter( "manage_edit-{$taxonomy}_columns", function ( array $columns ) : array {
					unset( $columns[ 'description' ] );

					return $columns;
				}, 20 );
			}
		});

		// Plugin.
		add_filter( 'manage_plugins_columns', function ( array $columns ) : array {		
			return YMSK_Advanced_Columns_Utility::insert_thumbnail_th( $columns );
		});
		add_action( 'manage_plugins_custom_column', function ( string $column, string $plugin_file, array $plugin_data ) {
			switch ( $column ) {
				case 'ymsk-thumbnail':
					if ( isset( $plugin_data[ 'icons' ][ '1x' ] ) ) {
						printf( '<img src="%s" alt="%s" with="40" height="40" decoding="async" loading="lazy">',
							esc_url( $plugin_data[ 'icons' ][ '1x' ] ),
							esc_attr( $plugin_data[ 'Title' ] ),
						);
					} else {
						echo '<span class="dashicons dashicons-admin-plugins"></span>';
					}
					
					break;
			}
		}, 10, 3 );
	},
]);