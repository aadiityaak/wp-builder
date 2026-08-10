<?php
/**
 * Frontend assets.
 *
 * @package WP_Builder
 */

namespace WPB\Render;

defined( 'ABSPATH' ) || exit;

use WPB\Document;

/**
 * Class Assets
 */
final class Assets {

	/**
	 * Enqueue frontend styles for builder pages/parts.
	 */
	public static function enqueue(): void {
		$should = false;

		if ( is_singular( 'page' ) && Document::is_active( (int) get_queried_object_id() ) ) {
			$should = true;
		}

		if ( isset( $_GET['wpb_preview'] ) && current_user_can( 'edit_pages' ) ) {
			$should = true;
		}

		if ( $should ) {
			wp_enqueue_style(
				'wp-builder',
				WPB_PLUGIN_URL . 'assets/css/wpb.css',
				array(),
				WPB_VERSION
			);
		}
	}
}
