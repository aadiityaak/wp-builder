<?php
/**
 * Chat REST controller.
 *
 * @package WP_Builder
 */

namespace WPB\REST;

defined( 'ABSPATH' ) || exit;

use WPB\Chat\Agent;
use WPB\Chat\Session;

/**
 * Class Chat_Controller
 */
final class Chat_Controller {

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
			'/chat',
			array(
				'methods'             => \WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'chat' ),
				'permission_callback' => array( $this, 'permission' ),
			)
		);

		register_rest_route(
			$this->namespace,
			'/chat/session/(?P<id>[a-zA-Z0-9_\-]{1,64})',
			array(
				array(
					'methods'             => \WP_REST_Server::READABLE,
					'callback'            => array( $this, 'get_session' ),
					'permission_callback' => array( $this, 'permission' ),
				),
				array(
					'methods'             => \WP_REST_Server::DELETABLE,
					'callback'            => array( $this, 'delete_session' ),
					'permission_callback' => array( $this, 'permission' ),
				),
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
	 * POST /chat
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response|\WP_Error
	 */
	public function chat( \WP_REST_Request $request ) {
		$session_id = sanitize_text_field( (string) $request->get_param( 'session_id' ) );
		$message    = trim( (string) $request->get_param( 'message' ) );

		if ( '' === $message ) {
			return new \WP_Error( 'wpb_empty_message', __( 'Message cannot be empty.', 'wp-builder' ), array( 'status' => 400 ) );
		}

		$session_id = Session::sanitize_id( $session_id );
		if ( '' === $session_id ) {
			$session_id = 'sess_' . substr( (string) wp_generate_uuid4(), 0, 12 );
		}

		$result = Agent::run( $session_id, $message );

		if ( is_wp_error( $result ) ) {
			return new \WP_Error(
				$result->get_error_code(),
				$result->get_error_message(),
				array( 'status' => 502 )
			);
		}

		return rest_ensure_response(
			array(
				'session_id' => $session_id,
				'reply'      => $result['reply'],
				'events'     => array_map(
					static function ( $event ) {
						$item = array(
							'tool' => $event['tool'],
							'ok'   => ! is_wp_error( $event['result'] ),
						);
						if ( is_wp_error( $event['result'] ) ) {
							$item['error'] = $event['result']->get_error_message();
						}
						return $item;
					},
					$result['events']
				),
				'preview'    => $result['preview'],
			)
		);
	}

	/**
	 * GET /chat/session/{id}
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function get_session( \WP_REST_Request $request ) {
		$data = Session::get( (string) $request['id'] );

		$transcript = array();
		foreach ( $data['messages'] as $message ) {
			if ( in_array( $message['role'] ?? '', array( 'user', 'assistant' ), true ) ) {
				$transcript[] = array(
					'role'    => $message['role'],
					'content' => $message['content'] ?? '',
				);
			}
		}

		return rest_ensure_response(
			array(
				'id'        => (string) $request['id'],
				'messages'  => $transcript,
				'preview'   => $data['preview'],
			)
		);
	}

	/**
	 * DELETE /chat/session/{id}
	 *
	 * @param \WP_REST_Request $request Request.
	 * @return \WP_REST_Response
	 */
	public function delete_session( \WP_REST_Request $request ) {
		Session::delete( (string) $request['id'] );

		return rest_ensure_response( array( 'deleted' => true ) );
	}
}
