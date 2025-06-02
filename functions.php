<?php
// Theme Setup
function cbd_beauty_setup() {
    // Add theme support
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('custom-logo');
    add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption'));
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
    
    // Register navigation menus
    register_nav_menus(array(
        'primary' => 'Primary Menu',
        'footer' => 'Footer Menu'
    ));
    
    // Add image sizes
    add_image_size('product-thumb', 300, 300, true);
    add_image_size('hero-image', 1200, 600, true);
    add_image_size('ingredient-thumb', 150, 150, true);
}
add_action('after_setup_theme', 'cbd_beauty_setup');

// Enqueue styles and scripts
function cbd_beauty_scripts() {
    wp_enqueue_style('cbd-beauty-style', get_stylesheet_uri(), array(), '1.0.0');
    
    // Enqueue jQuery
    wp_enqueue_script('jquery');
    
    // Enqueue WooCommerce scripts if WooCommerce is active
    if (class_exists('WooCommerce')) {
        wp_enqueue_script('wc-add-to-cart');
        wp_enqueue_script('wc-cart-fragments');
        wp_enqueue_script('wc-single-product');
    }
    
    wp_enqueue_script('cbd-beauty-carousel', get_template_directory_uri() . '/js/ingredient-carousel.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('cbd-beauty-cart', get_template_directory_uri() . '/js/cart-system.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('cbd-beauty-shop', get_template_directory_uri() . '/js/shop.js', array('jquery'), '1.0.0', true);
    wp_enqueue_script('cbd-beauty-script', get_template_directory_uri() . '/js/main.js', array('jquery'), '1.0.0', true);
    
    // Localize script for AJAX
    wp_localize_script('cbd-beauty-cart', 'cbd_beauty_ajax', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('cbd_beauty_nonce'),
    ));
    
    // Add WooCommerce parameters if WooCommerce is active
    if (class_exists('WooCommerce')) {
        wp_localize_script('cbd-beauty-cart', 'wc_add_to_cart_params', array(
            'ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
            'wc_ajax_url' => WC_AJAX::get_endpoint('%%endpoint%%'),
            'i18n_view_cart' => esc_attr__('View cart', 'woocommerce'),
            'cart_url' => wc_get_cart_url(),
            'is_cart' => is_cart(),
            'cart_redirect_after_add' => get_option('woocommerce_cart_redirect_after_add')
        ));
    }
}
add_action('wp_enqueue_scripts', 'cbd_beauty_scripts');

// Add cart fragments handler
function cbd_beauty_cart_fragments($fragments) {
    if (!class_exists('WooCommerce')) {
        return $fragments;
    }
    
    // Update mini cart
    ob_start();
    woocommerce_mini_cart();
    $mini_cart = ob_get_clean();
    
    $fragments['div.widget_shopping_cart_content'] = '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>';
    
    // Update cart count
    $cart_count = WC()->cart->get_cart_contents_count();
    $fragments['.cart-count'] = '<span class="cart-count">' . $cart_count . '</span>';
    $fragments['.mini-cart-count'] = '<span class="mini-cart-count">' . $cart_count . ' items</span>';
    
    // Update cart total
    $cart_total = WC()->cart->get_cart_total();
    $fragments['.cart-total-amount'] = '<span class="cart-total-amount">' . $cart_total . '</span>';
    
    // Update cart totals in cart page
    ob_start();
    ?>
    <div class="cart-totals">
        <div class="cart-subtotal">
            <span>Subtotal:</span>
            <span><?php echo WC()->cart->get_cart_subtotal(); ?></span>
        </div>
        
        <?php if (WC()->cart->get_cart_tax()) : ?>
            <div class="cart-tax">
                <span>Tax:</span>
                <span><?php echo WC()->cart->get_cart_tax(); ?></span>
            </div>
        <?php endif; ?>
        
        <div class="cart-total">
            <span>Total:</span>
            <span><?php echo WC()->cart->get_cart_total(); ?></span>
        </div>
    </div>
    <?php
    $fragments['.cart-totals'] = ob_get_clean();

    // Update cart count from fragments if available
    if (isset($fragments['.cart-count'])) {
        $count = WC()->cart->get_cart_contents_count();
        $fragments['.cart-count'] = '<span class="cart-count">' . $count . '</span>';
    }

    // Update quantity displays
    foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
        $quantity = $cart_item['quantity'];
        $fragments['.quantity-display[data-cart-key="' . $cart_item_key . '"]'] =
            '<span class="quantity-display" data-cart-key="' . $cart_item_key . '">' . $quantity . '</span>';
    }
    
    return $fragments;
}
add_filter('woocommerce_add_to_cart_fragments', 'cbd_beauty_cart_fragments');

// Add AJAX handlers for cart operations
function cbd_beauty_add_to_cart_handler() {
    check_ajax_referer('cbd_beauty_nonce', 'nonce');
    
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;
    
    if (!$product_id) {
        wp_send_json_error(['message' => 'Invalid product ID']);
        return;
    }
    
    try {
        if (class_exists('WooCommerce')) {
            // Check if product exists and is purchasable
            $product = wc_get_product($product_id);
            if (!$product || !$product->is_purchasable()) {
                throw new Exception('Product is not available for purchase');
            }
            
            // Check if product is in stock
            if (!$product->is_in_stock()) {
                throw new Exception('Product is out of stock');
            }
            
            // Add to cart
            $cart_item_key = WC()->cart->add_to_cart($product_id, $quantity);
            
            if ($cart_item_key) {
                // Get updated cart data
                $cart_count = WC()->cart->get_cart_contents_count();
                $cart_total = WC()->cart->get_cart_total();
                
                // Get mini cart HTML
                ob_start();
                woocommerce_mini_cart();
                $mini_cart = ob_get_clean();
                
                $data = [
                    'message' => 'Product added to cart successfully!',
                    'cart_count' => $cart_count,
                    'cart_total' => $cart_total,
                    'fragments' => apply_filters('woocommerce_add_to_cart_fragments', [
                        'div.widget_shopping_cart_content' => '<div class="widget_shopping_cart_content">' . $mini_cart . '</div>',
                        '.cart-count' => '<span class="cart-count">' . $cart_count . '</span>',
                        '.mini-cart-count' => '<span class="mini-cart-count">' . $cart_count . ' items</span>',
                        '.cart-total-amount' => '<span class="cart-total-amount">' . $cart_total . '</span>'
                    ]),
                    'cart_hash' => WC()->cart->get_cart_hash()
                ];
                
                wp_send_json_success($data);
            } else {
                throw new Exception('Failed to add product to cart');
            }
        } else {
            throw new Exception('WooCommerce is not active');
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wp_ajax_cbd_beauty_add_to_cart', 'cbd_beauty_add_to_cart_handler');
add_action('wp_ajax_nopriv_cbd_beauty_add_to_cart', 'cbd_beauty_add_to_cart_handler');

// WooCommerce AJAX update cart handler
function cbd_beauty_wc_update_cart() {
    if (!class_exists('WooCommerce')) {
        wp_die();
    }
    
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 0;
    
    if (!$cart_item_key || $quantity < 1) {
        wp_send_json_error(['message' => 'Invalid cart item or quantity']);
        return;
    }
    
    try {
        // Check if cart item exists
        if (!WC()->cart->get_cart_item($cart_item_key)) {
            throw new Exception('Cart item not found');
        }
        
        // Update quantity
        WC()->cart->set_quantity($cart_item_key, $quantity);
        
        // Get updated fragments
        $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
        
        $data = [
            'message' => 'Cart updated successfully',
            'fragments' => $fragments,
            'cart_hash' => WC()->cart->get_cart_hash()
        ];
        
        wp_send_json_success($data);
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wc_ajax_update_cart', 'cbd_beauty_wc_update_cart');
add_action('wp_ajax_nopriv_update_cart', 'cbd_beauty_wc_update_cart');

// WooCommerce AJAX remove from cart handler
function cbd_beauty_wc_remove_from_cart() {
    if (!class_exists('WooCommerce')) {
        wp_die();
    }
    
    $cart_item_key = isset($_POST['cart_item_key']) ? sanitize_text_field($_POST['cart_item_key']) : '';
    
    if (!$cart_item_key) {
        wp_send_json_error(['message' => 'Invalid cart item key']);
        return;
    }
    
    try {
        // Check if the cart item exists
        if (!WC()->cart->get_cart_item($cart_item_key)) {
            throw new Exception('Cart item not found');
        }
        
        // Remove the item
        $removed = WC()->cart->remove_cart_item($cart_item_key);
        
        if ($removed) {
            // Get updated cart data
            $cart_count = WC()->cart->get_cart_contents_count();
            $cart_total = WC()->cart->get_cart_total();
            $fragments = apply_filters('woocommerce_add_to_cart_fragments', []);
            
            $data = [
                'message' => 'Item removed from cart',
                'cart_count' => $cart_count,
                'cart_total' => $cart_total,
                'fragments' => $fragments,
                'cart_hash' => WC()->cart->get_cart_hash()
            ];
            
            wp_send_json_success($data);
        } else {
            throw new Exception('Failed to remove item from cart');
        }
    } catch (Exception $e) {
        wp_send_json_error(['message' => $e->getMessage()]);
    }
}
add_action('wc_ajax_remove_from_cart', 'cbd_beauty_wc_remove_from_cart');
add_action('wp_ajax_nopriv_remove_from_cart', 'cbd_beauty_wc_remove_from_cart');

// Add cart count endpoint
function cbd_beauty_get_cart_count() {
    if (!class_exists('WooCommerce')) {
        wp_send_json_error(['message' => 'WooCommerce not active']);
        return;
    }
    
    $count = WC()->cart->get_cart_contents_count();
    wp_send_json_success(['count' => $count]);
}
add_action('wp_ajax_cbd_beauty_get_cart_count', 'cbd_beauty_get_cart_count');
add_action('wp_ajax_nopriv_cbd_beauty_get_cart_count', 'cbd_beauty_get_cart_count');

// Remove the old cart update and remove handlers and replace with these
remove_action('wp_ajax_cbd_beauty_update_cart', 'cbd_beauty_update_cart');
remove_action('wp_ajax_nopriv_cbd_beauty_update_cart', 'cbd_beauty_update_cart');
remove_action('wp_ajax_cbd_beauty_remove_from_cart', 'cbd_beauty_remove_from_cart');
remove_action('wp_ajax_nopriv_cbd_beauty_remove_from_cart', 'cbd_beauty_remove_from_cart');

// Add cart icon to header
add_action('wp_head', 'cbd_beauty_cart_icon_script');
function cbd_beauty_cart_icon_script() {
    if (class_exists('WooCommerce')) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                // Update cart count on page load
                $(document.body).on('added_to_cart removed_from_cart cart_item_updated', function() {
                    $('.cart-count').text(<?php echo WC()->cart->get_cart_contents_count(); ?>);
                    $('.mini-cart-count').text(<?php echo WC()->cart->get_cart_contents_count(); ?> + ' items');
                    $('.cart-total-amount').text('<?php echo WC()->cart->get_cart_total(); ?>');
                });

                // Handle AJAX errors globally
                $(document).ajaxError(function(event, jqXHR, settings, error) {
                    console.error('AJAX Error:', error);
                    // Clear loading states
                    $('.add-to-cart-btn.loading').removeClass('loading').prop('disabled', false);
                    $('.add-to-cart-btn .button-text').text('Add to Cart');
                });
            });
        </script>
        <?php
    }
}

// Register ACF Fields for Product Ingredients
if (function_exists('acf_add_local_field_group')) {
    acf_add_local_field_group(array(
        'key' => 'group_product_ingredients',
        'title' => 'Product Ingredients',
        'fields' => array(
            array(
                'key' => 'field_ingredients',
                'label' => 'Ingredients',
                'name' => 'ingredients',
                'type' => 'repeater',
                'instructions' => 'Add ingredients for this product',
                'required' => 0,
                'min' => 0,
                'max' => 0,
                'layout' => 'block',
                'button_label' => 'Add Ingredient',
                'sub_fields' => array(
                    array(
                        'key' => 'field_ingredient_image',
                        'label' => 'Ingredient Image',
                        'name' => 'ingredient_image',
                        'type' => 'image',
                        'required' => 1,
                        'return_format' => 'array',
                        'preview_size' => 'medium',
                        'library' => 'all',
                    ),
                    array(
                        'key' => 'field_ingredient_title',
                        'label' => 'Ingredient Title',
                        'name' => 'ingredient_title',
                        'type' => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_ingredient_description',
                        'label' => 'Short Description',
                        'name' => 'ingredient_description',
                        'type' => 'text',
                        'required' => 1,
                    ),
                    array(
                        'key' => 'field_ingredient_long_description',
                        'label' => 'Long Description',
                        'name' => 'ingredient_long_description',
                        'type' => 'textarea',
                        'required' => 0,
                    ),
                ),
            ),
        ),
        'location' => array(
            array(
                array(
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'product',
                ),
            ),
        ),
    ));
}

// Product Ingredients Carousel Shortcode
function product_ingredients_scroll_shortcode($atts) {
    $atts = shortcode_atts(array(
        'id' => get_the_ID(),
    ), $atts, 'product_ingredients_scroll');

    $product_id = intval($atts['id']);
    $ingredients = get_field('ingredients', $product_id);

    if (!$ingredients || !is_array($ingredients)) {
        return '';
    }

    // Enqueue required scripts and styles
    wp_enqueue_style('ingredient-carousel-style', get_template_directory_uri() . '/assets/css/ingredient-carousel.css');
    wp_enqueue_script('ingredient-carousel-script', get_template_directory_uri() . '/ingredient-carousel.js', array('jquery'), '1.0', true);

    // Prepare data for JavaScript
    $ingredients_data = array_map(function($ingredient) {
        return array(
            'ingredient_image' => $ingredient['ingredient_image'],
            'ingredient_title' => $ingredient['ingredient_title'],
            'ingredient_description' => $ingredient['ingredient_description'],
            'ingredient_long_description' => $ingredient['ingredient_long_description'],
        );
    }, $ingredients);

    // Output the carousel HTML
    ob_start();
    ?>
    <div class="ingredients-carousel-container" data-product-id="<?php echo esc_attr($product_id); ?>">
        <div class="carousel-track">
            <?php foreach ($ingredients as $ingredient): ?>
                <div class="ingredient-card" tabindex="0" role="button" aria-label="<?php echo esc_attr($ingredient['ingredient_title']); ?>">
                    <div class="ingredient-image">
                        <?php 
                        $image = $ingredient['ingredient_image'];
                        if ($image): ?>
                            <img src="<?php echo esc_url($image['sizes']['medium']); ?>"
                                 alt="<?php echo esc_attr($ingredient['ingredient_title']); ?>"
                                 loading="lazy"
                                 width="<?php echo esc_attr($image['sizes']['medium-width']); ?>"
                                 height="<?php echo esc_attr($image['sizes']['medium-height']); ?>">
                        <?php endif; ?>
                    </div>
                    <h3 class="ingredient-title"><?php echo esc_html($ingredient['ingredient_title']); ?></h3>
                    <p class="ingredient-description"><?php echo esc_html($ingredient['ingredient_description']); ?></p>
                    <div class="ingredient-actions">
                        <button class="add-to-cart-btn" 
                                data-product-id="<?php echo esc_attr($product_id); ?>"
                                aria-label="Add <?php echo esc_attr($ingredient['ingredient_title']); ?> to cart">
                            Add to Cart
                        </button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <button class="carousel-prev" aria-label="Previous ingredient">←</button>
        <button class="carousel-next" aria-label="Next ingredient">→</button>
    </div>

    <!-- Modal Template -->
    <div id="ingredient-modal" class="ingredient-modal" aria-hidden="true" role="dialog">
        <div class="modal-overlay"></div>
        <div class="modal-content">
            <button class="modal-close" aria-label="Close modal">×</button>
            <div class="modal-body">
                <div class="modal-image">
                    <img id="modal-ingredient-image" src="/placeholder.svg" alt="">
                </div>
                <div class="modal-info">
                    <h2 id="modal-ingredient-title"></h2>
                    <p id="modal-ingredient-description"></p>
                    <div class="modal-actions">
                        <button class="add-to-cart-btn modal-add-to-cart" 
                                data-product-id="<?php echo esc_attr($product_id); ?>"
                                aria-label="Add to cart">
                            Add to Cart
                        </button>
                    </div>
                </div>
            </div>
            <div class="modal-navigation">
                <button class="modal-prev" aria-label="Previous ingredient">←</button>
                <button class="modal-next" aria-label="Next ingredient">→</button>
            </div>
        </div>
    </div>

    <script type="application/json" id="ingredients-data-<?php echo esc_attr($product_id); ?>">
        <?php echo json_encode($ingredients_data); ?>
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('product_ingredients_scroll', 'product_ingredients_scroll_shortcode');

// Cart shortcode
function cbd_beauty_cart_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_title' => 'true',
    ), $atts);
    
    ob_start();
    ?>
    <div class="cbd-beauty-cart-section">
        <?php if ($atts['show_title'] === 'true') : ?>
            <h2 class="cart-section-title">Shopping Cart</h2>
        <?php endif; ?>
        
        <div class="cart-container">
            <?php if (class_exists('WooCommerce')) : ?>
                <?php if (WC()->cart->is_empty()) : ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <h3>Your cart is empty</h3>
                        <p>Add some products to get started!</p>
                        <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="cta-button">Continue Shopping</a>
                    </div>
                <?php else : ?>
                    <div class="cart-items">
                        <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                            $product = $cart_item['data'];
                            $product_id = $cart_item['product_id'];
                            $quantity = $cart_item['quantity'];
                        ?>
                            <div class="cart-item" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                <div class="cart-item-image">
                                    <?php echo $product->get_image('thumbnail'); ?>
                                </div>
                                <div class="cart-item-details">
                                    <h4 class="cart-item-title"><?php echo $product->get_name(); ?></h4>
                                    <div class="cart-item-price"><?php echo WC()->cart->get_product_price($product); ?></div>
                                </div>
                                <div class="cart-item-quantity">
                                    <button class="quantity-btn quantity-minus" 
                                            data-cart-key="<?php echo esc_attr($cart_item_key); ?>" 
                                            type="button">-</button>
                                    <span class="quantity-display" 
                                          data-cart-key="<?php echo esc_attr($cart_item_key); ?>"><?php echo $quantity; ?></span>
                                    <button class="quantity-btn quantity-plus" 
                                            data-cart-key="<?php echo esc_attr($cart_item_key); ?>" 
                                            type="button">+</button>
                                </div>
                                <div class="cart-item-total">
                                    <?php echo WC()->cart->get_product_subtotal($product, $quantity); ?>
                                </div>
                                <button class="remove-item" data-cart-key="<?php echo esc_attr($cart_item_key); ?>" aria-label="Remove item">
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="18" y1="6" x2="6" y2="18"></line>
                                        <line x1="6" y1="6" x2="18" y2="18"></line>
                                    </svg>
                                </button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="cart-summary">
                        <div class="cart-totals">
                            <div class="cart-subtotal">
                                <span>Subtotal:</span>
                                <span><?php echo WC()->cart->get_cart_subtotal(); ?></span>
                            </div>
                            <?php if (WC()->cart->get_cart_tax()) : ?>
                                <div class="cart-tax">
                                    <span>Tax:</span>
                                    <span><?php echo WC()->cart->get_cart_tax(); ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="cart-total">
                                <span>Total:</span>
                                <span><?php echo WC()->cart->get_cart_total(); ?></span>
                            </div>
                        </div>
                        
                        <div class="cart-actions">
                            <a href="<?php echo wc_get_cart_url(); ?>" class="view-cart-btn">View Cart</a>
                            <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-btn">Checkout</a>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="fallback-cart">
                    <p>Cart functionality requires WooCommerce to be installed and activated.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
    <?php
    
    return ob_get_clean();
}
add_shortcode('cbd_beauty_cart', 'cbd_beauty_cart_shortcode');

// Customizer Settings
function cbd_beauty_customize_register($wp_customize) {
    // Hero Section
    $wp_customize->add_section('hero_section', array(
        'title' => 'Hero Section',
        'priority' => 30,
    ));
    
    $wp_customize->add_setting('hero_title', array(
        'default' => 'DISCOVER CBD BEAUTY CERTIFIED',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('hero_title', array(
        'label' => 'Hero Title',
        'section' => 'hero_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('hero_subtitle', array(
        'default' => 'Premium CBD-infused beauty products for natural wellness and radiant skin',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('hero_subtitle', array(
        'label' => 'Hero Subtitle',
        'section' => 'hero_section',
        'type' => 'textarea',
    ));
    
    $wp_customize->add_setting('hero_button_text', array(
        'default' => 'Shop Now',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('hero_button_text', array(
        'label' => 'Hero Button Text',
        'section' => 'hero_section',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('hero_button_url', array(
        'default' => '/shop',
        'sanitize_callback' => 'esc_url_raw',
    ));
    
    $wp_customize->add_control('hero_button_url', array(
        'label' => 'Hero Button URL',
        'section' => 'hero_section',
        'type' => 'url',
    ));
    
    // Contact Information
    $wp_customize->add_section('contact_info', array(
        'title' => 'Contact Information',
        'priority' => 40,
    ));
    
    $wp_customize->add_setting('contact_email', array(
        'default' => 'info@cbdbeauty.com',
        'sanitize_callback' => 'sanitize_email',
    ));
    
    $wp_customize->add_control('contact_email', array(
        'label' => 'Email Address',
        'section' => 'contact_info',
        'type' => 'email',
    ));
    
    $wp_customize->add_setting('contact_phone', array(
        'default' => '(555) 123-4567',
        'sanitize_callback' => 'sanitize_text_field',
    ));
    
    $wp_customize->add_control('contact_phone', array(
        'label' => 'Phone Number',
        'section' => 'contact_info',
        'type' => 'text',
    ));
    
    $wp_customize->add_setting('contact_address', array(
        'default' => '123 Beauty St, Wellness City, WC 12345',
        'sanitize_callback' => 'sanitize_textarea_field',
    ));
    
    $wp_customize->add_control('contact_address', array(
        'label' => 'Address',
        'section' => 'contact_info',
        'type' => 'textarea',
    ));
}
add_action('customize_register', 'cbd_beauty_customize_register');

// Fallback menu functions
function cbd_beauty_fallback_menu() {
    echo '<ul>';
    echo '<li><a href="' . home_url() . '">Home</a></li>';
    echo '<li><a href="' . home_url() . '/shop">Shop</a></li>';
    echo '<li><a href="' . home_url() . '/about">About</a></li>';
    echo '<li><a href="' . home_url() . '/contact">Contact</a></li>';
    echo '</ul>';
}

function cbd_beauty_footer_fallback_menu() {
    echo '<ul>';
    echo '<li><a href="' . home_url() . '">Home</a></li>';
    echo '<li><a href="' . home_url() . '/shop">Shop</a></li>';
    echo '<li><a href="' . home_url() . '/about">About</a></li>';
    echo '<li><a href="' . home_url() . '/contact">Contact</a></li>';
    echo '<li><a href="' . home_url() . '/privacy-policy">Privacy Policy</a></li>';
    echo '</ul>';
}

// Custom Post Types
function cbd_beauty_custom_post_types() {
    // Testimonials Post Type
    register_post_type('testimonial', array(
        'labels' => array(
            'name' => 'Testimonials',
            'singular_name' => 'Testimonial',
            'add_new' => 'Add New Testimonial',
            'add_new_item' => 'Add New Testimonial',
            'edit_item' => 'Edit Testimonial',
            'new_item' => 'New Testimonial',
            'view_item' => 'View Testimonial',
            'search_items' => 'Search Testimonials',
            'not_found' => 'No testimonials found',
            'not_found_in_trash' => 'No testimonials found in trash'
        ),
        'public' => true,
        'supports' => array('title', 'editor'),
        'menu_icon' => 'dashicons-format-quote',
        'show_in_rest' => true
    ));

    // Add custom meta box for testimonial role
    function cbd_beauty_add_testimonial_meta_boxes() {
        add_meta_box(
            'testimonial_role_meta_box',
            'Testimonial Details',
            'cbd_beauty_testimonial_role_callback',
            'testimonial',
            'normal',
            'default'
        );
    }
    add_action('add_meta_boxes', 'cbd_beauty_add_testimonial_meta_boxes');

    // Callback function to display the meta box
    function cbd_beauty_testimonial_role_callback($post) {
        wp_nonce_field(basename(__FILE__), 'testimonial_role_nonce');
        $testimonial_role = get_post_meta($post->ID, 'testimonial_role', true);
        ?>
        <p>
            <label for="testimonial_role">Role or Title:</label>
            <input type="text" name="testimonial_role" id="testimonial_role" value="<?php echo esc_attr($testimonial_role); ?>" style="width: 100%;" />
            <span class="description">Enter the person's role, title, or location (e.g., "Skincare Enthusiast", "New York, NY")</span>
        </p>
        <?php
    }

    // Save the meta box data
    function cbd_beauty_save_testimonial_meta($post_id) {
        // Check if nonce is set
        if (!isset($_POST['testimonial_role_nonce']) || !wp_verify_nonce($_POST['testimonial_role_nonce'], basename(__FILE__))) {
            return $post_id;
        }

        // Check if autosave
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return $post_id;
        }

        // Check permissions
        if ('testimonial' === $_POST['post_type']) {
            if (!current_user_can('edit_page', $post_id)) {
                return $post_id;
            }
        } elseif (!current_user_can('edit_post', $post_id)) {
            return $post_id;
        }

        // Save the role
        if (isset($_POST['testimonial_role'])) {
            update_post_meta($post_id, 'testimonial_role', sanitize_text_field($_POST['testimonial_role']));
        }
    }
    add_action('save_post', 'cbd_beauty_save_testimonial_meta');
}
add_action('init', 'cbd_beauty_custom_post_types');

// Widget Areas
function cbd_beauty_widgets_init() {
    register_sidebar(array(
        'name' => 'Sidebar',
        'id' => 'sidebar-1',
        'description' => 'Add widgets here to appear in your sidebar.',
        'before_widget' => '<section id="%1$s" class="widget %2$s">',
        'after_widget' => '</section>',
        'before_title' => '<h3 class="widget-title">',
        'after_title' => '</h3>',
    ));
}
add_action('widgets_init', 'cbd_beauty_widgets_init');

// WooCommerce customizations
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
add_action('woocommerce_single_product_summary', 'cbd_beauty_add_ingredients_carousel', 25);

function cbd_beauty_add_ingredients_carousel() {
    global $product;
    $ingredients = get_field('ingredients', $product->get_id());
    
    if ($ingredients && !empty($ingredients)) {
        echo '<div class="product-ingredients-section">';
        echo '<h3>Key Ingredients</h3>';
        echo do_shortcode('[product_ingredients_scroll id="' . $product->get_id() . '"]');
        echo '</div>';
    }
}

// Start session for non-WooCommerce cart
add_action('init', 'cbd_beauty_start_session');
function cbd_beauty_start_session() {
    if (!session_id()) {
        session_start();
    }
}

// Enqueue scripts and styles
function cbd_beauty_enqueue_scripts() {
    // Enqueue product carousel script
    wp_enqueue_script(
        'product-carousel',
        get_template_directory_uri() . '/js/product-carousel.js',
        array(),
        '1.0.0',
        true
    );
}
add_action('wp_enqueue_scripts', 'cbd_beauty_enqueue_scripts');
?>
