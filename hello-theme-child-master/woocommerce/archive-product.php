<?php
/**
 * The Template for displaying product archives, including the main shop page which is a post type archive
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/archive-product.php.
 *
 * @package HelloElementorChild
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

/**
 * Hook: woocommerce_before_main_content.
 *
 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
 * @hooked woocommerce_breadcrumb - 20
 * @hooked WC_Structured_Data::generate_website_data() - 30
 */
do_action( 'woocommerce_before_main_content' );
?>

<div class="shop-archive-container">
	
	<!-- Left Sidebar with Categories and Themes -->
	<aside class="shop-sidebar">
		
		<!-- Product Categories -->
		<div class="sidebar-section">
			<h3 class="sidebar-title">Catégories</h3>
			<?php
			// Define the desired category order (exact order to display)
			$category_order = array(
				'Livres',
				'Ebooks',
				'Livres audio',
				'Sadhanas & prières',
				'Cartes & stickers',
				'PDF'
			);
			
			// Categories to exclude (case-insensitive check)
			$excluded_categories = array( 'Livres papier', 'Livres Papier', 'livres papier' );
			
			// Get ALL categories including empty ones - without parent restriction first
			$all_categories = get_terms( array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => false, // Show even if empty
			) );
			
			// Create a map of category names to category objects
			$category_map = array();
			if ( ! empty( $all_categories ) && ! is_wp_error( $all_categories ) ) {
				foreach ( $all_categories as $category ) {
					$category_name = trim( $category->name );
					
					// Skip excluded categories (case-insensitive)
					$is_excluded = false;
					foreach ( $excluded_categories as $excluded ) {
						if ( strcasecmp( trim( $category_name ), trim( $excluded ) ) === 0 ) {
							$is_excluded = true;
							break;
						}
					}
					
					if ( ! $is_excluded ) {
						// Only include top-level categories (parent = 0) or categories in our order list
						if ( $category->parent == 0 || in_array( $category_name, $category_order ) ) {
							$category_map[ $category_name ] = $category;
						}
					}
				}
			}
			
			// Also try to get categories by name directly if they're in our order list but not found
			// Try multiple variations of the name to catch any case/spelling differences
			foreach ( $category_order as $ordered_name ) {
				if ( ! isset( $category_map[ $ordered_name ] ) ) {
					// Try exact name match
					$term = get_term_by( 'name', $ordered_name, 'product_cat' );
					if ( ! $term || is_wp_error( $term ) ) {
						// Try case-insensitive search through all categories
						foreach ( $all_categories as $cat ) {
							if ( strcasecmp( trim( $cat->name ), trim( $ordered_name ) ) === 0 ) {
								$term = $cat;
								break;
							}
						}
					}
					if ( $term && ! is_wp_error( $term ) ) {
						$category_map[ $ordered_name ] = $term;
					}
				}
			}
			
			// Build ordered array based on category_order - always show all in order
			$ordered_categories = array();
			foreach ( $category_order as $ordered_name ) {
				if ( isset( $category_map[ $ordered_name ] ) ) {
					$ordered_categories[] = $category_map[ $ordered_name ];
				}
			}
			
			// Always display the list - show all categories in order
			echo '<ul class="product-categories-list">';
			foreach ( $ordered_categories as $category ) {
				$category_link = get_term_link( $category );
				if ( is_wp_error( $category_link ) ) {
					continue;
				}
				$active_class = ( is_product_category( $category->slug ) ) ? ' active' : '';
				echo '<li class="cat-item' . $active_class . '">';
				echo '<a href="' . esc_url( $category_link ) . '">' . esc_html( $category->name ) . '</a>';
				echo '</li>';
			}
			echo '</ul>';
			?>
		</div>
		
		<!-- Product Tags/Themes -->
		<div class="sidebar-section">
			<h3 class="sidebar-title">Thèmes</h3>
			<?php
			$product_tags = get_terms( array(
				'taxonomy'   => 'product_tag',
				'hide_empty' => true,
				'number'     => 20, // Limit to 20 tags
			) );
			
			if ( ! empty( $product_tags ) && ! is_wp_error( $product_tags ) ) {
				echo '<ul class="product-tags-list">';
				foreach ( $product_tags as $tag ) {
					$tag_link = get_term_link( $tag );
					$active_class = ( is_product_tag( $tag->slug ) ) ? ' active' : '';
					echo '<li class="tag-item' . $active_class . '">';
					echo '<a href="' . esc_url( $tag_link ) . '">' . esc_html( $tag->name ) . '</a>';
					echo '</li>';
				}
				echo '</ul>';
			}
			?>
		</div>
		
	</aside>
	
	<!-- Main Content Area -->
	<div class="shop-main-content">
		
		<?php

/**
		 * Hook: woocommerce_shop_loop_header.
		 *
		 * @since 8.6.0
		 *
		 * @hooked woocommerce_product_taxonomy_archive_header - 10
		 */
		do_action( 'woocommerce_shop_loop_header' );

		if ( woocommerce_product_loop() ) {

			/**
			 * Top bar with result count and sorting
			 */
			?>
			<div class="shop-toolbar">
				<div class="result-count-wrapper">
					<?php
					$total    = wc_get_loop_prop( 'total' );
					$per_page = wc_get_loop_prop( 'per_page' );
					$current  = wc_get_loop_prop( 'current_page' );
					$first    = ( $per_page * $current ) - $per_page + 1;
					$last     = min( $total, $per_page * $current );
					
					printf(
						'Affichage De %s–%s Sur %s Résultats',
						'<span class="count-first">' . number_format_i18n( $first ) . '</span>',
						'<span class="count-last">' . number_format_i18n( $last ) . '</span>',
						'<span class="count-total">' . number_format_i18n( $total ) . '</span>'
					);
					?>
				</div>
				<div class="ordering-wrapper">
					<?php woocommerce_catalog_ordering(); ?>
				</div>
			</div>
			<?php

			/**
			 * Hook: woocommerce_before_shop_loop.
			 *
			 * @hooked woocommerce_output_all_notices - 10
			 * @hooked woocommerce_result_count - 20 (removed in functions.php, custom above)
			 * @hooked woocommerce_catalog_ordering - 30 (removed in functions.php, custom above)
			 */
			do_action( 'woocommerce_before_shop_loop' );

			woocommerce_product_loop_start();

			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();

					/**
					 * Hook: woocommerce_shop_loop.
					 */
					do_action( 'woocommerce_shop_loop' );

					wc_get_template_part( 'content', 'product' );
				}
			}

			woocommerce_product_loop_end();

			/**
			 * Hook: woocommerce_after_shop_loop.
			 *
			 * @hooked woocommerce_pagination - 10
			 */
			?>
			<div class="shop-pagination-wrapper">
				<?php do_action( 'woocommerce_after_shop_loop' ); ?>
			</div>
			<?php
		} else {
			/**
			 * Hook: woocommerce_no_products_found.
			 *
			 * @hooked wc_no_products_found - 10
			 */
			do_action( 'woocommerce_no_products_found' );
		}
		?>
		
	</div><!-- .shop-main-content -->
	
</div><!-- .shop-archive-container -->

<?php
/**
 * Hook: woocommerce_after_main_content.
 *
 * @hooked woocommerce_output_content_wrapper_end - 10 (outputs closing divs for the content)
 */
do_action( 'woocommerce_after_main_content' );

get_footer( 'shop' );

