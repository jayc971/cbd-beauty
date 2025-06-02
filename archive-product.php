<?php get_header(); ?>

<main class="main-content">
    <section class="shop-page">
        <div class="container">
            <header class="shop-header">
                <h1 class="shop-title">Our Premium CBD Beauty Products</h1>
                <p class="shop-description">Discover our complete range of natural, lab-tested CBD beauty products designed to enhance your skin's natural radiance.</p>
            </header>
            
            <?php if (class_exists('WooCommerce')) : ?>
                <div class="shop-toolbar">
                    <div class="shop-results">
                        <?php woocommerce_result_count(); ?>
                    </div>
                    <div class="shop-ordering">
                        <?php woocommerce_catalog_ordering(); ?>
                    </div>
                </div>
                
                <?php if (woocommerce_product_loop()) : ?>
                    <div class="products-grid">
                        <?php woocommerce_product_loop_start(); ?>
                        
                        <?php if (wc_get_loop_prop('is_shortcode')) : ?>
                            <?php woocommerce_product_subcategories(); ?>
                        <?php endif; ?>
                        
                        <?php while (have_posts()) : ?>
                            <?php the_post(); ?>
                            <?php 
                            /**
                             * Hook: woocommerce_shop_loop.
                             */
                            do_action('woocommerce_shop_loop');
                            
                            wc_get_template_part('content', 'product'); 
                            ?>
                        <?php endwhile; ?>
                        
                        <?php woocommerce_product_loop_end(); ?>
                    </div>
                    
                    <div class="shop-pagination">
                        <?php woocommerce_pagination(); ?>
                    </div>
                <?php else : ?>
                    <div class="no-products-found">
                        <div class="no-products-icon">🔍</div>
                        <h2>No products found</h2>
                        <p>We couldn't find any products matching your criteria. Please try adjusting your search or browse our categories.</p>
                        <a href="<?php echo home_url(); ?>" class="cta-button">Return to Homepage</a>
                    </div>
                <?php endif; ?>
            <?php else : ?>
                <div class="woocommerce-missing">
                    <h2>WooCommerce Required</h2>
                    <p>This shop page requires WooCommerce to be installed and activated.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
</main>

<?php get_footer(); ?>
