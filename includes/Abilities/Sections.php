<?php
/**
 * Section abilities.
 *
 * @package WP_Builder
 */

namespace WPB\Abilities;

defined( 'ABSPATH' ) || exit;

use WPB\Document;
use WPB\Render\Sections as Section_Types;

/**
 * Class Sections
 */
final class Sections {

	/**
	 * Load + validate a builder page.
	 *
	 * @param int $page_id Page ID.
	 * @return \WP_Post|\WP_Error
	 */
	private static function load_page( int $page_id ) {
		$post = get_post( $page_id );

		if ( ! $post || 'page' !== $post->post_type ) {
			return new \WP_Error( 'wpb_page_not_found', __( 'Page not found.', 'wp-builder' ) );
		}
		if ( ! current_user_can( 'edit_post', $page_id ) ) {
			return new \WP_Error( 'wpb_forbidden', __( 'You cannot edit this page.', 'wp-builder' ) );
		}

		return $post;
	}

	/**
	 * wpb/get-sections
	 *
	 * @return array
	 */
	public static function tool_get_sections(): array {
		return array(
			'name'        => 'wpb/get-sections',
			'label'       => __( 'Get page sections', 'wp-builder' ),
			'description' => __( 'Get the ordered list of sections of a builder page, including each section id, type and props. Use section ids in subsequent edit calls.', 'wp-builder' ),
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
			'execute_callback' => array( __CLASS__, 'get_sections' ),
		);
	}

	/**
	 * wpb/add-section
	 *
	 * @return array
	 */
	public static function tool_add_section(): array {
		return array(
			'name'        => 'wpb/add-section',
			'label'       => __( 'Add section', 'wp-builder' ),
			'description' => __( 'Append a new section to a page, or insert it at a given index. `type` must be one of: ' . implode( ', ', Section_Types::types() ) . '. Provide all props the section needs.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'page_id' => array(
						'type'        => 'integer',
						'description' => __( 'ID of the page.', 'wp-builder' ),
					),
					'type' => array(
						'type'        => 'string',
						'description' => __( 'Section type.', 'wp-builder' ),
					),
					'props' => array(
						'type'        => 'object',
						'description' => __( 'Section props (type specific).', 'wp-builder' ),
					),
					'index' => array(
						'type'        => 'integer',
						'description' => __( 'Optional 0-based insertion index. Omit to append at the end.', 'wp-builder' ),
					),
				),
				'required'   => array( 'page_id', 'type', 'props' ),
			),
			'execute_callback' => array( __CLASS__, 'add_section' ),
		);
	}

	/**
	 * wpb/update-section
	 *
	 * @return array
	 */
	public static function tool_update_section(): array {
		return array(
			'name'        => 'wpb/update-section',
			'label'       => __( 'Update section', 'wp-builder' ),
			'description' => __( 'Replace the props of an existing section (identified by its section id). Provide the complete new props object.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'page_id' => array(
						'type'        => 'integer',
						'description' => __( 'ID of the page.', 'wp-builder' ),
					),
					'section_id' => array(
						'type'        => 'string',
						'description' => __( 'Section id (from wpb/get-sections).', 'wp-builder' ),
					),
					'props' => array(
						'type'        => 'object',
						'description' => __( 'New props for the section.', 'wp-builder' ),
					),
				),
				'required'   => array( 'page_id', 'section_id', 'props' ),
			),
			'execute_callback' => array( __CLASS__, 'update_section' ),
		);
	}

	/**
	 * wpb/delete-section
	 *
	 * @return array
	 */
	public static function tool_delete_section(): array {
		return array(
			'name'        => 'wpb/delete-section',
			'label'       => __( 'Delete section', 'wp-builder' ),
			'description' => __( 'Remove a section from a page by its section id.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'page_id' => array(
						'type'        => 'integer',
						'description' => __( 'ID of the page.', 'wp-builder' ),
					),
					'section_id' => array(
						'type'        => 'string',
						'description' => __( 'Section id to remove.', 'wp-builder' ),
					),
				),
				'required'   => array( 'page_id', 'section_id' ),
			),
			'destructive' => true,
			'execute_callback' => array( __CLASS__, 'delete_section' ),
		);
	}

	/**
	 * wpb/move-section
	 *
	 * @return array
	 */
	public static function tool_move_section(): array {
		return array(
			'name'        => 'wpb/move-section',
			'label'       => __( 'Move section', 'wp-builder' ),
			'description' => __( 'Move a section to a new 0-based index within the page.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'page_id' => array(
						'type'        => 'integer',
						'description' => __( 'ID of the page.', 'wp-builder' ),
					),
					'section_id' => array(
						'type'        => 'string',
						'description' => __( 'Section id to move.', 'wp-builder' ),
					),
					'index' => array(
						'type'        => 'integer',
						'description' => __( 'New 0-based index.', 'wp-builder' ),
					),
				),
				'required'   => array( 'page_id', 'section_id', 'index' ),
			),
			'execute_callback' => array( __CLASS__, 'move_section' ),
		);
	}

	/**
	 * Get sections of a page.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function get_sections( array $input ) {
		$page = self::load_page( (int) ( $input['page_id'] ?? 0 ) );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		return array(
			'page_id'  => $page->ID,
			'title'    => $page->post_title,
			'sections' => Document::get( $page->ID )['sections'],
		);
	}

	/**
	 * Add a section.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function add_section( array $input ) {
		$page = self::load_page( (int) ( $input['page_id'] ?? 0 ) );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		$type = sanitize_key( (string) ( $input['type'] ?? '' ) );
		if ( '' === $type || ! in_array( $type, Section_Types::types(), true ) ) {
			return new \WP_Error(
				'wpb_bad_section_type',
				sprintf(
					/* translators: %s: allowed types. */
					__( 'Unknown section type "%1$s". Allowed: %2$s.', 'wp-builder' ),
					$type,
					implode( ', ', Section_Types::types() )
				)
			);
		}

		$doc     = Document::get( $page->ID );
		$section = Document::normalize_section(
			array(
				'type'  => $type,
				'props' => isset( $input['props'] ) && is_array( $input['props'] ) ? $input['props'] : array(),
			)
		);

		if ( isset( $input['index'] ) ) {
			$index = max( 0, min( count( $doc['sections'] ), (int) $input['index'] ) );
			array_splice( $doc['sections'], $index, 0, array( $section ) );
		} else {
			$doc['sections'][] = $section;
			$index             = count( $doc['sections'] ) - 1;
		}

		Document::set( $page->ID, $doc );

		return array(
			'page_id'      => $page->ID,
			'id'           => $section['id'],
			'type'         => $type,
			'index'        => $index,
			'section_count' => count( $doc['sections'] ),
		);
	}

	/**
	 * Update a section.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function update_section( array $input ) {
		$page = self::load_page( (int) ( $input['page_id'] ?? 0 ) );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		$section_id = sanitize_text_field( (string) ( $input['section_id'] ?? '' ) );
		$doc        = Document::get( $page->ID );

		foreach ( $doc['sections'] as &$section ) {
			if ( $section['id'] === $section_id ) {
				$section['props'] = isset( $input['props'] ) && is_array( $input['props'] ) ? $input['props'] : array();
				Document::set( $page->ID, $doc );
				return array(
					'page_id'      => $page->ID,
					'id'           => $section_id,
					'section_count' => count( $doc['sections'] ),
				);
			}
		}

		return new \WP_Error( 'wpb_section_not_found', __( 'Section not found on this page.', 'wp-builder' ) );
	}

	/**
	 * Delete a section.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function delete_section( array $input ) {
		$page = self::load_page( (int) ( $input['page_id'] ?? 0 ) );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		$section_id = sanitize_text_field( (string) ( $input['section_id'] ?? '' ) );
		$doc        = Document::get( $page->ID );

		foreach ( $doc['sections'] as $i => $section ) {
			if ( $section['id'] === $section_id ) {
				array_splice( $doc['sections'], $i, 1 );
				Document::set( $page->ID, $doc );
				return array(
					'page_id'      => $page->ID,
					'removed'      => $section_id,
					'section_count' => count( $doc['sections'] ),
				);
			}
		}

		return new \WP_Error( 'wpb_section_not_found', __( 'Section not found on this page.', 'wp-builder' ) );
	}

	/**
	 * Move a section.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function move_section( array $input ) {
		$page = self::load_page( (int) ( $input['page_id'] ?? 0 ) );
		if ( is_wp_error( $page ) ) {
			return $page;
		}

		$section_id = sanitize_text_field( (string) ( $input['section_id'] ?? '' ) );
		$doc        = Document::get( $page->ID );

		$current = null;
		foreach ( $doc['sections'] as $i => $section ) {
			if ( $section['id'] === $section_id ) {
				$current = $i;
				break;
			}
		}

		if ( null === $current ) {
			return new \WP_Error( 'wpb_section_not_found', __( 'Section not found on this page.', 'wp-builder' ) );
		}

		$section = $doc['sections'][ $current ];
		array_splice( $doc['sections'], $current, 1 );
		$index = max( 0, min( count( $doc['sections'] ), (int) $input['index'] ) );
		array_splice( $doc['sections'], $index, 0, array( $section ) );

		Document::set( $page->ID, $doc );

		return array(
			'page_id'      => $page->ID,
			'id'           => $section_id,
			'index'        => $index,
			'section_count' => count( $doc['sections'] ),
		);
	}
}
