<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php wp_title('|', true, 'right'); ?><?php bloginfo('name'); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>

<header class="site-header">
    <div class="container">
        <div class="header-content">
            <a href="<?php echo home_url(); ?>" class="logo">
                <?php 
                if (get_theme_mod('custom_logo')) {
                    the_custom_logo();
                } else {
                    echo get_bloginfo('name');
                }
                ?>
            </a>
            
            <nav class="main-nav">
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'primary',
                    'menu_class' => 'main-menu',
                    'container' => false,
                    'fallback_cb' => 'cbd_beauty_fallback_menu'
                ));
                ?>
            </nav>
            
            <div class="header-actions">
                <?php if (class_exists('WooCommerce')) : ?>
                    <div class="cart-icon-container">
                        <a href="<?php echo wc_get_cart_url(); ?>" class="cart-icon" aria-label="Shopping cart">
                            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="9" cy="21" r="1"></circle>
                                <circle cx="20" cy="21" r="1"></circle>
                                <path d="m1 1 4 4 2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                            </svg>
                            <span class="cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?></span>
                        </a>
                        
                        <!-- Mini Cart Dropdown -->
                        <div class="mini-cart-dropdown">
                            <div class="mini-cart-header">
                                <h3>Shopping Cart</h3>
                                <span class="mini-cart-count"><?php echo WC()->cart->get_cart_contents_count(); ?> items</span>
                            </div>
                            
                            <div class="mini-cart-items">
                                <?php if (WC()->cart->is_empty()) : ?>
                                    <div class="mini-cart-empty">
                                        <p>Your cart is empty</p>
                                    </div>
                                <?php else : ?>
                                    <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                                        $product = $cart_item['data'];
                                        $quantity = $cart_item['quantity'];
                                    ?>
                                        <div class="mini-cart-item">
                                            <div class="mini-cart-item-image">
                                                <?php echo $product->get_image('thumbnail'); ?>
                                            </div>
                                            <div class="mini-cart-item-details">
                                                <h4><?php echo $product->get_name(); ?></h4>
                                                <span class="mini-cart-item-price"><?php echo WC()->cart->get_product_price($product); ?> × <?php echo $quantity; ?></span>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                    
                                    <div class="mini-cart-footer">
                                        <div class="mini-cart-total">
                                            <strong>Total: <?php echo WC()->cart->get_cart_total(); ?></strong>
                                        </div>
                                        <div class="mini-cart-actions">
                                            <a href="<?php echo wc_get_cart_url(); ?>" class="view-cart-btn">View Cart</a>
                                            <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-btn">Checkout</a>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>
