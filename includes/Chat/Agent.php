<?php
/**
 * Agentic chat loop: messages + tools + tool_calls.
 *
 * @package WP_Builder
 */

namespace WPB\Chat;

defined( 'ABSPATH' ) || exit;

use WPB\Render\Renderer;

/**
 * Class Agent
 */
final class Agent {

	const MAX_TURNS = 8;

	/**
	 * Ability names this agent may call.
	 *
	 * @return array
	 */
	private static function ability_names(): array {
		return array(
			'wpb/list-pages',
			'wpb/get-page',
			'wpb/create-page',
			'wpb/update-page',
			'wpb/delete-page',
			'wpb/publish-page',
			'wpb/get-sections',
			'wpb/add-section',
			'wpb/update-section',
			'wpb/delete-section',
			'wpb/move-section',
			'wpb/get-part',
			'wpb/update-part',
		);
	}

	/**
	 * Run one chat turn.
	 *
	 * @param string $session_id Session id.
	 * @param string $user_message User message.
	 * @return array{reply:string,events:array,preview:array}|\WP_Error
	 */
	public static function run( string $session_id, string $user_message ) {
		$data     = Session::get( $session_id );
		$messages = $data['messages'];
		$tools    = array();

		$abilities = array();
		foreach ( self::ability_names() as $name ) {
			$ability = wp_get_ability( $name );
			if ( $ability ) {
				$abilities[ $name ] = $ability;
				$tools[]            = Provider::ability_to_tool( $ability );
			}
		}

		$messages[] = array( 'role' => 'user', 'content' => $user_message );

		$events   = array();
		$last     = null;
		$response = null;

		for ( $turn = 0; $turn < self::MAX_TURNS; $turn++ ) {
			$composed = array_merge( array( array( 'role' => 'system', 'content' => Prompt::system() ) ), $messages );

			$response = Provider::chat( $composed, $tools );

			if ( is_wp_error( $response ) ) {
				return $response;
			}

			$message = $response['choices'][0]['message'] ?? null;
			if ( ! is_array( $message ) ) {
				return new \WP_Error( 'wpb_empty_response', __( 'The provider returned an empty response.', 'wp-builder' ) );
			}

			$tool_calls = $message['tool_calls'] ?? array();

			if ( empty( $tool_calls ) ) {
				// Final assistant text.
				$text         = isset( $message['content'] ) ? (string) $message['content'] : '';
				$messages[]   = array( 'role' => 'assistant', 'content' => $text );
				$last         = $text;
				break;
			}

			$messages[] = array(
				'role'       => 'assistant',
				'content'    => $message['content'] ?? '',
				'tool_calls' => array_map(
					static function ( $call ) {
						return array(
							'id'       => $call['id'],
							'type'     => 'function',
							'function' => array(
								'name'      => $call['function']['name'],
								'arguments' => $call['function']['arguments'],
							),
						);
					},
					$tool_calls
				),
			);

			foreach ( $tool_calls as $call ) {
				$name      = (string) ( $call['function']['name'] ?? '' );
				$ability   = Provider::tool_name_to_ability( $name );
				$args_raw  = (string) ( $call['function']['arguments'] ?? '{}' );
				$args      = json_decode( $args_raw, true );
				$args      = is_array( $args ) ? $args : array();
				$tool_id   = (string) ( $call['id'] ?? '' );

				$result = self::execute_ability( $ability, $args, $abilities );

				if ( is_wp_error( $result ) ) {
					$payload = array(
						'ok'    => false,
						'error' => $result->get_error_message(),
						'code'  => $result->get_error_code(),
					);
				} else {
					$payload = array( 'ok' => true, 'data' => $result );
					$last    = $result;
				}

				$events[] = array(
					'tool'   => $ability,
					'args'   => $args,
					'result' => $result,
				);

				$messages[] = array(
					'role'         => 'tool',
					'tool_call_id' => $tool_id,
					'content'      => wp_json_encode( $payload, JSON_UNESCAPED_UNICODE ),
				);
			}
		}

		if ( null === $last ) {
			return new \WP_Error( 'wpb_max_turns', __( 'The agent reached the maximum number of tool steps.', 'wp-builder' ) );
		}

		$preview = self::resolve_preview( $events );

		Session::save(
			$session_id,
			array(
				'messages' => $messages,
				'preview'  => $preview,
			)
		);

		return array(
			'reply'   => is_string( $last ) ? $last : (string) $last,
			'events'  => $events,
			'preview' => $preview,
		);
	}

	/**
	 * Execute an ability by name (validate + permission check handled by WP_Ability).
	 *
	 * @param string $name Ability name.
	 * @param array  $args Args.
	 * @param array  $abilities Loaded abilities.
	 * @return mixed|\WP_Error
	 */
	private static function execute_ability( string $name, array $args, array $abilities ) {
		$ability = $abilities[ $name ] ?? null;

		if ( ! $ability ) {
			return new \WP_Error( 'wpb_unknown_ability', __( 'Unknown tool.', 'wp-builder' ) );
		}

		return $ability->execute( $args );
	}

	/**
	 * Resolve the preview target from the last relevant write event.
	 *
	 * @param array $events Tool events.
	 * @return array{url:string,label:string}
	 */
	private static function resolve_preview( array $events ): array {
		$page_events = array(
			'wpb/create-page'       => true,
			'wpb/update-page'       => true,
			'wpb/add-section'       => true,
			'wpb/update-section'    => true,
			'wpb/delete-section'    => true,
			'wpb/move-section'      => true,
			'wpb/publish-page'      => true,
		);
		$part_events = array(
			'wpb/update-part' => true,
		);

		for ( $i = count( $events ) - 1; $i >= 0; $i-- ) {
			$event = $events[ $i ];
			$tool  = $event['tool'];

			if ( isset( $part_events[ $tool ] ) && is_array( $event['result'] ) && isset( $event['result']['part'] ) ) {
				$part = $event['result']['part'];
				return array(
					'url'   => Renderer::preview_url( 0, $part ),
					'label' => 'header' === $part ? __( 'Header', 'wp-builder' ) : __( 'Footer', 'wp-builder' ),
				);
			}

			if ( isset( $page_events[ $tool ] ) && is_array( $event['args'] ) && ! empty( $event['args']['page_id'] ) ) {
				$page_id = (int) $event['args']['page_id'];
				$result  = $event['result'];
				$url     = Renderer::preview_url( $page_id );

				if ( 'wpb/publish-page' === $tool && is_array( $result ) && ! empty( $result['url'] ) ) {
					$url = $result['url'];
				}

				return array(
					'url'   => $url,
					'label' => get_the_title( $page_id ) ?: sprintf( 'ID %d', $page_id ),
				);
			}
		}

		return array( 'url' => '', 'label' => '' );
	}
}
