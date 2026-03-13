<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles WordPress/WooCommerce hooks.
 */
class HPCO_Hooks {

	/**
	 * @var HPCO_Template_Loader
	 */
	private $template_loader;

	/**
	 * Constructor.
	 */
	public function __construct( $template_loader ) {
		$this->template_loader = $template_loader;
	}

	/**
	 * Initialize hooks.
	 */
	public function init() {
		$enabled = get_option( 'hpco_enable_override', 'yes' );
		
		/**
		 * Filter to allow programmatic enabling/disabling of the override.
		 * 
		 * @param bool $enabled
		 */
		$is_enabled = apply_filters( 'hpco_enable_override', ( 'yes' === $enabled ) );

		if ( ! $is_enabled ) {
			return;
		}

		// Enqueue styles and scripts.
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

		// AJAX for Quick Buy.
		add_action( 'wp_ajax_hpco_load_quick_buy', array( $this, 'ajax_load_quick_buy' ) );
		add_action( 'wp_ajax_nopriv_hpco_load_quick_buy', array( $this, 'ajax_load_quick_buy' ) );

		// Add filter for WooCommerce Blocks.
		add_filter( 'woocommerce_blocks_product_grid_item_html', array( $this, 'render_block_product_card' ), 10, 3 );

		// Footer template for the drawer and SVG sprite.
		add_action( 'wp_footer', array( $this, 'render_svg_sprite' ), 5 );
		add_action( 'wp_footer', array( $this, 'render_drawer_template' ), 10 );

		// Apply global loop override, but only in appropriate contexts.
		add_action( 'wp', array( $this, 'apply_loop_override' ) );
	}

	/**
	 * Apply the loop override by removing default hooks and adding our custom card renderer.
	 * Skips the override on cart and checkout pages to prevent layout/functional issues.
	 */
	public function apply_loop_override() {
		if ( is_cart() || is_checkout() || is_admin() ) {
			return;
		}

		// Remove default WooCommerce loop actions.
		remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_show_product_loop_sale_flash', 10 );
		remove_action( 'woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_product_thumbnail', 10 );
		remove_action( 'woocommerce_shop_loop_item_title', 'woocommerce_template_loop_product_title', 10 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );
		remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );
		remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );

		// Add custom card rendering.
		add_action( 'woocommerce_before_shop_loop_item', array( $this, 'render_product_card' ), 10 );
	}

	/**
	 * Render the custom product card.
	 */
	public function render_product_card() {
		global $product;
		if ( ! $product ) {
			return;
		}

		$this->template_loader->get_template( 'product-card.php', array( 'product' => $product ) );
	}

	/**
	 * Filter for WooCommerce Blocks product card HTML.
	 */
	public function render_block_product_card( $html, $data, $product_obj ) {
		global $product;
		$original_product = $product;
		$product          = $product_obj;

		ob_start();
		$this->template_loader->get_template( 'product-card.php', array( 'product' => $product ) );
		$card_html = ob_get_clean();

		$product = $original_product; // Restore original global.

		return sprintf(
			'<li class="wc-block-grid__product">%s</li>',
			$card_html
		);
	}

	/**
	 * Helper to determine if we should load HPCO assets/HTML on the current page.
	 */
	private function should_load_assets() {
		if ( is_woocommerce() || is_shop() || is_product_category() || is_product_tag() || is_cart() || is_checkout() ) {
			return true;
		}

		if ( function_exists( 'has_block' ) ) {
			$woo_blocks = array( 
				'woocommerce/product-new', 
				'woocommerce/handpicked-products', 
				'woocommerce/product-best-sellers', 
				'woocommerce/product-category', 
				'woocommerce/product-on-sale', 
				'woocommerce/product-collection',
				'house-products/carousel'
			);
			foreach ( $woo_blocks as $block ) {
				if ( has_block( $block ) ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Enqueue frontend assets.
	 */
	public function enqueue_assets() {
		if ( ! $this->should_load_assets() ) {
			return;
		}

		wp_enqueue_style(
			'hpco-product-card',
			HPCO_PLUGIN_URL . 'assets/css/product-card.css',
			array(),
			HPCO_VERSION
		);

		wp_enqueue_script(
			'hpco-quick-buy',
			HPCO_PLUGIN_URL . 'assets/js/quick-buy.js',
			array( 'jquery' ),
			HPCO_VERSION,
			true
		);

		wp_localize_script( 'hpco-quick-buy', 'hpcoData', array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'hpco-quick-buy-nonce' ),
		) );
	}

	/**
	 * AJAX handler for loading Quick Buy content.
	 */
	public function ajax_load_quick_buy() {
		check_ajax_referer( 'hpco-quick-buy-nonce', 'nonce' );

		$product_id = isset( $_POST['product_id'] ) ? intval( $_POST['product_id'] ) : 0;
		if ( ! $product_id ) {
			wp_send_json_error( array( 'message' => 'Invalid product ID' ) );
		}

		// Try to get from cache first.
		$cache_key = 'hpco_quick_buy_' . $product_id;
		$cached_data = get_transient( $cache_key );
		
		if ( $cached_data !== false ) {
			wp_send_json_success( $cached_data );
		}

		$product = wc_get_product( $product_id );
		if ( ! $product ) {
			wp_send_json_error( array( 'message' => 'Product not found' ) );
		}

		// Check if APF function exists.
		if ( ! function_exists( 'apf_render_buy_now_form' ) ) {
			wp_send_json_error( array( 'message' => 'APF Dynamic Buy Now Support plugin is not active or helper function missing.' ) );
		}

		$form_html = apf_render_buy_now_form( $product_id );
		
		if ( ! $form_html ) {
			wp_send_json_error( array( 'message' => 'Could not generate form.' ) );
		}

		$response_data = array( 
			'html'  => $form_html,
			'title' => $product->get_name(),
			'price' => $product->get_price_html(),
			'image' => get_the_post_thumbnail( $product_id, 'thumbnail', array( 'class' => 'hpco-header-thumb' ) )
		);

		// Cache for 1 hour.
		set_transient( $cache_key, $response_data, HOUR_IN_SECONDS );

		wp_send_json_success( $response_data );
	}

	/**
	 * Render the drawer HTML boilerplate in the footer.
	 */
	public function render_drawer_template() {
		if ( ! $this->should_load_assets() ) {
			return;
		}
		?>
		<div class="hpco-drawer-overlay"></div>
		<div class="hpco-drawer">
			<div class="hpco-drawer__header">
				<div class="hpco-drawer__product-info">
					<div class="hpco-drawer__thumbnail"></div>
					<div class="hpco-drawer__meta">
						<h3 class="hpco-drawer__title"><?php esc_html_e( 'Quick Buy', 'house-product-card-override' ); ?></h3>
						<div class="hpco-drawer__price"></div>
					</div>
				</div>
				<button class="hpco-drawer__close">&times;</button>
			</div>
			<div class="hpco-drawer__body">
				<div class="hpco-drawer__placeholder">
					<div class="hpco-drawer__loader">
						<div class="hpco-spinner"></div>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Render the SVG sprite in the footer.
	 */
	public function render_svg_sprite() {
		if ( ! $this->should_load_assets() ) {
			return;
		}
		HPCO_Helpers::render_svg_sprite();
	}
}
