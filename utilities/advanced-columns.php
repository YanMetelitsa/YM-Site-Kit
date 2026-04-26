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
	'section'     => 'administration',
	'title'       => _x( 'Advanced Columns', 'Utility Title', 'ym-site-kit' ),
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
			
			return $rest + [ 'ymsk-filesize' => __( 'Size', 'ym-site-kit' ) ] + $last;
		});
		add_action( 'manage_media_custom_column', function ( string $column, int $attachment_id ) {
			switch ( $column ) {
				case 'ymsk-filesize':
					// Get file path.
					$file_path = get_attached_file( $attachment_id );

					if ( ! $file_path || ! file_exists( $file_path ) ) {
						echo '–';
						break;
					}

					// Get file size.
					$file_size           = filesize( $file_path ) / 1024;
					$formatted_file_size = sprintf( '%s %s',
						number_format( $file_size, 2 ),
						esc_html__( 'KB', 'ym-site-kit' ),
					);

					// Break if file is not image.
					if ( ! wp_attachment_is_image( $attachment_id ) ) {
						echo esc_html( $formatted_file_size );
						break;
					}

					// Output file size, maybe with mark.
					$allowed_file_size = apply_filters( 'ymsk_advanced_columns_max_file_size', 400 );
					
					printf( '<%1$s>%2$s</%1$s>',
						esc_attr( $file_size <= $allowed_file_size ? 'span' : 'mark' ),
						esc_html( $formatted_file_size ),
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
			
			return $rest + [ 'ymsk-template' => __( 'Template', 'ym-site-kit' ) ] + $last;
		});
		add_action( 'manage_page_posts_custom_column', function ( string $column, int $page_id ) {
			switch ( $column ) {
				case 'ymsk-template':
					$template_slug = get_post_meta( $page_id, '_wp_page_template', true );
					$templates     = wp_get_theme()->get_page_templates();
					$template_name = $templates[ $template_slug ] ?? __( 'Default', 'ym-site-kit' );
					
					echo esc_html( $template_name );
					
					break;
			}
		}, 10, 2 );

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


		// Columns for Custom Post Types.
		add_action( 'init', function () {
			// ACF/SCF Field Group.
			if ( class_exists( 'ACF' ) ) {
				add_filter( 'manage_acf-field-group_posts_columns', function ( array $columns ) : array {
					$new_columns = [];
					
					foreach ( $columns as $key => $label ) {
						$new_columns[ $key ] = $label;
						
						if ( 'title' === $key ) {
							$new_columns[ 'acf-display-title' ] = _x( 'Display Title', 'noun', 'ym-site-kit' );
						}
					}
					
					return $new_columns;
				}, 20 );
				add_action( 'manage_acf-field-group_posts_custom_column', function ( string $column, int $group_id ) {
					$field_group = acf_get_field_group( $group_id ); // phpcs:ignore
					
					switch ( $column ) {
						case 'acf-display-title':
							echo esc_html( $field_group[ 'display_title' ] ?: $field_group[ 'title' ] );
							break;
					}
				}, 20, 2 );
			}

			// Post Types with page attributes (order).
			foreach ( get_post_types_by_support( 'page-attributes' ) as $post_type ) {
				add_filter( "manage_{$post_type}_posts_columns", function ( array $columns ) : array {
					$columns[ 'ymsk-order' ] = _x( 'Order', 'sorting', 'ym-site-kit' );

					return $columns;
				});
				add_action( "manage_{$post_type}_posts_custom_column", function ( string $column, int $post_id ) {
					switch ( $column ) {
						case 'ymsk-order':
							echo esc_html( get_post_field( 'menu_order', $post_id ) );
							break;
					}
				}, 10, 2 );
			}
		});


		// Sets default hidden columns.
		add_filter( 'default_hidden_columns', function( array $hidden, \WP_Screen $screen ) : array {
			$hidden[] = 'ymsk-order';
			
			// Posts.
			if ( 'edit-post' !== $screen->id ) {
				$hidden[] = 'date';
			}

			// Users.
			if ( 'users' === $screen->id ) {
				$hidden[] = 'posts';
				$hidden[] = 'user_jetpack';
			}

			// Taxonomies.
			foreach ( get_taxonomies( [], 'names' ) as $taxonomy ) {
				if ( "edit-{$taxonomy}" === $screen->id ) {
					$hidden[] = 'description';
				}
			}

			// ACF / SCF.
			$hidden[] = 'acf-display-title';
			$hidden[] = 'acf-description';
			$hidden[] = 'acf-key';

			return $hidden;
		}, 10, 2 );
	},
]);