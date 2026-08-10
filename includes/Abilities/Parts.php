<?php
/**
 * Header/footer part abilities.
 *
 * @package WP_Builder
 */

namespace WPB\Abilities;

defined( 'ABSPATH' ) || exit;

use WPB\Document;
use WPB\Settings;

/**
 * Class Parts
 */
final class Parts {

	/**
	 * Validate a part key.
	 *
	 * @param string $part Part key.
	 * @return string|\WP_Error
	 */
	private static function validate_part( string $part ) {
		if ( ! in_array( $part, array( 'header', 'footer' ), true ) ) {
			return new \WP_Error( 'wpb_bad_part', __( 'Part must be "header" or "footer".', 'wp-builder' ) );
		}
		return $part;
	}

	/**
	 * wpb/get-part
	 *
	 * @return array
	 */
	public static function tool_get_part(): array {
		return array(
			'name'        => 'wpb/get-part',
			'label'       => __( 'Get header/footer part', 'wp-builder' ),
			'description' => __( 'Get the current section document of the site header or footer part. Call this before editing a part.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'part' => array(
						'type'        => 'string',
						'enum'        => array( 'header', 'footer' ),
						'description' => __( 'Which part to read.', 'wp-builder' ),
					),
				),
				'required'   => array( 'part' ),
			),
			'readonly'    => true,
			'execute_callback' => array( __CLASS__, 'get_part' ),
		);
	}

	/**
	 * wpb/update-part
	 *
	 * @return array
	 */
	public static function tool_update_part(): array {
		return array(
			'name'        => 'wpb/update-part',
			'label'       => __( 'Update header/footer part', 'wp-builder' ),
			'description' => __( 'Replace the entire section list of the site header or footer part. Provide the complete new sections array.', 'wp-builder' ),
			'input_schema' => array(
				'type'       => 'object',
				'properties' => array(
					'part' => array(
						'type'        => 'string',
						'enum'        => array( 'header', 'footer' ),
						'description' => __( 'Which part to update.', 'wp-builder' ),
					),
					'sections' => array(
						'type'        => 'array',
						'description' => __( 'Complete new sections array.', 'wp-builder' ),
					),
				),
				'required'   => array( 'part', 'sections' ),
			),
			'execute_callback' => array( __CLASS__, 'update_part' ),
		);
	}

	/**
	 * Get a part document.
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function get_part( array $input ) {
		$part = self::validate_part( (string) ( $input['part'] ?? '' ) );
		if ( is_wp_error( $part ) ) {
			return $part;
		}

		$part_id = Settings::part_id( $part );
		$post    = $part_id ? get_post( $part_id ) : null;

		if ( ! $post || 'wpb_part' !== $post->post_type ) {
			return array(
				'part'     => $part,
				'set'      => false,
				'settings' => Document::empty_doc()['settings'],
				'sections' => array(),
			);
		}

		$doc = Document::get( $part_id );

		return array(
			'part'     => $part,
			'set'      => true,
			'part_id'  => (int) $part_id,
			'settings' => $doc['settings'],
			'sections' => $doc['sections'],
		);
	}

	/**
	 * Update a part document (replace sections).
	 *
	 * @param array $input Input.
	 * @return array|\WP_Error
	 */
	public static function update_part( array $input ) {
		$part = self::validate_part( (string) ( $input['part'] ?? '' ) );
		if ( is_wp_error( $part ) ) {
			return $part;
		}

		if ( ! current_user_can( 'edit_pages' ) ) {
			return new \WP_Error( 'wpb_forbidden', __( 'You cannot edit parts.', 'wp-builder' ) );
		}

		$part_id = Settings::part_id( $part );
		$post    = $part_id ? get_post( $part_id ) : null;

		if ( ! $post || 'wpb_part' !== $post->post_type ) {
			$created = wp_insert_post(
				array(
					'post_type'   => 'wpb_part',
					'post_status' => 'publish',
					'post_title'  => ucfirst( $part ),
				),
				true
			);
			if ( is_wp_error( $created ) ) {
				return $created;
			}
			$part_id = $created;
			Settings::set_part_id( $part, $part_id );
			Document::activate( $part_id );
		}

		$doc          = Document::get( $part_id );
		$doc['sections'] = Document::normalize( array( 'sections' => $input['sections'] ?? array() ) )['sections'];
		Document::set( $part_id, $doc );

		return array(
			'part'         => $part,
			'part_id'      => (int) $part_id,
			'section_count' => count( $doc['sections'] ),
		);
	}
}
