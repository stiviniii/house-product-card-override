<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
class HPCO_Plugin {

	/**
	 * Initialize the plugin.
	 */
	public function run() {
		// Only run if WooCommerce is active.
		if ( ! $this->is_woocommerce_active() ) {
			return;
		}

		$this->load_dependencies();
		$this->init_hooks();
		$this->init_admin_settings();
	}

	/**
	 * Check if WooCommerce is active.
	 */
	private function is_woocommerce_active() {
		return class_exists( 'WooCommerce' );
	}

	/**
	 * Load required files.
	 */
	private function load_dependencies() {
		require_once HPCO_PLUGIN_DIR . 'includes/class-helpers.php';
		require_once HPCO_PLUGIN_DIR . 'includes/class-hooks.php';
		require_once HPCO_PLUGIN_DIR . 'includes/class-template-loader.php';
	}

	/**
	 * Initialize hooks.
	 */
	private function init_hooks() {
		$template_loader = new HPCO_Template_Loader();
		$hooks = new HPCO_Hooks( $template_loader );
		$hooks->init();
	}

	/**
	 * Initialize admin settings.
	 */
	private function init_admin_settings() {
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );
	}

	/**
	 * Add Settings Page under WooCommerce or Settings.
	 */
	public function add_settings_page() {
		add_options_page(
			__( 'House Product Card Override', 'house-product-card-override' ),
			__( 'House Product Card', 'house-product-card-override' ),
			'manage_options',
			'hpco-settings',
			array( $this, 'render_settings_page' )
		);
	}

	/**
	 * Register Plugin Settings.
	 */
	public function register_settings() {
		register_setting( 'hpco_settings_group', 'hpco_enable_override' );
		
		// Set default value if not set.
		if ( false === get_option( 'hpco_enable_override' ) ) {
			update_option( 'hpco_enable_override', 'yes' );
		}
	}

	/**
	 * Render Settings Page.
	 */
	public function render_settings_page() {
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'House Product Card Override Settings', 'house-product-card-override' ); ?></h1>
			<form method="post" action="options.php">
				<?php
				settings_fields( 'hpco_settings_group' );
				do_settings_sections( 'hpco_settings_group' );
				?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row"><?php esc_html_e( 'Enable Global Override', 'house-product-card-override' ); ?></th>
						<td>
							<input type="checkbox" name="hpco_enable_override" value="yes" <?php checked( get_option( 'hpco_enable_override' ), 'yes' ); ?> />
							<p class="description"><?php esc_html_e( 'When enabled, the default WooCommerce product loop design will be replaced by the custom card design.', 'house-product-card-override' ); ?></p>
						</td>
					</tr>
				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}
