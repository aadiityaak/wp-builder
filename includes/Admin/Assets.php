<?php
/**
 * Admin asset loading for the React app.
 *
 * @package WP_Builder
 */

namespace WPB\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Class Assets
 */
final class Assets {

	/**
	 * Enqueue the compiled admin app.
	 */
	public static function enqueue(): void {
		$asset = WPB_PLUGIN_DIR . 'build/index.asset.php';

		if ( ! file_exists( $asset ) ) {
			// Not built yet: expose a plain hint.
			wp_enqueue_style( 'wp-builder-admin-fallback', WPB_PLUGIN_URL . 'assets/css/admin-fallback.css', array(), WPB_VERSION );
			return;
		}

		$meta = require $asset;

		wp_enqueue_script(
			'wp-builder-admin',
			WPB_PLUGIN_URL . 'build/index.js',
			$meta['dependencies'],
			$meta['version'],
			true
		);

		wp_enqueue_style( 'wp-builder-admin', WPB_PLUGIN_URL . 'build/style-index.css', array( 'wp-components' ), $meta['version'] );

		wp_localize_script(
			'wp-builder-admin',
			'wpBuilder',
			array(
				'restUrl' => esc_url_raw( rest_url( 'wp-builder/v1' ) ),
				'nonce'   => wp_create_nonce( 'wp_rest' ),
				'homeUrl' => esc_url_raw( home_url( '/' ) ),
			)
		);
	}

	/**
	 * Initial data embedded on the page.
	 *
	 * @return array
	 */
	public static function data(): array {
		return array(
			'nonce'   => wp_create_nonce( 'wp_rest' ),
			'restUrl' => esc_url_raw( rest_url( 'wp-builder/v1' ) ),
			'homeUrl' => esc_url_raw( home_url( '/' ) ),
			'built'   => file_exists( WPB_PLUGIN_DIR . 'build/index.asset.php' ),
		);
	}
}
