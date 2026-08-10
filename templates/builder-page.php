<?php
/**
 * Template: full builder page frame.
 *
 * Uses the builder header/footer parts when set; otherwise falls back
 * to the active theme's header/footer.
 *
 * @package WP_Builder
 */

defined( 'ABSPATH' ) || exit;

use WPB\Document;
use WPB\Render\Renderer;

$post_id = get_the_ID();
$doc     = $post_id ? Document::get( $post_id ) : Document::empty_doc();

$has_header = Renderer::part_exists( 'header' ) && empty( $doc['settings']['hide_header'] );
$has_footer = Renderer::part_exists( 'footer' ) && empty( $doc['settings']['hide_footer'] );

$header_html = Renderer::render_part_html( 'header' );
$footer_html = Renderer::render_part_html( 'footer' );

$body_class = 'wpb-builder wpb-builder-' . $post_id;

if ( $has_header ) :
	?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class( $body_class ); ?>>
<?php wp_body_open(); ?>
<?php echo $header_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered sections. ?>
<?php else : ?>
<?php get_header(); ?>
<?php endif; ?>

<?php echo Renderer::preview_bar(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>

<?php echo Renderer::render_doc( $doc ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- rendered sections. ?>

<?php if ( $has_footer ) : ?>
<?php echo $footer_html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php wp_footer(); ?>
</body>
</html>
<?php else : ?>
<?php get_footer(); ?>
<?php endif; ?>
