<?php
/**
 * Template routing for builder pages.
 *
 * @package WP_Builder
 */

namespace WPB\Render;

defined( 'ABSPATH' ) || exit;

use WPB\Document;

/**
 * Class Template
 */
final class Template {

	/**
	 * Route builder pages and part previews to our templates.
	 *
	 * @param string $template Current template.
	 * @return string
	 */
	public static function template_include( string $template ): string {
		if ( is_admin() || is_embed() ) {
			return $template;
		}

		// Isolated part preview (?wpb_preview=1&wpb_part=header).
		$part = isset( $_GET['wpb_part'] ) ? sanitize_key( wp_unslash( $_GET['wpb_part'] ) ) : '';
		if ( in_array( $part, array( 'header', 'footer' ), true ) && current_user_can( 'edit_pages' ) ) {
			return WPB_PLUGIN_DIR . 'templates/part-preview.php';
		}

		if ( ! is_singular( 'page' ) ) {
			return $template;
		}

		$post_id = get_queried_object_id();
		if ( ! $post_id || ! Document::is_active( $post_id ) ) {
			return $template;
		}

		$post = get_post( $post_id );

		$is_preview = isset( $_GET['wpb_preview'] ) && current_user_can( 'edit_pages' );

		if ( 'publish' === $post->post_status || $is_preview ) {
			return WPB_PLUGIN_DIR . 'templates/builder-page.php';
		}

		return $template;
	}
}
