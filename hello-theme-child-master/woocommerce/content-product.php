<?php
/**
 * The template for displaying product content within loops
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/content-product.php.
 *
 * @package HelloElementorChild
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Ensure visibility.
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}
?>
<li <?php wc_product_class( '', $product ); ?>>
	<?php
	$permalink = esc_url( get_permalink() );
	$title     = get_the_title();
	?>
	<div class="product-card">
		<div class="product-card__media">
			<a href="<?php echo $permalink; ?>">
				<?php echo woocommerce_get_product_thumbnail(); ?>
			</a>
		</div>

		<div class="product-card__body">
			<h2>
				<a href="<?php echo $permalink; ?>">
					<?php echo esc_html( $title ); ?>
				</a>
			</h2>

			<?php
			$subtitle = get_post_meta( get_the_ID(), '_product_subtitle', true );
			if ( $subtitle ) {
				echo '<p>' . esc_html( $subtitle ) . '</p>';
			}

			$author = get_post_meta( get_the_ID(), '_product_author', true );
			if ( $author ) {
				echo '<p>' . esc_html__( 'De ', 'hello-elementor-child' ) . esc_html( $author ) . '</p>';
			}

			if ( has_excerpt() ) {
				$excerpt = wp_trim_words( get_the_excerpt(), 35, '...' );
				echo '<p>' . esc_html( $excerpt ) . '</p>';
			}
			?>
		</div>
	</div>
</li>

