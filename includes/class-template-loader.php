<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles template loading.
 */
class HPCO_Template_Loader {

	/**
	 * Load a template file.
	 * 
	 * @param string $template_name Template name.
	 * @param array $args Arguments to pass to the template.
	 */
	public function get_template( $template_name, $args = array() ) {
		/**
		 * Filter the template path.
		 * 
		 * @param string $template_path
		 * @param string $template_name
		 */
		$template_path = apply_filters( 'hpco_product_card_template_path', HPCO_PLUGIN_DIR . 'templates/' . $template_name, $template_name );

		if ( file_exists( $template_path ) ) {
			if ( ! empty( $args ) ) {
				extract( $args ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
			}
			include $template_path;
		}
	}
}
