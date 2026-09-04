<?php

defined( 'ABSPATH' ) || exit;

class YMSK_Duplicator extends YMSK_Utility {
	/**
	 * Filters Post row actions.
	 * 
	 * @param array   $actions Actions array.
	 * @param WP_Post $post    Post object.
	 * 
	 * @return array
	 */
	public static function row_actions_filter ( array $actions, \WP_Post $post ) : array {
		if ( ! current_user_can( 'edit_post', $post->ID ) ) {
			return $actions;
		}

		if ( 'trash' === get_post_status( $post ) ) {
			return $actions;
		}

		$actions[ 'ymsk-duplicate'] = sprintf( '<a href="%s">%s</a>',
			wp_nonce_url(
				admin_url( 'admin-post.php?action=ymsk_duplicate&post_id=' . $post->ID ),
				"ymsk_duplicate_{$post->ID}"
			),
			__( 'Duplicate', 'ym-site-kit' ),
		);
		
		return $actions;
	}

	/**
	 * Duplicates Post.
	 */
	public static function handle_duplicate_action () {
		// Check parameters.
		if ( ! isset( $_GET[ '_wpnonce' ] ) || ! isset( $_GET[ 'post_id' ] ) ) {
			wp_die();
		}
		
		$post_id = intval( $_GET[ 'post_id' ] );
		
		// Check nonce.
		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET[ '_wpnonce' ] ) ), "ymsk_duplicate_{$post_id}" ) ) {
			wp_die();
		}
		
		// Check capability.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			wp_die();
		}
		
		$original = get_post( $post_id );

		if ( ! $original ) {
			wp_die();
		}
		
		$new_post_id = wp_insert_post([
			'post_type'      => $original->post_type,

			'post_title'     => sprintf( '%s (%s)', $original->post_title, __( 'copy', 'ym-site-kit' ) ),
			'post_content'   => $original->post_content,
			'post_excerpt'   => $original->post_excerpt,

			'post_parent'    => $original->post_parent,
			'post_author'    => get_current_user_id(),
			'post_status'    => 'draft',

			'post_date'      => current_time( 'mysql' ),
			'post_date_gmt'  => current_time( 'mysql', 1 ),
		]);
		
		if ( is_wp_error( $new_post_id ) ) {
			wp_die( esc_html( $new_post_id->get_error_message() ) );
		}
		
		// Terms.
		foreach ( get_object_taxonomies( $original->post_type ) as $tax ) {
			$terms = wp_get_object_terms( $post_id, $tax, ['fields' => 'slugs' ] );

			if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
				wp_set_object_terms( $new_post_id, $terms, $tax );
			}
		}
		
		// Meta fields.
		foreach ( get_post_custom_keys( $post_id ) as $meta_key ) {
			if ( in_array( $meta_key, [ '_wp_old_slug', '_wp_old_date', '_edit_lock', '_edit_last' ] ) ) {
				continue;
			}

			foreach ( get_post_meta( $post_id, $meta_key ) as $meta_value ) {
				add_post_meta( $new_post_id, $meta_key, maybe_unserialize( $meta_value ) );
			}
		}
		
		// Redirect.
		wp_safe_redirect( admin_url( 'post.php?action=edit&post=' . $new_post_id ) );
		exit;
	}
}

new YMSK_Duplicator( 'duplicator', [
	'section'     => 'administration',
	'title'       => _x( 'Duplicator', 'Utility Title', 'ym-site-kit' ),
	'label'       => __( 'Enable duplicator', 'ym-site-kit' ),
	'description' => __( 'Allows to create copies of pages and posts.', 'ym-site-kit' ),
	'callback'    => function () {
		// Adds action to Post / Page row.
		add_filter( 'post_row_actions', [ 'YMSK_Duplicator', 'row_actions_filter' ], 10, 2 );
		add_filter( 'page_row_actions', [ 'YMSK_Duplicator', 'row_actions_filter' ], 10, 2 );

		// Provides duplicate event.
		add_action( 'admin_post_ymsk_duplicate', [ 'YMSK_Duplicator', 'handle_duplicate_action' ] );
	},
]);