<?php
/**
 * Product Card Template
 *
 * @var WC_Product $product
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Ensure $product is valid.
if ( ! is_a( $product, 'WC_Product' ) ) {
	global $product; // Try getting it from global if not passed or invalid.
}

if ( ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$product_id = $product->get_id();
$title      = $product->get_name();
$price      = $product->get_price_html();
$permalink  = $product->get_permalink();
$image_id   = $product->get_image_id();

// Check for "best-seller" tag.
$tags          = wp_get_post_terms( $product_id, 'product_tag', array( 'fields' => 'slugs' ) );
$is_bestseller = is_array( $tags ) && in_array( 'best-seller', $tags, true );

// Rating.
$rating_count = $product->get_rating_count();
$average      = (float) $product->get_average_rating();

// Specs.
$specs = HPCO_Helpers::get_product_specs( $product_id );
?>

<article class="hpc-card" data-product-id="<?php echo esc_attr( $product_id ); ?>">
	<a href="<?php echo esc_url( $permalink ); ?>" class="hpc-card__link" aria-label="<?php echo esc_attr( $title ); ?>">

		<div class="hpc-card__image-wrapper">
			<?php
			$gallery_ids    = $product->get_gallery_image_ids();
			$hover_image_id = ! empty( $gallery_ids ) ? $gallery_ids[0] : null;
			?>

			<?php if ( $image_id ) : ?>
				<?php
				echo wp_get_attachment_image(
					$image_id,
					'medium_large',
					false,
					array(
						'class'    => 'hpc-card__image hpc-card__image--featured',
						'loading'  => 'lazy',
						'decoding' => 'async',
						'alt'      => esc_attr( $title ),
					)
				);
				?>
				<?php if ( $hover_image_id ) : ?>
					<?php
					echo wp_get_attachment_image(
						$hover_image_id,
						'medium_large',
						false,
						array(
							'class'    => 'hpc-card__image hpc-card__image--hover',
							'loading'  => 'lazy',
							'decoding' => 'async',
							'alt'      => esc_attr( $title ),
						)
					);
					?>
				<?php endif; ?>
			<?php else : ?>
				<div class="hpc-card__image hpc-card__image--placeholder">
					<span><?php esc_html_e( 'No Image', 'house-product-card-override' ); ?></span>
				</div>
			<?php endif; ?>

			<?php 
			/**
			 * Sale Badge
			 */
			woocommerce_show_product_loop_sale_flash(); 
			?>

			<?php if ( $is_bestseller ) : ?>
				<span class="hpc-card__badge"><?php esc_html_e( 'Best Seller', 'house-product-card-override' ); ?></span>
			<?php endif; ?>

			<button class="hpco-quick-buy-btn" data-product-id="<?php echo esc_attr( $product_id ); ?>" aria-label="<?php esc_attr_e( 'Quick Buy', 'house-product-card-override' ); ?>">
				<?php esc_html_e( '+ Quick Buy', 'house-product-card-override' ); ?>
			</button>
		</div>

		<div class="hpc-card__body">
			<h3 class="hpc-card__title"><?php echo esc_html( $title ); ?></h3>
			<div class="hpc-card__price"><span><?php esc_html_e( 'From', 'house-product-card-override' ); ?></span> <?php echo wp_kses_post( $price ); ?></div>

			<?php if ( $rating_count > 0 ) : ?>
				<div class="hpc-card__rating" aria-label="<?php printf( esc_attr__( 'Rated %s out of 5', 'house-product-card-override' ), esc_attr( number_format( $average, 1 ) ) ); ?>">
					<?php echo HPCO_Helpers::render_stars( $average, $product_id ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<span class="hpc-card__rating-count">(<?php echo esc_html( $rating_count ); ?>)</span>
				</div>
			<?php endif; ?>
		</div>

		<?php if ( ! empty( $specs ) ) : ?>
			<div class="hpc-card__specs">
				<?php foreach ( $specs as $spec ) : ?>
					<div class="hpc-card__spec" title="<?php echo esc_attr( $spec['label'] ); ?>">
						<span class="hpc-card__spec-icon">
							<svg width="32" height="32" aria-hidden="true"><use href="#<?php echo esc_attr( $spec['icon_id'] ?? '' ); ?>"></use></svg>
						</span>
						<span class="hpc-card__spec-value"><?php echo wp_kses( $spec['value'], array( 'sup' => array() ) ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

	</a>
</article>
