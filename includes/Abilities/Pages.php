<?php
/**
 * Page abilities.
 *
 * @package WP_Builder
 */

namespace WPB\Abilities;

defined( 'ABSPATH' ) || exit;

use WPB\Document;

/**
 * Class Pages
 */
final class Pages {

	/**
	 * wpb/list-pages
	 *
	 * @return array
	 */
	public static function tool_list_pages(): array {
		return array(
			'name'        => 'wpb/list-pages',
			'label'       => __( 'List builder pages', 'wp-builder' ),
			'description' => __( 'List all pages managed by the page builder (id, title, status, url, section count). Use this first to know what pages exist.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(),
			),
			'readonly'    => true,
			'execute_callback' => array( __CLASS__, 'list_pages' ),
		);
	}

	/**
	 * wpb/get-page
	 *
	 * @return array
	 */
	public static function tool_get_page(): array {
		return array(
			'name'        => 'wpb/get-page',
			'label'       => __( 'Get page document', 'wp-builder' ),
			'description' => __( 'Get the full section document of a builder page. Always call this before editing a page so you know the current section ids and props.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'page_id' => array(
						'type'        => 'integer',
						'description' => __( 'ID of the page.', 'wp-builder' ),
					),
				),
				'required'   => array( 'page_id' ),
			),
			'readonly'    => true,
			'execute_callback' => array( __CLASS__, 'get_page' ),
		);
	}

	/**
	 * wpb/create-page
	 *
	 * @return array
	 */
	public static function tool_create_page(): array {
		return array(
			'name'        => 'wpb/create-page',
			'label'       => __( 'Create page', 'wp-builder' ),
			'description' => __( 'Create a new page managed by the builder with an empty document. Then use wpb/add-section to fill it with sections.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'title' => array(
						'type'        => 'string',
						'description' => __( 'Title of the new page.', 'wp-builder' ),
					),
					'status' => array(
						'type'        => 'string',
						'enum'        => array( 'draft', 'publish' ),
						'description' => __( 'Initial status. Default draft.', 'wp-builder' ),
					),
					'parent_id' => array(
						'type'        => 'integer',
						'description' => __( 'Optional parent page ID.', 'wp-builder' ),
					),
				),
				'required'   => array( 'title' ),
			),
			'execute_callback' => array( __CLASS__, 'create_page' ),
		);
	}

	/**
	 * wpb/update-page
	 *
	 * @return array
	 */
	public static function tool_update_page(): array {
		return array(
			'name'        => 'wpb/update-page',
			'label'       => __( 'Update page', 'wp-builder' ),
			'description' => __( 'Update title and/or status of a builder page.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'page_id' => array(
						'type'        => 'integer',
						'description' => __( 'ID of the page.', 'wp-builder' ),
					),
					'title' => array(
						'type'        => 'string',
						'description' => __( 'New title.', 'wp-builder' ),
					),
					'status' => array(
						'type'        => 'string',
						'enum'        => array( 'draft', 'publish' ),
						'description' => __( 'New status.', 'wp-builder' ),
					),
				),
				'required'   => array( 'page_id' ),
			),
			'execute_callback' => array( __CLASS__, 'update_page' ),
		);
	}

	/**
	 * wpb/delete-page
	 *
	 * @return array
	 */
	public static function tool_delete_page(): array {
		return array(
			'name'        => 'wpb/delete-page',
			'label'       => __( 'Delete page', 'wp-builder' ),
			'description' => __( 'Move a builder page to the trash.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'page_id' => array(
						'type'        => 'integer',
						'description' => __( 'ID of the page.', 'wp-builder' ),
					),
				),
				'required'   => array( 'page_id' ),
			),
			'destructive' => true,
			'execute_callback' => array( __CLASS__, 'delete_page' ),
		);
	}

	/**
	 * wpb/publish-page
	 *
	 * @return array
	 */
	public static function tool_publish_page(): array {
		return array(
			'name'        => 'wpb/publish-page',
			'label'       => __( 'Publish page', 'wp-builder' ),
			'description' => __( 'Publish a builder page and return its public URL.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'page_id' => array(
						'type'        => 'integer',
						'description' => __( 'ID of the page.', 'wp-builder' ),
					),
				),
				'required'   => array( 'page_id' ),
			),
			'execute_callback' => array( __CLASS__, 'publish_page' ),
		);
	}

	/**
	 * List builder pages.
	 *
	 * @return array|\WP_Error
	 */
	public static function list_pages() {
		$posts = get_posts(
			array(
				'post_type'      => 'page',
				'post_status'    => array( 'draft', 'publish', 'pending' ),
				'posts_per_page' => 50,
				'orderby'        => 'modified',
				'order'          => 'DESC',
				'meta_key'       => Document::META_ACTIVE,
				'meta_value'     => 1,
			)
		);

		$items = array();
		foreach ( $posts as $post ) {
			$doc = Document::get( $post->ID );
			$items[] = array(
				'id'            => (int) $post->ID,
				'title'         => $post->post_title,
				'status'        => $post->post_status,
				'url'           => get_permalink( $post->ID ),
				'section_count' => count( $doc['sections'] ),
				'modified'      => get_the_modified_date( 'Y-m-d H:i', $post ),
			);
		}

		return $items;
	}

	/**
	 * Get a page document.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function get_page( array $input ) {
		$page_id = (int) ( $input['page_id'] ?? 0 );
		$post    = get_post( $page_id );

		if ( ! $post || 'page' !== $post->post_type ) {
			return new \WP_Error( 'wpb_page_not_found', __( 'Page not found.', 'wp-builder' ) );
		}
		if ( ! current_user_can( 'edit_post', $page_id ) ) {
			return new \WP_Error( 'wpb_forbidden', __( 'You cannot edit this page.', 'wp-builder' ) );
		}

		$doc = Document::get( $page_id );

		return array(
			'id'      => (int) $post->ID,
			'title'   => $post->post_title,
			'status'  => $post->post_status,
			'slug'    => $post->post_name,
			'url'     => get_permalink( $post->ID ),
			'settings' => $doc['settings'],
			'sections' => $doc['sections'],
		);
	}

	/**
	 * Create a page.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function create_page( array $input ) {
		$title   = sanitize_text_field( (string) ( $input['title'] ?? '' ) );
		$status  = isset( $input['status'] ) && 'publish' === $input['status'] ? 'publish' : 'draft';
		$parent  = ! empty( $input['parent_id'] ) ? (int) $input['parent_id'] : 0;

		if ( '' === $title ) {
			return new \WP_Error( 'wpb_empty_title', __( 'Title cannot be empty.', 'wp-builder' ) );
		}

		$page_id = wp_insert_post(
			array(
				'post_type'   => 'page',
				'post_title'  => $title,
				'post_status' => $status,
				'post_parent' => $parent,
			),
			true
		);

		if ( is_wp_error( $page_id ) ) {
			return $page_id;
		}

		Document::activate( $page_id );
		Document::set( $page_id, Document::empty_doc() );

		return array(
			'id'     => (int) $page_id,
			'title'  => $title,
			'status' => $status,
			'url'    => get_permalink( $page_id ),
		);
	}

	/**
	 * Update a page.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function update_page( array $input ) {
		$page_id = (int) ( $input['page_id'] ?? 0 );
		$post    = get_post( $page_id );

		if ( ! $post || 'page' !== $post->post_type ) {
			return new \WP_Error( 'wpb_page_not_found', __( 'Page not found.', 'wp-builder' ) );
		}
		if ( ! current_user_can( 'edit_post', $page_id ) ) {
			return new \WP_Error( 'wpb_forbidden', __( 'You cannot edit this page.', 'wp-builder' ) );
		}

		$update = array( 'ID' => $page_id );

		if ( isset( $input['title'] ) && '' !== trim( (string) $input['title'] ) ) {
			$update['post_title'] = sanitize_text_field( $input['title'] );
		}
		if ( isset( $input['status'] ) && in_array( $input['status'], array( 'draft', 'publish' ), true ) ) {
			$update['post_status'] = $input['status'];
		}

		$result = wp_update_post( $update, true );
		if ( is_wp_error( $result ) ) {
			return $result;
		}

		return array(
			'id'     => $page_id,
			'title'  => $update['post_title'] ?? $post->post_title,
			'status' => $update['post_status'] ?? $post->post_status,
		);
	}

	/**
	 * Trash a page.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function delete_page( array $input ) {
		$page_id = (int) ( $input['page_id'] ?? 0 );

		if ( ! current_user_can( 'delete_post', $page_id ) ) {
			return new \WP_Error( 'wpb_forbidden', __( 'You cannot delete this page.', 'wp-builder' ) );
		}

		$result = wp_trash_post( $page_id );
		if ( ! $result ) {
			return new \WP_Error( 'wpb_delete_failed', __( 'Could not delete the page.', 'wp-builder' ) );
		}

		return array(
			'id'     => $page_id,
			'status' => 'trashed',
		);
	}

	/**
	 * Publish a page.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function publish_page( array $input ) {
		$input['status'] = 'publish';
		$result          = self::update_page( $input );

		if ( is_wp_error( $result ) ) {
			return $result;
		}

		$result['url'] = get_permalink( (int) $input['page_id'] );

		return $result;
	}
}
