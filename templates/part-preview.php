<?php
/**
 * Template: isolated header/footer part preview.
 *
 * @package WP_Builder
 */

defined( 'ABSPATH' ) || exit;

use WPB\Render\Renderer;

$part = isset( $_GET['wpb_part'] ) ? sanitize_key( wp_unslash( $_GET['wpb_part'] ) ) : '';
$part = in_array( $part, array( 'header', 'footer' ), true ) ? $part : 'header';
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
<style>body{background:#f1f5f9;padding:24px}</style>
</head>
<body <?php body_class( 'wpb-part-preview wpb-part-preview-' . $part ); ?>>
<?php wp_body_open(); ?>
<?php echo Renderer::render_part_html( $part ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
<?php wp_footer(); ?>
</body>
</html>
