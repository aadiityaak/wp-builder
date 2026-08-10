<?php
/**
 * WP Builder
 *
 * @package     WP_Builder
 * @author      WebSweet
 * @license     GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WP Builder
 * Plugin URI:        https://websweetstudio.com/
 * Description:       Chat-driven page builder. Build pages, edit sections, headers and footers directly from a chat interface, powered by your own AI provider.
 * Version:           0.1.0
 * Requires at least: 6.6
 * Requires PHP:      7.4
 * Author:            WebSweet
 * License:           GPL-2.0-or-later
 * License URI:       https://spdx.org/licenses/GPL-2.0-or-later.html
 * Text Domain:       wp-builder
 */

defined( 'ABSPATH' ) || exit;

define( 'WPB_VERSION', '0.1.0' );
define( 'WPB_PLUGIN_FILE', __FILE__ );
define( 'WPB_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'WPB_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'WPB_ABILITY_CATEGORY', 'wp-builder' );

require_once WPB_PLUGIN_DIR . 'includes/autoload.php';

WPB\Main::init();
