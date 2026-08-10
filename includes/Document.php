<?php
/**
 * Document model: builder JSON stored in post meta.
 *
 * @package WP_Builder
 */

namespace WPB;

defined( 'ABSPATH' ) || exit;

/**
 * Class Document
 */
final class Document {

	const META_DATA   = '_wpb_data';
	const META_ACTIVE = '_wpb_active';
	const META_PART   = '_wpb_part_type';

	/**
	 * Empty document.
	 *
	 * @return array
	 */
	public static function empty_doc(): array {
		return array(
			'version'  => 1,
			'settings' => array(
				'hide_header' => false,
				'hide_footer' => false,
			),
			'sections' => array(),
		);
	}

	/**
	 * Normalize/validate a raw document array.
	 *
	 * @param mixed $doc Raw document.
	 * @return array Normalized document.
	 */
	public static function normalize( $doc ): array {
		$normalized = self::empty_doc();

		if ( is_array( $doc ) ) {
			if ( isset( $doc['settings'] ) && is_array( $doc['settings'] ) ) {
				$normalized['settings'] = array_merge(
					$normalized['settings'],
					array(
						'hide_header' => ! empty( $doc['settings']['hide_header'] ),
						'hide_footer' => ! empty( $doc['settings']['hide_footer'] ),
					)
				);
			}
			if ( isset( $doc['sections'] ) && is_array( $doc['sections'] ) ) {
				foreach ( $doc['sections'] as $section ) {
					$s = self::normalize_section( $section );
					if ( $s ) {
						$normalized['sections'][] = $s;
					}
				}
			}
		}

		return $normalized;
	}

	/**
	 * Normalize a single section.
	 *
	 * @param mixed $section Raw section.
	 * @return array|null
	 */
	public static function normalize_section( $section ): ?array {
		if ( ! is_array( $section ) || empty( $section['type'] ) ) {
			return null;
		}

		$id   = isset( $section['id'] ) ? sanitize_text_field( (string) $section['id'] ) : '';
		$type = sanitize_key( $section['type'] );

		if ( '' === $type ) {
			return null;
		}
		if ( '' === $id ) {
			$id = self::new_id();
		}

		$props = isset( $section['props'] ) && is_array( $section['props'] ) ? $section['props'] : array();

		return array(
			'id'    => $id,
			'type'  => $type,
			'props' => $props,
		);
	}

	/**
	 * Generate a fresh section ID.
	 *
	 * @return string
	 */
	public static function new_id(): string {
		return 's_' . substr( (string) wp_generate_uuid4(), 0, 8 ) . substr( (string) time(), -4 );
	}

	/**
	 * Read document for a post.
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	public static function get( int $post_id ): array {
		$raw = get_post_meta( $post_id, self::META_DATA, true );
		if ( ! is_string( $raw ) || '' === $raw ) {
			return self::empty_doc();
		}

		$decoded = json_decode( $raw, true );

		return self::normalize( is_array( $decoded ) ? $decoded : null );
	}

	/**
	 * Save document for a post.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $doc     Document.
	 * @return bool
	 */
	public static function set( int $post_id, array $doc ): bool {
		$normalized = self::normalize( $doc );

		return (bool) update_post_meta( $post_id, self::META_DATA, wp_json_encode( $normalized, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) );
	}

	/**
	 * Mark a post as builder-managed.
	 *
	 * @param int $post_id Post ID.
	 */
	public static function activate( int $post_id ): void {
		update_post_meta( $post_id, self::META_ACTIVE, 1 );
	}

	/**
	 * Whether a post is builder-managed.
	 *
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function is_active( int $post_id ): bool {
		return (bool) get_post_meta( $post_id, self::META_ACTIVE, true );
	}
}
