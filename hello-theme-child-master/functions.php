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
 * Hide BACS (Direct bank transfer) and Cheque payment methods for non-boutique users
 * Only boutique/librairie role can see these payment options
 */
function nem_filter_payment_gateways_for_boutique( $available_gateways ) {
    // Don't run in admin
    if ( is_admin() ) {
        return $available_gateways;
    }
    
    // If user is NOT boutique/librairie, hide BACS and Cheque
    if ( ! nem_user_is_librairie() ) {
        if ( isset( $available_gateways['bacs'] ) ) {
            unset( $available_gateways['bacs'] );
        }
        if ( isset( $available_gateways['cheque'] ) ) {
            unset( $available_gateways['cheque'] );
        }
    }
    
    return $available_gateways;
}
add_filter( 'woocommerce_available_payment_gateways', 'nem_filter_payment_gateways_for_boutique', 100 );

/**
 * Check if current user has the librairie/boutique role
 *
 * @return bool True if user has librairie or boutique role.
 */
function nem_user_is_librairie() {
    if ( ! is_user_logged_in() ) {
        return false;
    }
    
    $user = wp_get_current_user();
    $user_roles = (array) $user->roles;
    
    // Check if "View Admin As" plugin is active and viewing as a role
    if ( function_exists( 'view_admin_as' ) ) {
        $vaa = view_admin_as();
        if ( $vaa && method_exists( $vaa, 'store' ) ) {
            $store = $vaa->store();
            if ( $store && method_exists( $store, 'get_view' ) ) {
                $vaa_role = $store->get_view( 'role' );
                if ( $vaa_role === 'librairie' || $vaa_role === 'boutique' ) {
                    return true;
                }
            }
        }
    }
    
    // Check actual user roles
    return in_array( 'librairie', $user_roles, true ) || in_array( 'boutique', $user_roles, true );
}

/**
 * Make all products in 'Livres papier' category visible to boutique role
 * Hide digital products (Ebooks, Livres audio, PDF) from boutique role
 * Overrides WooCommerce catalog visibility for this role
 */
function nem_product_visibility_for_boutique( $visible, $product_id ) {
    // Only apply for boutique/librairie role
    if ( ! nem_user_is_librairie() ) {
        return $visible;
    }
    
    // Digital categories to hide from boutique
    $digital_categories = array( 'ebooks', 'livres-audio', 'pdf' );
    
    // Hide digital products
    foreach ( $digital_categories as $cat_slug ) {
        if ( has_term( $cat_slug, 'product_cat', $product_id ) ) {
            return false;
        }
    }
    
    // Show 'Livres papier' products (even if hidden in catalog)
    if ( has_term( 'livres-papier', 'product_cat', $product_id ) || has_term( 'Livres papier', 'product_cat', $product_id ) ) {
        return true;
    }
    
    return $visible;
}
add_filter( 'woocommerce_product_is_visible', 'nem_product_visibility_for_boutique', 999, 2 );

/**
 * Also include hidden products in the query for boutique role
 */
function nem_include_hidden_livres_papier_for_boutique( $query ) {
    if ( is_admin() || ! $query->is_main_query() ) {
        return;
    }
    
    // Only apply for boutique/librairie role on shop or category pages
    if ( ! nem_user_is_librairie() ) {
        return;
    }
    
    if ( ! is_shop() && ! is_product_category() ) {
        return;
    }
    
    // Remove the catalog visibility restriction for this query
    if ( isset( $query->query_vars['tax_query'] ) ) {
        $tax_query = $query->query_vars['tax_query'];
        foreach ( $tax_query as $key => $tax ) {
            if ( is_array( $tax ) && isset( $tax['taxonomy'] ) && $tax['taxonomy'] === 'product_visibility' ) {
                unset( $tax_query[ $key ] );
            }
        }
        $query->set( 'tax_query', array_values( $tax_query ) );
    }
}
add_action( 'woocommerce_product_query', 'nem_include_hidden_livres_papier_for_boutique', 5 );

/**
 * Filter shop page to show only 'Livres' category products by default
 * For librairie role users, show 'Livres papier' instead
 * Only applies to the main shop page, not when viewing a specific category
 *
 * @param WP_Query $query The WP_Query instance.
 * @return void
 */
function hello_elementor_child_filter_shop_by_livres( $query ) {
    // Only on main shop page, not on category pages, and only for main query
    if ( ! is_admin() && $query->is_main_query() && is_shop() && ! is_product_category() ) {
        // Check if user is librairie role - show 'Livres papier' instead of 'Livres'
        if ( nem_user_is_librairie() ) {
            $category_name = 'Livres papier';
        } else {
            $category_name = 'Livres';
        }
        
        // Get the category term
        $category_term = get_term_by( 'name', $category_name, 'product_cat' );
        
        if ( $category_term && ! is_wp_error( $category_term ) ) {
            // Set the tax query to filter by the category
            $tax_query = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field'    => 'term_id',
                    'terms'    => $category_term->term_id,
                ),
            );
            
            $query->set( 'tax_query', $tax_query );
        }
        
        // Set default sorting to popularity (uses custom priority) if no orderby is set
        if ( ! isset( $_GET['orderby'] ) ) {
            $_GET['orderby'] = 'popularity'; // Trigger our custom popularity sorting
        }
    }
    
    // Also apply default sorting to category pages if no orderby is set
    if ( ! is_admin() && $query->is_main_query() && is_product_category() && ! isset( $_GET['orderby'] ) ) {
        $_GET['orderby'] = 'popularity'; // Trigger our custom popularity sorting
    }
}
add_action( 'pre_get_posts', 'hello_elementor_child_filter_shop_by_livres' );

/**
 * Set default WooCommerce shop ordering to popularity (uses custom priority)
 *
 * @param string $orderby Current orderby value.
 * @return string Modified orderby value.
 */
function hello_elementor_child_default_shop_orderby( $orderby ) {
    return 'popularity';
}
add_filter( 'woocommerce_default_catalog_orderby', 'hello_elementor_child_default_shop_orderby' );

/**
 * Force product category pages to use archive-product.php template
 * This ensures category pages have the same layout and width as the archive page
 *
 * @param string $template The template file path.
 * @return string Modified template file path.
 */
function hello_elementor_child_category_template( $template ) {
    if ( is_product_category() ) {
        $archive_template = locate_template( array( 'woocommerce/archive-product.php' ) );
        if ( $archive_template ) {
            return $archive_template;
        }
    }
    return $template;
}
add_filter( 'taxonomy_template', 'hello_elementor_child_category_template', 99 );
add_filter( 'archive_template', 'hello_elementor_child_category_template', 99 );

/**
 * Ensure WooCommerce uses our archive template for category pages
 * This is a more direct approach using WooCommerce's template system
 */
function hello_elementor_child_wc_category_template( $template, $template_name, $template_path ) {
    if ( is_product_category() && $template_name === 'archive-product.php' ) {
        $custom_template = locate_template( array( 'woocommerce/archive-product.php' ) );
        if ( $custom_template ) {
            return $custom_template;
        }
    }
    return $template;
}
add_filter( 'woocommerce_locate_template', 'hello_elementor_child_wc_category_template', 10, 3 );

// /**
//  * Adjust Max Mega Menu hover intent settings
//  * 
//  * This function modifies the hover intent timeout and interval for Max Mega Menu
//  * to improve the user experience when navigating dropdown menus.
//  * 
//  * @param array  $attributes            The menu wrapper attributes.
//  * @param string $menu_id               The menu ID.
//  * @param array  $menu_settings         The menu settings.
//  * @param array  $settings              The plugin settings.
//  * @param string $current_theme_location The current theme location.
//  * @return array Modified attributes array.
//  */
// function megamenu_adjust_wrap_attributes( $attributes, $menu_id, $menu_settings, $settings, $current_theme_location ) {

//     // Time (ms) before hiding the menu after mouse leaves
//     $attributes['data-hover-intent-timeout']  = 150; // default is ~250–300

//     // Interval (ms) for checking mouse movement (affects how it detects intent)
//     $attributes['data-hover-intent-interval'] = 100; // you can leave this or tweak

//     return $attributes;
// }
// add_filter( 'megamenu_wrap_attributes', 'megamenu_adjust_wrap_attributes', 10, 5 );

/**
 * ============================================================================
 * CUSTOM PRODUCT ORDERING SYSTEM
 * ============================================================================
 * 
 * This system allows you to:
 * 1. Set a global "popularity priority" for each product
 * 2. Set category-specific ordering (different priority per category)
 * 3. When sorting by "popularity", products with custom priority appear first
 * 4. Products without custom priority fall back to default WooCommerce sorting
 * 
 * How it works:
 * - Products with LOWER priority numbers appear FIRST (1 = highest priority)
 * - Products without a set priority use default WooCommerce popularity (total_sales)
 * - On category pages, category-specific priority is used if set
 * - On shop page with category filter, that category's priority is used
 */

/**
 * Add meta box for custom product ordering in product edit screen
 */
function nem_add_product_ordering_meta_box() {
    add_meta_box(
        'nem_product_ordering',
        __( 'Custom Priority (Popularity Sort)', 'hello-elementor-child' ),
        'nem_product_ordering_meta_box_callback',
        'product',
        'side',
        'high'
    );
}
add_action( 'add_meta_boxes', 'nem_add_product_ordering_meta_box' );

/**
 * Meta box callback - displays the ordering fields
 */
function nem_product_ordering_meta_box_callback( $post ) {
    wp_nonce_field( 'nem_product_ordering_nonce', 'nem_product_ordering_nonce_field' );
    
    // Get global priority
    $global_priority = get_post_meta( $post->ID, '_nem_popularity_priority', true );
    
    // Get all product categories this product belongs to
    $product_categories = wp_get_post_terms( $post->ID, 'product_cat', array( 'fields' => 'all' ) );
    
    echo '<div class="nem-ordering-wrap">';
    
    // Global priority field
    echo '<p><strong>' . esc_html__( 'Global Priority:', 'hello-elementor-child' ) . '</strong></p>';
    echo '<p><input type="number" name="nem_global_priority" value="' . esc_attr( $global_priority ) . '" min="0" step="1" style="width:100%;" placeholder="' . esc_attr__( 'Leave empty = default sort', 'hello-elementor-child' ) . '"></p>';
    echo '<p class="description">' . esc_html__( 'Lower numbers appear first when sorting by popularity. Leave empty for default WooCommerce sorting.', 'hello-elementor-child' ) . '</p>';
    
    // Category-specific priorities
    if ( ! empty( $product_categories ) && ! is_wp_error( $product_categories ) ) {
        echo '<hr style="margin: 15px 0;">';
        echo '<p><strong>' . esc_html__( 'Category Priority:', 'hello-elementor-child' ) . '</strong></p>';
        echo '<p class="description">' . esc_html__( 'Leave empty to use global priority.', 'hello-elementor-child' ) . '</p>';
        
        foreach ( $product_categories as $cat ) {
            $cat_priority = get_post_meta( $post->ID, '_nem_cat_priority_' . $cat->term_id, true );
            echo '<p style="margin-top: 10px;">';
            echo '<label for="nem_cat_priority_' . esc_attr( $cat->term_id ) . '">' . esc_html( $cat->name ) . ':</label><br>';
            echo '<input type="number" id="nem_cat_priority_' . esc_attr( $cat->term_id ) . '" name="nem_cat_priority[' . esc_attr( $cat->term_id ) . ']" value="' . esc_attr( $cat_priority ) . '" min="0" step="1" style="width:100%;" placeholder="' . esc_attr__( 'Use global priority', 'hello-elementor-child' ) . '">';
            echo '</p>';
        }
    }
    
    echo '</div>';
}

/**
 * Save meta box data
 */
function nem_save_product_ordering_meta( $post_id ) {
    // Security checks
    if ( ! isset( $_POST['nem_product_ordering_nonce_field'] ) ) {
        return;
    }
    if ( ! wp_verify_nonce( $_POST['nem_product_ordering_nonce_field'], 'nem_product_ordering_nonce' ) ) {
        return;
    }
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }
    if ( ! current_user_can( 'edit_post', $post_id ) ) {
        return;
    }
    
    // Save global priority
    if ( isset( $_POST['nem_global_priority'] ) ) {
        $global_priority = sanitize_text_field( $_POST['nem_global_priority'] );
        if ( $global_priority !== '' ) {
            update_post_meta( $post_id, '_nem_popularity_priority', intval( $global_priority ) );
        } else {
            delete_post_meta( $post_id, '_nem_popularity_priority' );
        }
    }
    
    // Save category-specific priorities
    if ( isset( $_POST['nem_cat_priority'] ) && is_array( $_POST['nem_cat_priority'] ) ) {
        foreach ( $_POST['nem_cat_priority'] as $term_id => $priority ) {
            $term_id = intval( $term_id );
            $priority = sanitize_text_field( $priority );
            
            if ( $priority !== '' ) {
                update_post_meta( $post_id, '_nem_cat_priority_' . $term_id, intval( $priority ) );
            } else {
                delete_post_meta( $post_id, '_nem_cat_priority_' . $term_id );
            }
        }
    }
}
add_action( 'save_post_product', 'nem_save_product_ordering_meta' );

/**
 * Get the current category ID for ordering purposes
 * Works on category pages AND shop page with category filter
 */
function nem_get_current_category_for_ordering() {
    // First check if on actual category page
    if ( is_product_category() ) {
        $current_cat = get_queried_object();
        if ( $current_cat && isset( $current_cat->term_id ) ) {
            return $current_cat->term_id;
        }
    }
    
    // Check if shop page is filtering by category (like "Livres" filter)
    if ( is_shop() ) {
        // Check the current query's tax_query for product_cat
        global $wp_query;
        if ( isset( $wp_query->query_vars['tax_query'] ) ) {
            foreach ( $wp_query->query_vars['tax_query'] as $tax_query ) {
                if ( is_array( $tax_query ) && isset( $tax_query['taxonomy'] ) && $tax_query['taxonomy'] === 'product_cat' ) {
                    if ( isset( $tax_query['terms'] ) ) {
                        $terms = (array) $tax_query['terms'];
                        if ( ! empty( $terms ) ) {
                            return is_numeric( $terms[0] ) ? intval( $terms[0] ) : null;
                        }
                    }
                }
            }
        }
    }
    
    return null;
}

/**
 * Override WooCommerce popularity sorting to use custom priority
 * Products with custom priority appear first, then fallback to default WooCommerce sorting
 */
add_filter( 'posts_clauses', 'nem_custom_popularity_clauses', 999, 2 );

function nem_custom_popularity_clauses( $clauses, $query ) {
    global $wpdb;
    
    // Only run on frontend
    if ( is_admin() ) {
        return $clauses;
    }
    
    // Check if popularity sorting is requested
    if ( ! isset( $_GET['orderby'] ) || $_GET['orderby'] !== 'popularity' ) {
        return $clauses;
    }
    
    // Check if this is a product query
    $post_type = $query->get( 'post_type' );
    $is_product_query = ( $post_type === 'product' || ( is_array( $post_type ) && in_array( 'product', $post_type ) ) );
    $is_wc_query = isset( $query->query_vars['wc_query'] ) && $query->query_vars['wc_query'] === 'product_query';
    
    if ( ! $is_product_query && ! $is_wc_query ) {
        return $clauses;
    }
    
    // Prevent running multiple times per request
    static $already_run = false;
    if ( $already_run ) {
        return $clauses;
    }
    $already_run = true;
    
    // Get current category (works for category pages AND shop with category filter)
    $current_cat_id = nem_get_current_category_for_ordering();
    
    // Determine which meta keys to use
    $cat_meta_key = $current_cat_id ? '_nem_cat_priority_' . intval( $current_cat_id ) : null;
    $global_meta_key = '_nem_popularity_priority';
    
    // Add LEFT JOINs for our priority meta fields
    if ( $cat_meta_key ) {
        $clauses['join'] .= $wpdb->prepare(
            " LEFT JOIN {$wpdb->postmeta} AS nem_cat_priority ON ({$wpdb->posts}.ID = nem_cat_priority.post_id AND nem_cat_priority.meta_key = %s) ",
            $cat_meta_key
        );
    }
    
    $clauses['join'] .= $wpdb->prepare(
        " LEFT JOIN {$wpdb->postmeta} AS nem_global_priority ON ({$wpdb->posts}.ID = nem_global_priority.post_id AND nem_global_priority.meta_key = %s) ",
        $global_meta_key
    );
    
    // Add join for total_sales (WooCommerce default popularity)
    $clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS nem_total_sales ON ({$wpdb->posts}.ID = nem_total_sales.post_id AND nem_total_sales.meta_key = 'total_sales') ";
    
    // Build the ORDER BY clause
    // Logic: Products with custom priority (category or global) appear first ordered by priority
    // Then products without custom priority appear ordered by total_sales, then by date (newest first)
    if ( $cat_meta_key ) {
        $clauses['orderby'] = "
            CASE 
                WHEN nem_cat_priority.meta_value IS NOT NULL THEN 0
                WHEN nem_global_priority.meta_value IS NOT NULL THEN 0
                ELSE 1
            END ASC,
            CASE 
                WHEN nem_cat_priority.meta_value IS NOT NULL THEN CAST(nem_cat_priority.meta_value AS UNSIGNED)
                WHEN nem_global_priority.meta_value IS NOT NULL THEN CAST(nem_global_priority.meta_value AS UNSIGNED)
                ELSE 999999
            END ASC,
            CAST(COALESCE(nem_total_sales.meta_value, 0) AS UNSIGNED) DESC,
            {$wpdb->posts}.menu_order ASC,
            {$wpdb->posts}.post_date DESC
        ";
    } else {
        $clauses['orderby'] = "
            CASE 
                WHEN nem_global_priority.meta_value IS NOT NULL THEN 0
                ELSE 1
            END ASC,
            CASE 
                WHEN nem_global_priority.meta_value IS NOT NULL THEN CAST(nem_global_priority.meta_value AS UNSIGNED)
                ELSE 999999
            END ASC,
            CAST(COALESCE(nem_total_sales.meta_value, 0) AS UNSIGNED) DESC,
            {$wpdb->posts}.menu_order ASC,
            {$wpdb->posts}.post_date DESC
        ";
    }
    
    return $clauses;
}

/**
 * Add admin page for bulk ordering products by category
 */
function nem_add_ordering_admin_menu() {
    add_submenu_page(
        'edit.php?post_type=product',
        __( 'Product Order by Category', 'hello-elementor-child' ),
        __( 'Order by Category', 'hello-elementor-child' ),
        'manage_woocommerce',
        'nem-product-ordering',
        'nem_product_ordering_page'
    );
}
add_action( 'admin_menu', 'nem_add_ordering_admin_menu' );

/**
 * Admin page for bulk product ordering
 */
function nem_product_ordering_page() {
    // Handle form submission
    if ( isset( $_POST['nem_save_ordering'] ) && wp_verify_nonce( $_POST['nem_ordering_nonce'], 'nem_save_ordering' ) ) {
        nem_save_bulk_ordering();
        echo '<div class="notice notice-success"><p>' . esc_html__( 'Product order updated!', 'hello-elementor-child' ) . '</p></div>';
    }
    
    // Get selected category and sort option
    $selected_cat = isset( $_GET['product_cat'] ) ? intval( $_GET['product_cat'] ) : 0;
    $sort_by = isset( $_GET['sort_by'] ) ? sanitize_text_field( $_GET['sort_by'] ) : 'priority';
    
    // Get all product categories
    $categories = get_terms( array(
        'taxonomy' => 'product_cat',
        'hide_empty' => false,
        'orderby' => 'name',
    ) );
    
    echo '<div class="wrap">';
    echo '<h1>' . esc_html__( 'Product Order by Category', 'hello-elementor-child' ) . '</h1>';
    echo '<p class="description">' . esc_html__( 'Set custom priority for products when sorting by popularity. Products with priority appear first, followed by products using default WooCommerce sorting.', 'hello-elementor-child' ) . '</p>';
    
    // Category selector
    echo '<form method="get" style="margin: 20px 0;">';
    echo '<input type="hidden" name="post_type" value="product">';
    echo '<input type="hidden" name="page" value="nem-product-ordering">';
    echo '<label for="product_cat"><strong>' . esc_html__( 'Category:', 'hello-elementor-child' ) . '</strong> </label>';
    echo '<select name="product_cat" id="product_cat" onchange="this.form.submit()">';
    echo '<option value="0">' . esc_html__( '-- Select a category --', 'hello-elementor-child' ) . '</option>';
    foreach ( $categories as $cat ) {
        $selected = $selected_cat === $cat->term_id ? 'selected' : '';
        echo '<option value="' . esc_attr( $cat->term_id ) . '" ' . $selected . '>' . esc_html( $cat->name ) . ' (' . esc_html( $cat->count ) . ')</option>';
    }
    echo '</select>';
    
    // Sort selector (only show if category is selected)
    if ( $selected_cat > 0 ) {
        echo '&nbsp;&nbsp;&nbsp;<label for="sort_by"><strong>' . esc_html__( 'Sort by:', 'hello-elementor-child' ) . '</strong> </label>';
        echo '<select name="sort_by" id="sort_by" onchange="this.form.submit()">';
        echo '<option value="priority"' . selected( $sort_by, 'priority', false ) . '>' . esc_html__( 'Priority', 'hello-elementor-child' ) . '</option>';
        echo '<option value="title"' . selected( $sort_by, 'title', false ) . '>' . esc_html__( 'Title (A-Z)', 'hello-elementor-child' ) . '</option>';
        echo '<option value="title_desc"' . selected( $sort_by, 'title_desc', false ) . '>' . esc_html__( 'Title (Z-A)', 'hello-elementor-child' ) . '</option>';
        echo '<option value="date"' . selected( $sort_by, 'date', false ) . '>' . esc_html__( 'Date (newest)', 'hello-elementor-child' ) . '</option>';
        echo '<option value="date_asc"' . selected( $sort_by, 'date_asc', false ) . '>' . esc_html__( 'Date (oldest)', 'hello-elementor-child' ) . '</option>';
        echo '</select>';
    }
    echo '</form>';
    
    if ( $selected_cat > 0 ) {
        // Get products in this category
        $products = new WP_Query( array(
            'post_type' => 'product',
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $selected_cat,
                ),
            ),
            'orderby' => 'title',
            'order' => 'ASC',
        ) );
        
        // Build products array
        $sorted_products = array();
        if ( $products->have_posts() ) {
            while ( $products->have_posts() ) {
                $products->the_post();
                $product_id = get_the_ID();
                $cat_priority = get_post_meta( $product_id, '_nem_cat_priority_' . $selected_cat, true );
                $global_priority = get_post_meta( $product_id, '_nem_popularity_priority', true );
                // Empty string means "not set" - show as dash, use "default" for sorting
                $effective = $cat_priority !== '' ? $cat_priority : ( $global_priority !== '' ? $global_priority : '' );
                $sorted_products[] = array(
                    'id' => $product_id,
                    'title' => get_the_title(),
                    'cat_priority' => $cat_priority,
                    'global_priority' => $global_priority,
                    'effective_priority' => $effective,
                    'date' => get_the_date( 'Y-m-d H:i:s' ),
                );
            }
            wp_reset_postdata();
        }
        
        // Sort based on selected option
        switch ( $sort_by ) {
            case 'title':
                usort( $sorted_products, function( $a, $b ) {
                    return strcasecmp( $a['title'], $b['title'] );
                } );
                break;
            case 'title_desc':
                usort( $sorted_products, function( $a, $b ) {
                    return strcasecmp( $b['title'], $a['title'] );
                } );
                break;
            case 'date':
                usort( $sorted_products, function( $a, $b ) {
                    return strcmp( $b['date'], $a['date'] );
                } );
                break;
            case 'date_asc':
                usort( $sorted_products, function( $a, $b ) {
                    return strcmp( $a['date'], $b['date'] );
                } );
                break;
            case 'priority':
            default:
                usort( $sorted_products, function( $a, $b ) {
                    // Products with priority come first
                    $a_has_priority = $a['effective_priority'] !== '';
                    $b_has_priority = $b['effective_priority'] !== '';
                    
                    if ( $a_has_priority && ! $b_has_priority ) return -1;
                    if ( ! $a_has_priority && $b_has_priority ) return 1;
                    if ( ! $a_has_priority && ! $b_has_priority ) return strcasecmp( $a['title'], $b['title'] );
                    
                    // Both have priority - sort by priority value
                    $diff = intval( $a['effective_priority'] ) - intval( $b['effective_priority'] );
                    if ( $diff !== 0 ) return $diff;
                    return strcasecmp( $a['title'], $b['title'] );
                } );
                break;
        }
        
        echo '<form method="post">';
        wp_nonce_field( 'nem_save_ordering', 'nem_ordering_nonce' );
        echo '<input type="hidden" name="selected_cat" value="' . esc_attr( $selected_cat ) . '">';
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr>';
        echo '<th style="width:100px;">' . esc_html__( 'Priority', 'hello-elementor-child' ) . '</th>';
        echo '<th>' . esc_html__( 'Product', 'hello-elementor-child' ) . '</th>';
        echo '<th style="width:120px;">' . esc_html__( 'Global Priority', 'hello-elementor-child' ) . '</th>';
        echo '<th style="width:140px;">' . esc_html__( 'Effective Priority', 'hello-elementor-child' ) . '</th>';
        echo '</tr></thead>';
        echo '<tbody>';
        
        foreach ( $sorted_products as $product ) {
            $edit_link = get_edit_post_link( $product['id'] );
            $effective_display = $product['effective_priority'] !== '' ? $product['effective_priority'] : esc_html__( 'Default', 'hello-elementor-child' );
            echo '<tr>';
            echo '<td><input type="number" name="priorities[' . esc_attr( $product['id'] ) . ']" value="' . esc_attr( $product['cat_priority'] ) . '" min="0" step="1" style="width:80px;" placeholder="—"></td>';
            echo '<td><a href="' . esc_url( $edit_link ) . '" target="_blank">' . esc_html( $product['title'] ) . '</a></td>';
            echo '<td>' . ( $product['global_priority'] !== '' ? esc_html( $product['global_priority'] ) : '—' ) . '</td>';
            echo '<td><strong>' . esc_html( $effective_display ) . '</strong></td>';
            echo '</tr>';
        }
        
        echo '</tbody></table>';
        
        echo '<p style="margin-top: 15px;">';
        echo '<button type="submit" name="nem_save_ordering" class="button button-primary">' . esc_html__( 'Save Order', 'hello-elementor-child' ) . '</button>';
        echo '</p>';
        
        echo '<p class="description">' . esc_html__( 'Products with lower priority numbers appear first. Leave empty to use default WooCommerce sorting (by sales).', 'hello-elementor-child' ) . '</p>';
        
        echo '</form>';
    }
    
    echo '</div>';
}

/**
 * Save bulk ordering from admin page
 */
function nem_save_bulk_ordering() {
    if ( ! isset( $_POST['priorities'] ) || ! isset( $_POST['selected_cat'] ) ) {
        return;
    }
    
    $selected_cat = intval( $_POST['selected_cat'] );
    $priorities = $_POST['priorities'];
    
    foreach ( $priorities as $product_id => $priority ) {
        $product_id = intval( $product_id );
        $priority = sanitize_text_field( $priority );
        
        if ( $priority !== '' ) {
            update_post_meta( $product_id, '_nem_cat_priority_' . $selected_cat, intval( $priority ) );
        } else {
            delete_post_meta( $product_id, '_nem_cat_priority_' . $selected_cat );
        }
    }
}

/**
 * Add "Popularity Priority" column to products list
 */
function nem_add_priority_column( $columns ) {
    $new_columns = array();
    foreach ( $columns as $key => $value ) {
        $new_columns[$key] = $value;
        if ( $key === 'name' ) {
            $new_columns['nem_priority'] = __( 'Priority', 'hello-elementor-child' );
        }
    }
    return $new_columns;
}
add_filter( 'manage_edit-product_columns', 'nem_add_priority_column', 20 );

/**
 * Display priority in products list column
 */
function nem_display_priority_column( $column, $post_id ) {
    if ( $column === 'nem_priority' ) {
        $priority = get_post_meta( $post_id, '_nem_popularity_priority', true );
        echo $priority !== '' ? $priority : '—';
    }
}
add_action( 'manage_product_posts_custom_column', 'nem_display_priority_column', 10, 2 );

/**
 * Make priority column sortable
 */
function nem_sortable_priority_column( $columns ) {
    $columns['nem_priority'] = 'nem_priority';
    return $columns;
}
add_filter( 'manage_edit-product_sortable_columns', 'nem_sortable_priority_column' );

/**
 * Handle sorting by priority in admin
 */
function nem_priority_column_orderby( $query ) {
    if ( ! is_admin() || ! $query->is_main_query() ) {
        return;
    }
    
    if ( $query->get( 'orderby' ) === 'nem_priority' ) {
        $query->set( 'meta_key', '_nem_popularity_priority' );
        $query->set( 'orderby', 'meta_value_num' );
    }
}
add_action( 'pre_get_posts', 'nem_priority_column_orderby' );

