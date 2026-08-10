<?php
/**
 * Pages/parts REST controller.
 *
 * @package WP_Builder
 */

namespace WPB\REST;

defined( 'ABSPATH' ) || exit;

use WPB\Abilities\Pages;
use WPB\Render\Renderer;
use WPB\Settings;

/**
 * Class Pages_Controller
 */
final class Pages_Controller {

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
			'/pages',
			array(
				'methods'             => \WP_REST_Server::READABLE,
				'callback'            => array( $this, 'pages' ),
				'permission_callback' => array( $this, 'permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/pages/(?P<id>\d+)/publish',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'publish' ),
				'permission_callback' => array( $this, 'permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/pages/(?P<id>\d+)',
			array(
				'methods'             => \WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete' ),
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
	 * GET /pages
	 *
	 * @return \WP_REST_Response
	 */
	public function pages(): \WP_REST_Response {
		$result = Pages::list_pages();

		return rest_ensure_response(
			array(
				'pages'               => is_wp_error( $result ) ? array() : $result,
				'header'              => array(
					'set'   => Renderer::part_exists( 'header' ),
					'url'   => Renderer::preview_url( 0, 'header' ),
					'label' => __( 'Header', 'wp-builder' ),
				),
				'footer'              => array(
					'set'   => Renderer::part_exists( 'footer' ),
					'url'   => Renderer::preview_url( 0, 'footer' ),
					'label' => __( 'Footer', 'wp-builder' ),
				),
				'provider_configured' => Settings::is_configured(),
			)
		);
	}

	/**
	 * POST /pages/{id}/publish
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function publish( \WP_REST_Request $request ) {
		$result = Pages::publish_page( array( 'page_id' => (int) $request['id'] ) );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => 400 ) );
		}

		return rest_ensure_response( $result );
	}

	/**
	 * DELETE /pages/{id}
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function delete( \WP_REST_Request $request ) {
		$result = Pages::delete_page( array( 'page_id' => (int) $request['id'] ) );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error( $result->get_error_code(), $result->get_error_message(), array( 'status' => 400 ) );
		}

		return rest_ensure_response( $result );
	}
}
