<?php
/**
 * Provider settings (OpenAI-compatible).
 *
 * @package WP_Builder
 */

namespace WPB;

defined( 'ABSPATH' ) || exit;

/**
 * Class Settings
 */
final class Settings {

	const OPTION = 'wp_builder_provider';

	/**
	 * Default settings.
	 *
	 * @return array
	 */
	public static function defaults(): array {
		return array(
			'base_url'    => 'https://api.openai.com/v1',
			'api_key'     => '',
			'model'       => 'gpt-4o-mini',
			'temperature' => 0.3,
		);
	}

	/**
	 * Get settings.
	 *
	 * @return array
	 */
	public static function get(): array {
		$stored = get_option( self::OPTION, array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return wp_parse_args( $stored, self::defaults() );
	}

	/**
	 * Sanitize + save settings from raw input.
	 *
	 * @param array $input Raw input.
	 * @return array Saved (sanitized) settings.
	 */
	public static function save( array $input ): array {
		$current = self::get();

		$next = array(
			'base_url'    => isset( $input['base_url'] ) ? untrailingslashit( esc_url_raw( $input['base_url'] ) ) : $current['base_url'],
			'api_key'     => isset( $input['api_key'] ) && '' !== trim( (string) $input['api_key'] ) ? sanitize_text_field( $input['api_key'] ) : $current['api_key'],
			'model'       => isset( $input['model'] ) ? sanitize_text_field( $input['model'] ) : $current['model'],
			'temperature' => isset( $input['temperature'] ) ? max( 0, min( 2, (float) $input['temperature'] ) ) : $current['temperature'],
		);

		if ( empty( $next['base_url'] ) ) {
			$next['base_url'] = $current['base_url'];
		}
		if ( empty( $next['model'] ) ) {
			$next['model'] = $current['model'];
		}

		update_option( self::OPTION, $next );

		return $next;
	}

	/**
	 * Whether a provider is configured.
	 *
	 * @return bool
	 */
	public static function is_configured(): bool {
		$s = self::get();
		return ! empty( $s['api_key'] ) && ! empty( $s['base_url'] ) && ! empty( $s['model'] );
	}

	/**
	 * Test connectivity against the provider /models endpoint.
	 *
	 * @return true|\WP_Error
	 */
	public static function test_connection() {
		$s = self::get();

		if ( empty( $s['api_key'] ) ) {
			return new \WP_Error( 'wpb_no_key', __( 'API key is empty.', 'wp-builder' ) );
		}

		$response = wp_remote_get(
			trailingslashit( $s['base_url'] ) . 'models',
			array(
				'timeout' => 20,
				'headers' => array( 'Authorization' => 'Bearer ' . $s['api_key'] ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		if ( $code >= 200 && $code < 300 ) {
			return true;
		}

		return new \WP_Error(
			'wpb_connection_failed',
			sprintf(
				/* translators: %d: HTTP status code. */
				__( 'Provider returned HTTP %d.', 'wp-builder' ),
				$code
			)
		);
	}

	/**
	 * Active part post IDs (header/footer).
	 *
	 * @return array{header:int,footer:int}
	 */
	public static function parts(): array {
		$stored = get_option( 'wp_builder_parts', array() );
		if ( ! is_array( $stored ) ) {
			$stored = array();
		}

		return array(
			'header' => ! empty( $stored['header'] ) ? (int) $stored['header'] : 0,
			'footer' => ! empty( $stored['footer'] ) ? (int) $stored['footer'] : 0,
		);
	}

	/**
	 * Get active part post ID.
	 *
	 * @param string $part 'header'|'footer'.
	 * @return int
	 */
	public static function part_id( string $part ): int {
		$parts = self::parts();
		return $parts[ $part ] ?? 0;
	}

	/**
	 * Set active part post ID.
	 *
	 * @param string $part 'header'|'footer'.
	 * @param int    $post_id Part post ID.
	 */
	public static function set_part_id( string $part, int $post_id ): void {
		$parts            = self::parts();
		$parts[ $part ]   = $post_id;
		update_option( 'wp_builder_parts', $parts );
	}
}
