<?php
/**
 * OpenAI-compatible chat completions client.
 *
 * @package WP_Builder
 */

namespace WPB\Chat;

defined( 'ABSPATH' ) || exit;

use WPB\Settings;

/**
 * Class Provider
 */
final class Provider {

	const TOOL_PREFIX = 'wpab__';

	/**
	 * Send a chat/completions request.
	 *
	 * @param array $messages Messages (OpenAI format).
	 * @param array $tools    Tool declarations.
	 * @return array|\WP_Error Decoded JSON body.
	 */
	public static function chat( array $messages, array $tools ) {
		$s = Settings::get();

		if ( empty( $s['api_key'] ) ) {
			return new \WP_Error(
				'wpb_no_provider',
				__( 'AI provider is not configured. Open Builder → Settings and fill in your API key.', 'wp-builder' )
			);
		}

		$body = array(
			'model'       => $s['model'],
			'messages'    => $messages,
			'temperature' => (float) $s['temperature'],
		);

		if ( ! empty( $tools ) ) {
			$body['tools']       = $tools;
			$body['tool_choice'] = 'auto';
		}

		$response = wp_remote_post(
			trailingslashit( $s['base_url'] ) . 'chat/completions',
			array(
				'timeout' => 120,
				'headers' => array(
					'Content-Type'  => 'application/json',
					'Authorization' => 'Bearer ' . $s['api_key'],
				),
				'body'    => wp_json_encode( $body, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$code = (int) wp_remote_retrieve_response_code( $response );
		$data = json_decode( (string) wp_remote_retrieve_body( $response ), true );

		if ( $code >= 200 && $code < 300 ) {
			return is_array( $data ) ? $data : array();
		}

		$message = __( 'Provider returned an error.', 'wp-builder' );
		if ( is_array( $data ) && isset( $data['error']['message'] ) ) {
			$message = (string) $data['error']['message'];
		}

		return new \WP_Error( 'wpb_provider_error', $message, array( 'status' => $code ) );
	}

	/**
	 * Convert a WP_Ability to an OpenAI tool declaration.
	 *
	 * @param \WP_Ability $ability Ability object.
	 * @return array
	 */
	public static function ability_to_tool( \WP_Ability $ability ): array {
		$schema = self::normalize_schema( $ability->get_input_schema() );
		return array(
			'type'     => 'function',
			'function' => array(
				'name'        => self::TOOL_PREFIX . str_replace( '/', '_', $ability->get_name() ),
				'description' => (string) $ability->get_description(),
				'parameters'  => $schema,
			),
		);
	}

	/**
	 * Normalize input schema for OpenAI compatibility.
	 *
	 * OpenAI/API consumers reject `properties: []` (must be object `{}`)
	 * and require a top-level `additionalProperties` key for strict schemas.
	 *
	 * @param array $schema Raw input schema from the ability.
	 * @return array Normalized schema object.
	 */
	private static function normalize_schema( array $schema ): array {
		if ( ! isset( $schema['type'] ) ) {
			$schema['type'] = 'object';
		}
		// PHP [] serializes to JSON `[]`; force empty properties to {}.
		if ( isset( $schema['properties'] ) && ! is_array( $schema['properties'] ) ) {
			$schema['properties'] = array();
		}
		$schema['properties']      = (object) ( array_merge( (array) $schema['properties'], array() ) );
		$schema['required']        = array_values( (array) ( $schema['required'] ?? array() ) );
		$schema['additionalProperties'] = false;
		return $schema;
	}

	/**
	 * Map an OpenAI tool name back to an ability name.
	 *
	 * @param string $tool_name OpenAI tool name.
	 * @return string
	 */
	public static function tool_name_to_ability( string $tool_name ): string {
		$stripped = preg_replace( '/^' . preg_quote( self::TOOL_PREFIX, '/' ) . '/', '', $tool_name );
		return (string) str_replace( '_', '/', $stripped );
	}
}
