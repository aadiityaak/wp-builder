<?php
/**
 * Provider settings REST controller.
 *
 * @package WP_Builder
 */

namespace WPB\REST;

defined( 'ABSPATH' ) || exit;

use WPB\Settings;

/**
 * Class Settings_Controller
 */
final class Settings_Controller {

	/**
	 * Route namespace.
	 *
	 * @var string
	 */
	private $namespace = 'wp-builder/v1';

	/**
	 * Register routes.
	 */
	public function register(): void {
		register_rest_route(
			$this->namespace,
			'/settings',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_settings' ),
					'permission_callback' => array( $this, 'permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::CREATABLE,
					'callback'            => array( $this, 'save_settings' ),
					'permission_callback' => array( $this, 'permission' ),
				),
			)
		);

		register_rest_route(
			$this->namespace,
			'/settings/test',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'test' ),
				'permission_callback' => array( $this, 'permission' ),
			)
		);
	}

	/**
	 * Permission callback.
	 *
	 * @return bool
	 */
	public function permission(): bool {
		return current_user_can( 'manage_options' );
	}

	/**
	 * GET /settings (masked API key).
	 *
	 * @return \WP_REST_Response
	 */
	public function get_settings(): \WP_REST_Response {
		$s    = Settings::get();
		$mask = '';

		if ( '' !== $s['api_key'] ) {
			$mask = substr( $s['api_key'], 0, 4 ) . str_repeat( '•', 12 ) . substr( $s['api_key'], -4 );
		}

		return rest_ensure_response(
			array(
				'base_url'    => $s['base_url'],
				'model'       => $s['model'],
				'temperature' => $s['temperature'],
				'api_key_set' => '' !== $s['api_key'],
				'api_key_masked' => $mask,
			)
		);
	}

	/**
	 * POST /settings
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function save_settings( \WP_REST_Request $request ): \WP_REST_Response {
		$saved = Settings::save( (array) $request->get_params() );

		return rest_ensure_response(
			array(
				'saved'        => true,
				'base_url'     => $saved['base_url'],
				'model'        => $saved['model'],
				'temperature'  => $saved['temperature'],
				'api_key_set'  => '' !== $saved['api_key'],
			)
		);
	}

	/**
	 * POST /settings/test
	 *
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function test(): \WP_REST_Response {
		$result = Settings::test_connection();

		if ( is_wp_error( $result ) ) {
			return new \WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => 400 ) );
		}

		return rest_ensure_response( array( 'ok' => true ) );
	}
}
