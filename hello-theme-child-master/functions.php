<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );

function mytheme_enqueue_google_fonts() {
    wp_enqueue_style( 'my-google-fonts', 'https://fonts.googleapis.com/css2?family=Lato:wght@300;400;700&display=swap', false );
}
add_action( 'wp_enqueue_scripts', 'mytheme_enqueue_google_fonts' );

/**
 * Enqueue WooCommerce custom styles
 *
 * @return void
 */
function hello_elementor_child_woocommerce_styles() {
	// Only load on WooCommerce pages
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		wp_enqueue_style(
			'hello-elementor-child-woocommerce',
			get_stylesheet_directory_uri() . '/woocommerce.css',
			array( 'hello-elementor-child-style' ),
			HELLO_ELEMENTOR_CHILD_VERSION
		);
	}
}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_woocommerce_styles', 30 );

/**
 * WooCommerce Customization Hooks
 * 
 * Add your custom WooCommerce hooks and filters here
 */

// Example: Change number of products per row
// add_filter( 'loop_shop_columns', function() {
// 	return 3; // 3 products per row
// });

// Example: Change number of products per page
// add_filter( 'loop_shop_per_page', function() {
// 	return 12; // 12 products per page
// });

// Example: Remove product link from product images (archive)
// remove_action( 'woocommerce_before_shop_loop_item', 'woocommerce_template_loop_product_link_open', 10 );
// remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_product_link_close', 5 );

// Example: Add custom content before product title on archive pages
// add_action( 'woocommerce_shop_loop_item_title', function() {
// 	echo '<div class="custom-badge">Custom Content</div>';
// }, 5 );

// Example: Remove product rating on archive pages
// remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );

// Display product weight
add_shortcode( 'product_weight', function() {
    global $product;
    if ( ! $product ) return '';
    if ( $product->has_weight() ) {
        return '<p><strong>Weight:</strong> ' . wc_format_weight( $product->get_weight() ) . '</p>';
    }
    return '';
});

// Display product dimensions
add_shortcode( 'product_dimensions', function() {
    global $product;
    if ( ! $product ) return '';
    if ( $product->has_dimensions() ) {
        return '<p><strong>Dimensions:</strong> ' . wc_format_dimensions( $product->get_dimensions(false) ) . '</p>';
    }
    return '';
});

/**
 * Remove default WooCommerce archive elements (only on archive pages)
 * This prevents the actions from being removed globally and affecting other pages like single products.
 *
 * @return void
 */
function hello_elementor_child_modify_archive_actions() {
    if ( is_shop() || is_product_category() || is_product_tag() ) {
        remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
        remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
    }
}
add_action( 'template_redirect', 'hello_elementor_child_modify_archive_actions' );

/**
 * Filter shop page to show only 'Livres' category products by default
 * Only applies to the main shop page, not when viewing a specific category
 *
 * @param WP_Query $query The WP_Query instance.
 * @return void
 */
function hello_elementor_child_filter_shop_by_livres( $query ) {
    // Only on main shop page, not on category pages, and only for main query
    if ( ! is_admin() && $query->is_main_query() && is_shop() && ! is_product_category() ) {
        // Get the 'Livres' category term
        $livres_term = get_term_by( 'name', 'Livres', 'product_cat' );
        
        if ( $livres_term && ! is_wp_error( $livres_term ) ) {
            // Set the tax query to filter by 'Livres' category
            $tax_query = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $livres_term->term_id,
                ),
            );
            
            $query->set( 'tax_query', $tax_query );
        }
    }
}
add_action( 'pre_get_posts', 'hello_elementor_child_filter_shop_by_livres' );

