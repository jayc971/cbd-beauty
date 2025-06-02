<?php
/**
 * Template Name: Cart Page
 */

get_header(); ?>

<main class="main-content">
    <div class="container">
        <div class="cart-page">
            <h1 class="page-title">Shopping Cart</h1>
            
            <?php if (class_exists('WooCommerce')) : ?>
                <?php if (WC()->cart->is_empty()) : ?>
                    <div class="empty-cart">
                        <div class="empty-cart-icon">🛒</div>
                        <h2>Your cart is empty</h2>
                        <p>Looks like you haven't added any products to your cart yet.</p>
                        <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="continue-shopping-btn">Continue Shopping</a>
                    </div>
                <?php else : ?>
                    <div class="cart-content">
                        <div class="cart-items">
                            <?php foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) :
                                $product = $cart_item['data'];
                                $product_id = $cart_item['product_id'];
                                $quantity = $cart_item['quantity'];
                                $price = WC()->cart->get_product_price($product);
                                $subtotal = WC()->cart->get_product_subtotal($product, $quantity);
                            ?>
                                <div class="cart-item" data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                    <div class="cart-item-image">
                                        <?php echo $product->get_image('thumbnail'); ?>
                                    </div>
                                    
                                    <div class="cart-item-details">
                                        <h3 class="cart-item-title">
                                            <a href="<?php echo get_permalink($product_id); ?>">
                                                <?php echo $product->get_name(); ?>
                                            </a>
                                        </h3>
                                        
                                        <?php if ($product->get_sku()) : ?>
                                            <div class="cart-item-sku">SKU: <?php echo $product->get_sku(); ?></div>
                                        <?php endif; ?>
                                        
                                        <div class="cart-item-price">
                                            <?php echo $price; ?>
                                        </div>
                                    </div>
                                    
                                    <div class="cart-item-quantity">
                                        <button class="quantity-btn quantity-minus" 
                                                data-cart-key="<?php echo esc_attr($cart_item_key); ?>" 
                                                type="button">-</button>
                                        <input type="number" 
                                               class="quantity-input" 
                                               value="<?php echo $quantity; ?>" 
                                               min="1" 
                                               max="<?php echo $product->get_max_purchase_quantity(); ?>" 
                                               data-cart-key="<?php echo esc_attr($cart_item_key); ?>">
                                        <button class="quantity-btn quantity-plus" 
                                                data-cart-key="<?php echo esc_attr($cart_item_key); ?>" 
                                                type="button">+</button>
                                    </div>
                                    
                                    <div class="cart-item-subtotal">
                                        <?php echo $subtotal; ?>
                                    </div>
                                    
                                    <button class="remove-item" 
                                            data-cart-key="<?php echo esc_attr($cart_item_key); ?>" 
                                            aria-label="Remove item"
                                            type="button">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="18" y1="6" x2="6" y2="18"></line>
                                            <line x1="6" y1="6" x2="18" y2="18"></line>
                                        </svg>
                                    </button>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="cart-summary">
                            <h2>Order Summary</h2>
                            
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
                                <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="continue-shopping-btn">Continue Shopping</a>
                                <a href="<?php echo wc_get_checkout_url(); ?>" class="checkout-btn">Proceed to Checkout</a>
                            </div>
                            
                            <?php if (wc_coupons_enabled()) : ?>
                                <div class="coupon-section">
                                    <h3>Have a coupon?</h3>
                                    <form class="coupon-form" method="post">
                                        <input type="text" name="coupon_code" class="coupon-input" placeholder="Enter coupon code">
                                        <button type="submit" class="apply-coupon-btn">Apply Coupon</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="woocommerce-missing">
                    <p>This page requires WooCommerce to be installed and activated.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</main>

<style>
.cart-page {
    padding: 60px 0;
}

.page-title {
    font-size: 2.5rem;
    color: #2d5016;
    margin-bottom: 40px;
    text-align: center;
}

.empty-cart {
    text-align: center;
    padding: 60px 20px;
    background: #f9f9f9;
    border-radius: 8px;
}

.empty-cart-icon {
    font-size: 4rem;
    margin-bottom: 20px;
}

.empty-cart h2 {
    font-size: 1.8rem;
    color: #2d5016;
    margin-bottom: 15px;
}

.empty-cart p {
    color: #666;
    margin-bottom: 30px;
}

.cart-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
}

.cart-items {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 20px;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 2fr 1fr 1fr auto;
    gap: 20px;
    align-items: center;
    padding: 20px 0;
    border-bottom: 1px solid #eee;
}

.cart-item:last-child {
    border-bottom: none;
}

.cart-item-image img {
    width: 100%;
    height: auto;
    border-radius: 4px;
}

.cart-item-title {
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.cart-item-title a {
    color: #2d5016;
    text-decoration: none;
}

.cart-item-sku {
    font-size: 0.9rem;
    color: #666;
    margin-bottom: 5px;
}

.cart-item-price {
    font-weight: 600;
    color: #2d5016;
}

.cart-item-quantity {
    display: flex;
    align-items: center;
    gap: 8px;
    background: #f8f9f4;
    border-radius: 8px;
    padding: 8px;
    border: 1px solid #e0e0e0;
}

.quantity-btn {
    background: #2d5016;
    color: white;
    border: none;
    width: 32px;
    height: 32px;
    border-radius: 6px;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    transition: all 0.3s ease;
    font-size: 16px;
}

.quantity-btn:hover {
    background: #1a3009;
    transform: scale(1.05);
}

.quantity-input {
    width: 60px;
    text-align: center;
    border: none;
    background: transparent;
    font-size: 16px;
    font-weight: 600;
    color: #2d5016;
    padding: 4px;
}

.cart-item-subtotal {
    font-weight: 600;
    color: #2d5016;
}

.remove-item {
    background: none;
    border: none;
    color: #999;
    cursor: pointer;
    padding: 5px;
    transition: color 0.3s;
}

.remove-item:hover {
    color: #ff4444;
}

.cart-summary {
    background: #fff;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 30px;
    position: sticky;
    top: 20px;
}

.cart-summary h2 {
    font-size: 1.5rem;
    color: #2d5016;
    margin-bottom: 20px;
}

.cart-totals {
    margin-bottom: 30px;
}

.cart-totals > div {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #eee;
}

.cart-total {
    font-size: 1.2rem;
    font-weight: 600;
    color: #2d5016;
    border-bottom: none !important;
}

.cart-actions {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.continue-shopping-btn,
.checkout-btn {
    padding: 15px;
    border-radius: 4px;
    text-align: center;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.3s;
}

.continue-shopping-btn {
    background: #f5f5f5;
    color: #2d5016;
    border: 1px solid #ddd;
}

.continue-shopping-btn:hover {
    background: #e5e5e5;
}

.checkout-btn {
    background: #2d5016;
    color: white;
    border: none;
}

.checkout-btn:hover {
    background: #1f3710;
}

.coupon-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.coupon-section h3 {
    font-size: 1.1rem;
    color: #2d5016;
    margin-bottom: 15px;
}

.coupon-form {
    display: flex;
    gap: 10px;
}

.coupon-input {
    flex: 1;
    padding: 10px;
    border: 1px solid #ddd;
    border-radius: 4px;
}

.apply-coupon-btn {
    padding: 10px 20px;
    background: #f5f5f5;
    border: 1px solid #ddd;
    border-radius: 4px;
    cursor: pointer;
    transition: all 0.3s;
}

.apply-coupon-btn:hover {
    background: #e5e5e5;
}

@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 15px;
    }
    
    .cart-item-quantity,
    .cart-item-subtotal {
        grid-column: 2;
    }
    
    .remove-item {
        position: absolute;
        top: 20px;
        right: 20px;
    }
    
    .cart-summary {
        position: static;
    }
}
</style>

<?php get_footer(); ?>
