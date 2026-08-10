<?php
/**
 * Main bootstrap: hooks, menus, assets, REST, template routing.
 *
 * @package WP_Builder
 */

namespace WPB;

defined( 'ABSPATH' ) || exit;

/**
 * Class Main
 */
final class Main {

	/**
	 * Init.
	 */
	public static function init(): void {
		add_action( 'init', array( __CLASS__, 'register_part_post_type' ), 5 );
		add_action( 'wp_abilities_api_categories_init', array( __CLASS__, 'register_category' ) );
		add_action( 'wp_abilities_api_init', array( Abilities\Registry::class, 'register' ) );
		add_action( 'rest_api_init', array( __CLASS__, 'register_rest' ) );
		add_action( 'admin_menu', array( __CLASS__, 'admin_menu' ) );

		// Frontend.
		add_action( 'wp_enqueue_scripts', array( Render\Assets::class, 'enqueue' ), 20 );
		add_filter( 'template_include', array( Render\Template::class, 'template_include' ), 10 );
		add_filter( 'the_content', array( Render\Renderer::class, 'maybe_filter_content' ), 5 );
	}

	/**
	 * Register the wpb_part custom post type (header/footer parts).
	 */
	public static function register_part_post_type(): void {
		register_post_type(
			'wpb_part',
			array(
				'labels'              => array(
					'name'          => __( 'Builder Parts', 'wp-builder' ),
					'singular_name' => __( 'Builder Part', 'wp-builder' ),
				),
				'public'              => false,
				'publicly_queryable'  => false,
				'show_ui'             => false,
				'show_in_menu'        => false,
				'show_in_nav_menus'   => false,
				'exclude_from_search' => true,
				'supports'            => array( 'title' ),
				'capability_type'     => 'page',
				'has_archive'         => false,
			)
		);
	}

	/**
	 * Register the wp-builder ability category.
	 */
	public static function register_category(): void {
		wp_register_ability_category(
			WPB_ABILITY_CATEGORY,
			array(
				'label'       => __( 'WP Builder', 'wp-builder' ),
				'description' => __( 'Page, section, header and footer tools for the chat page builder.', 'wp-builder' ),
			)
		);
	}

	/**
	 * Register REST routes.
	 */
	public static function register_rest(): void {
		( new REST\Chat_Controller() )->register();
		( new REST\Pages_Controller() )->register();
		( new REST\Settings_Controller() )->register();
	}

	/**
	 * Admin menu.
	 */
	public static function admin_menu(): void {
		$hook = add_menu_page(
			__( 'Builder', 'wp-builder' ),
			__( 'Builder', 'wp-builder' ),
			'manage_options',
			'wp-builder',
			array( __CLASS__, 'render_page' ),
			'dashicons-layout',
			58
		);

		add_action( 'admin_enqueue_scripts', static function ( $current_hook ) use ( $hook ) {
			if ( $current_hook === $hook ) {
				Admin\Assets::enqueue();
			}
		} );
	}

	/**
	 * Render the React app container.
	 */
	public static function render_page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'Insufficient permissions.', 'wp-builder' ) );
		}

		$assets = Admin\Assets::data();

		echo '<div class="wrap">';
		echo '<div id="wpb-app" data-settings="' . esc_attr( wp_json_encode( $assets, JSON_HEX_TAG ) ) . '"></div>';
		echo '</div>';
	}
}
