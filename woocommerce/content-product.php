<?php
/**
 * The template for displaying product content in the shop loop
 */

defined('ABSPATH') || exit;

global $product;

// Ensure visibility
if (empty($product) || !$product->is_visible()) {
    return;
}
?>
<div <?php wc_product_class('shop-product-item', $product); ?>>
    <div class="product-card">
        <div class="product-image-wrapper">
            <a href="<?php the_permalink(); ?>" class="product-link">
                <?php
                /**
                 * Hook: woocommerce_before_shop_loop_item_title.
                 *
                 * @hooked woocommerce_show_product_loop_sale_flash - 10
                 * @hooked woocommerce_template_loop_product_thumbnail - 10
                 */
                do_action('woocommerce_before_shop_loop_item_title');
                ?>
            </a>
            
            <?php if ($product->is_on_sale()) : ?>
                <span class="sale-badge">Sale!</span>
            <?php endif; ?>
            
            <?php if (!$product->is_in_stock()) : ?>
                <span class="out-of-stock-badge">Out of Stock</span>
            <?php endif; ?>
        </div>

        <div class="product-info">
            <div class="product-category">
                <?php
                $categories = get_the_terms($product->get_id(), 'product_cat');
                if ($categories && !is_wp_error($categories)) {
                    echo esc_html($categories[0]->name);
                }
                ?>
            </div>
            
            <h3 class="product-title">
                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
            </h3>
            
            <div class="product-rating">
                <?php if ($rating_html = wc_get_rating_html($product->get_average_rating())) : ?>
                    <?php echo $rating_html; ?>
                <?php else : ?>
                    <div class="star-rating">
                        <span style="width:100%">★★★★★</span>
                    </div>
                <?php endif; ?>
            </div>
            
            <div class="product-price">
                <?php
                /**
                 * Hook: woocommerce_after_shop_loop_item_title.
                 *
                 * @hooked woocommerce_template_loop_rating - 5
                 * @hooked woocommerce_template_loop_price - 10
                 */
                do_action('woocommerce_after_shop_loop_item_title');
                ?>
            </div>
            
            <div class="product-excerpt">
                <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
            </div>

            <div class="product-actions">
                <?php if ($product->is_purchasable() && $product->is_in_stock()) : ?>
                    <button class="add-to-cart-btn shop-add-to-cart" 
                            data-product-id="<?php echo esc_attr($product->get_id()); ?>"
                            data-product-name="<?php echo esc_attr($product->get_name()); ?>"
                            aria-label="Add <?php echo esc_attr($product->get_name()); ?> to cart">
                        <span class="button-text">Add to Cart</span>
                        <span class="loading-spinner"></span>
                    </button>
                <?php else : ?>
                    <button class="add-to-cart-btn shop-add-to-cart out-of-stock" disabled>
                        <?php echo esc_html__('Out of Stock', 'woocommerce'); ?>
                    </button>
                <?php endif; ?>
                
                <a href="<?php the_permalink(); ?>" class="quick-view-btn">
                    View Details
                </a>
            </div>
        </div>
    </div>
</div>
