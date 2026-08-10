<?php
/**
 * Ability registry: registers the wpb/* tool set.
 *
 * @package WP_Builder
 */

namespace WPB\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * Class Registry
 */
final class Registry {

	/**
	 * Register all wpb/* abilities.
	 */
	public static function register(): void {
		$tools = array(
			Pages::tool_list_pages(),
			Pages::tool_get_page(),
			Pages::tool_create_page(),
			Pages::tool_update_page(),
			Pages::tool_delete_page(),
			Pages::tool_publish_page(),
			Sections::tool_get_sections(),
			Sections::tool_add_section(),
			Sections::tool_update_section(),
			Sections::tool_delete_section(),
			Sections::tool_move_section(),
			Parts::tool_get_part(),
			Parts::tool_update_part(),
		);

		foreach ( $tools as $tool ) {
			wp_register_ability(
				$tool['name'],
				array(
					'label'               => $tool['label'],
					'description'         => $tool['description'],
					'category'            => WPB_ABILITY_CATEGORY,
					'input_schema'        => $tool['input_schema'],
					'execute_callback'    => $tool['execute_callback'],
					'permission_callback' => $tool['permission_callback'] ?? array( __CLASS__, 'can_edit_pages' ),
					'meta'                => array(
						'annotations' => array(
							'readonly'    => ! empty( $tool['readonly'] ),
							'destructive' => ! empty( $tool['destructive'] ),
						),
						'show_in_rest' => false,
					),
				)
			);
		}
	}

	/**
	 * Default permission: can the current user manage builder pages?
	 *
	 * @return bool
	 */
	public static function can_edit_pages(): bool {
		return current_user_can( 'edit_pages' );
	}

	/**
	 * Integer property schema helper.
	 *
	 * @param string $description Description.
	 * @return array
	 */
	public static function int_prop( string $description ): array {
		return array(
			'type'        => 'integer',
			'description' => $description,
		);
	}

	/**
	 * String property schema helper.
	 *
	 * @param string $description Description.
	 * @return array
	 */
	public static function str_prop( string $description ): array {
		return array(
			'type'        => 'string',
			'description' => $description,
		);
	}
}
