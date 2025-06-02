<?php get_header(); ?>

<main class="main-content">
    <?php while (have_posts()) : the_post(); 
        // Initialize WooCommerce product object if WooCommerce is active
        $product = class_exists('WooCommerce') ? wc_get_product(get_the_ID()) : null;
    ?>
        <article class="product-single">
            <div class="container">
                <div class="product-content">
                    <div class="product-gallery">
                        <?php if (has_post_thumbnail()) : ?>
                            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large'); ?>" alt="<?php the_title(); ?>" class="product-main-image">
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-details">
                        <h1 class="product-title"><?php the_title(); ?></h1>
                        
                        <div class="product-price">
                            <?php 
                            if (class_exists('WooCommerce') && $product) {
                                echo $product->get_price_html();
                            } else {
                                $price = get_post_meta(get_the_ID(), '_price', true);
                                echo $price ? '$' . $price : 'Contact for Price';
                            }
                            ?>
                        </div>
                        
                        <div class="product-description">
                            <?php the_content(); ?>
                        </div>
                        
                        <div class="product-actions">
                            <?php if (class_exists('WooCommerce')) : ?>
                                <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                                    <div class="quantity-wrapper">
                                        <button class="quantity-btn quantity-minus" data-action="decrease" type="button">-</button>
                                        <input type="number" class="quantity-input" value="1" min="1" max="<?php echo $product->get_max_purchase_quantity(); ?>">
                                        <button class="quantity-btn quantity-plus" data-action="increase" type="button">+</button>
                                    </div>
                                    <button class="add-to-cart-btn" 
                                            data-product-id="<?php echo get_the_ID(); ?>" 
                                            data-quantity="1"
                                            type="button">
                                        <span class="button-text">Add to Cart</span>
                                        <span class="loading-spinner"></span>
                                    </button>
                                <?php else : ?>
                                    <button class="add-to-cart-btn" disabled type="button">
                                        <?php echo $product->is_in_stock() ? 'Read More' : 'Out of Stock'; ?>
                                    </button>
                                <?php endif; ?>
                            <?php else : ?>
                                <div class="quantity-wrapper">
                                    <button class="quantity-btn quantity-minus" data-action="decrease" type="button">-</button>
                                    <input type="number" class="quantity-input" value="1" min="1">
                                    <button class="quantity-btn quantity-plus" data-action="increase" type="button">+</button>
                                </div>
                                <button class="add-to-cart-btn" 
                                        data-product-id="<?php echo get_the_ID(); ?>" 
                                        data-quantity="1"
                                        type="button">
                                    <span class="button-text">Add to Cart</span>
                                    <span class="loading-spinner"></span>
                                </button>
                            <?php endif; ?>
                            <button class="wishlist-btn" type="button">Add to Wishlist</button>
                        </div>
                    </div>
                </div>
            </div>
        </article>
    <?php endwhile; ?>
</main>

<style>
.product-single {
    padding: 120px 0 80px;
}

.product-content {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 60px;
    align-items: start;
}

.product-main-image {
    width: 100%;
    height: auto;
    border-radius: 15px;
}

.product-title {
    font-size: 2.5rem;
    color: #2d5016;
    margin-bottom: 20px;
}

.product-price {
    font-size: 2rem;
    color: #2d5016;
    font-weight: 700;
    margin-bottom: 30px;
}

.product-description {
    margin-bottom: 40px;
    line-height: 1.6;
}

.product-actions {
    display: flex;
    gap: 20px;
    align-items: center;
}

.quantity-wrapper {
    display: flex;
    align-items: center;
    border: 0px solid #ddd;
    border-radius: 4px;
    overflow: hidden;
}

.quantity-btn {
    padding: 8px 12px;
    background: #f5f5f5;
    border: none;
    cursor: pointer;
    font-size: 16px;
    transition: background-color 0.3s;
}

.quantity-btn:hover {
    background: #e5e5e5;
}

.quantity-input {
    width: 50px;
    text-align: center;
    border: none;
    padding: 8px 0;
    font-size: 16px;
}

.add-to-cart-btn {
    padding: 12px 24px;
    background: #2d5016;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: background-color 0.3s;
    position: relative;
    min-width: 150px;
}

.add-to-cart-btn:hover {
    background: #1f3710;
}

.add-to-cart-btn:disabled {
    background: #ccc;
    cursor: not-allowed;
}

.add-to-cart-btn.loading {
    pointer-events: none;
}

.add-to-cart-btn .loading-spinner {
    display: none;
    width: 20px;
    height: 20px;
    border: 2px solid #fff;
    border-top-color: transparent;
    border-radius: 50%;
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    animation: spin 0.8s linear infinite;
}

.add-to-cart-btn.loading .button-text {
    opacity: 0;
}

.add-to-cart-btn.loading .loading-spinner {
    display: block;
}

@keyframes spin {
    to {
        transform: translateY(-50%) rotate(360deg);
    }
}

.wishlist-btn {
    padding: 12px 24px;
    background: transparent;
    color: #2d5016;
    border: 2px solid #2d5016;
    border-radius: 4px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    transition: all 0.3s;
}

.wishlist-btn:hover {
    background: #2d5016;
    color: white;
}

.cart-message {
    position: fixed;
    top: 20px;
    right: 20px;
    padding: 12px 24px;
    border-radius: 4px;
    color: white;
    font-weight: 500;
    z-index: 9999;
    opacity: 0;
    transform: translateY(-20px);
    transition: all 0.3s ease;
}

.cart-message.show {
    opacity: 1;
    transform: translateY(0);
}

.cart-message.success {
    background: #4CAF50;
}

.cart-message.error {
    background: #f44336;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const quantityInput = document.querySelector('.quantity-input');
    const minusBtn = document.querySelector('.quantity-minus');
    const plusBtn = document.querySelector('.quantity-plus');
    const addToCartBtn = document.querySelector('.add-to-cart-btn');
    
    if (quantityInput && minusBtn && plusBtn) {
        minusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            if (currentValue > 1) {
                quantityInput.value = currentValue - 1;
                if (addToCartBtn) {
                    addToCartBtn.dataset.quantity = quantityInput.value;
                }
            }
        });
        
        plusBtn.addEventListener('click', function() {
            const currentValue = parseInt(quantityInput.value);
            const maxValue = parseInt(quantityInput.getAttribute('max')) || 999;
            if (currentValue < maxValue) {
                quantityInput.value = currentValue + 1;
                if (addToCartBtn) {
                    addToCartBtn.dataset.quantity = quantityInput.value;
                }
            }
        });
        
        quantityInput.addEventListener('change', function() {
            const currentValue = parseInt(this.value);
            const maxValue = parseInt(this.getAttribute('max')) || 999;
            if (currentValue < 1) {
                this.value = 1;
            } else if (currentValue > maxValue) {
                this.value = maxValue;
            }
            if (addToCartBtn) {
                addToCartBtn.dataset.quantity = this.value;
            }
        });
    }
});
</script>

<?php get_footer(); ?>
