<?php
/**
 * Document renderer: turns builder JSON into HTML.
 *
 * @package WP_Builder
 */

namespace WPB\Render;

defined( 'ABSPATH' ) || exit;

use WPB\Document;
use WPB\Settings;

/**
 * Class Renderer
 */
final class Renderer {

	/**
	 * Render a full document body.
	 *
	 * @param array $doc Document.
	 * @param array $ctx Context.
	 * @return string
	 */
	public static function render_doc( array $doc, array $ctx = array() ): string {
		return '<main class="wpb-content">' . Sections::render_part( $doc, $ctx ) . '</main>';
	}

	/**
	 * Whether a part exists and is active.
	 *
	 * @param string $part 'header'|'footer'.
	 * @return bool
	 */
	public static function part_exists( string $part ): bool {
		$id   = Settings::part_id( $part );
		$post = $id ? get_post( $id ) : null;
		return (bool) $post && 'wpb_part' === $post->post_type;
	}

	/**
	 * Render a part to HTML (empty string when not set).
	 *
	 * @param string $part 'header'|'footer'.
	 * @param array  $ctx  Context.
	 * @return string
	 */
	public static function render_part_html( string $part, array $ctx = array() ): string {
		$id   = Settings::part_id( $part );
		$post = $id ? get_post( $id ) : null;

		if ( ! $post || 'wpb_part' !== $post->post_type ) {
			return '';
		}

		$doc = Document::get( $post->ID );

		return '<' . $part . ' class="wpb-part wpb-part-' . esc_attr( $part ) . '">'
			. Sections::render_part( $doc, $ctx )
			. '</' . $part . '>';
	}

	/**
	 * Replace post content with the builder doc when active.
	 *
	 * @param string $content Post content.
	 * @return string
	 */
	public static function maybe_filter_content( string $content ): string {
		if ( ! in_the_loop() || ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( ! $post_id || ! Document::is_active( $post_id ) ) {
			return $content;
		}

		$doc = Document::get( $post_id );

		return Sections::render_part( $doc, array( 'context' => 'content' ) );
	}

	/**
	 * Preview notice bar for `?wpb_preview=1` when the page is not published.
	 *
	 * @return string
	 */
	public static function preview_bar(): string {
		$post_id = get_the_ID();
		$post    = $post_id ? get_post( $post_id ) : null;

		if ( ! $post || 'publish' === $post->post_status || ! current_user_can( 'edit_pages' ) ) {
			return '';
		}

		return '<div class="wpb-preview-bar">'
			. esc_html__( 'Preview mode — this page is not published yet.', 'wp-builder' )
			. ' <a href="' . esc_url( get_permalink( $post_id ) ) . '">' . esc_html__( 'View published page', 'wp-builder' ) . '</a>'
			. '</div>';
	}

	/**
	 * Preview URL for a page or part.
	 *
	 * @param int    $post_id Page ID.
	 * @param string $part    Optional part key (header/footer) for isolated preview.
	 * @return string
	 */
	public static function preview_url( int $post_id, string $part = '' ): string {
		if ( '' !== $part ) {
			return add_query_arg(
				array( 'wpb_preview' => 1, 'wpb_part' => $part ),
				home_url( '/' )
			);
		}

		return add_query_arg(
			array( 'p' => $post_id, 'preview' => 'true', 'wpb_preview' => 1 ),
			home_url( '/' )
		);
	}
}
