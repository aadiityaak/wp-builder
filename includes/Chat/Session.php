<?php
/**
 * Chat session persistence (ephemeral, transient-based).
 *
 * @package WP_Builder
 */

namespace WPB\Chat;

defined( 'ABSPATH' ) || exit;

/**
 * Class Session
 */
final class Session {

	const TTL = DAY_IN_SECONDS;

	/**
	 * Sanitize a session id.
	 *
	 * @param string $id Raw id.
	 * @return string
	 */
	public static function sanitize_id( string $id ): string {
		$id = preg_replace( '/[^a-zA-Z0-9_\-]/', '', $id );
		return '' !== $id ? substr( $id, 0, 64 ) : '';
	}

	/**
	 * Transient key.
	 *
	 * @param string $id Session id.
	 * @return string
	 */
	private static function key( string $id ): string {
		return 'wpb_chat_' . $id;
	}

	/**
	 * Get session data.
	 *
	 * @param string $id Session id.
	 * @return array
	 */
	public static function get( string $id ): array {
		$data = get_transient( self::key( $id ) );
		if ( ! is_array( $data ) ) {
			$data = array();
		}

		return array(
			'messages' => isset( $data['messages'] ) && is_array( $data['messages'] ) ? $data['messages'] : array(),
			'preview'  => isset( $data['preview'] ) && is_array( $data['preview'] ) ? $data['preview'] : array( 'url' => '', 'label' => '' ),
		);
	}

	/**
	 * Save session data.
	 *
	 * @param string $id   Session id.
	 * @param array  $data Data.
	 */
	public static function save( string $id, array $data ): void {
		$capped = array_slice( (array) $data['messages'], -60 ); // keep the last 60 messages.
		set_transient( self::key( $id ), array( 'messages' => $capped, 'preview' => $data['preview'] ?? array() ), self::TTL );
	}

	/**
	 * Delete a session.
	 *
	 * @param string $id Session id.
	 */
	public static function delete( string $id ): void {
		delete_transient( self::key( $id ) );
	}
}
