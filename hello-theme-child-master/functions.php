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

