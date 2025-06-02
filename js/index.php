<?php get_header(); ?>

<main class="main-content">
    <!-- Hero Section with Custom Background -->
    <section class="hero-section">
        <div class="container">
            <div class="hero-content">
                <h1><?php echo get_theme_mod('hero_title', 'DISCOVER CBD BEAUTY CERTIFIED'); ?></h1>
                <p class="hero-subtitle"><?php echo get_theme_mod('hero_subtitle', 'Premium CBD-infused beauty products crafted with natural ingredients for radiant, healthy skin'); ?></p>
                <a href="<?php echo get_theme_mod('hero_button_url', '/shop'); ?>" class="cta-button">
                    <?php echo get_theme_mod('hero_button_text', 'Shop Now'); ?>
                </a>
            </div>
        </div>
    </section>

    <!-- Featured Products Section -->
    <section class="product-grid" id="products">
        <div class="container">
            <h2 class="section-title">Featured Products</h2>
            <div class="products-container">
                <?php
                // Check if WooCommerce is active
                if (class_exists('WooCommerce')) {
                    $featured_products = wc_get_featured_product_ids();
                    
                    if (!empty($featured_products)) {
                        $products = new WP_Query(array(
                            'post_type' => 'product',
                            'post__in' => array_slice($featured_products, 0, 8),
                            'posts_per_page' => 8
                        ));
                    } else {
                        $products = new WP_Query(array(
                            'post_type' => 'product',
                            'posts_per_page' => 8,
                            'meta_query' => array(
                                array(
                                    'key' => '_visibility',
                                    'value' => array('catalog', 'visible'),
                                    'compare' => 'IN'
                                )
                            )
                        ));
                    }
                } else {
                    // Fallback to custom products if WooCommerce is not active
                    $products = new WP_Query(array(
                        'post_type' => 'product',
                        'posts_per_page' => 8,
                        'meta_query' => array(
                            array(
                                'key' => '_featured',
                                'value' => 'yes'
                            )
                        )
                    ));
                }

                if ($products->have_posts()) :
                    while ($products->have_posts()) : $products->the_post();
                        if (class_exists('WooCommerce')) {
                            global $product;
                ?>
                    <div class="product-card">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php the_title(); ?>" class="product-image">
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/placeholder-product.jpg" alt="<?php the_title(); ?>" class="product-image">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3 class="product-title"><?php the_title(); ?></h3>
                                <div class="product-price">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php
                        } else {
                            // Fallback for custom product post type
                ?>
                    <div class="product-card">
                        <a href="<?php the_permalink(); ?>">
                            <?php if (has_post_thumbnail()) : ?>
                                <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'medium'); ?>" alt="<?php the_title(); ?>" class="product-image">
                            <?php else : ?>
                                <img src="<?php echo get_template_directory_uri(); ?>/assets/placeholder-product.jpg" alt="<?php the_title(); ?>" class="product-image">
                            <?php endif; ?>
                            <div class="product-info">
                                <h3 class="product-title"><?php the_title(); ?></h3>
                                <div class="product-price">
                                    <?php echo get_post_meta(get_the_ID(), '_price', true) ? '$' . get_post_meta(get_the_ID(), '_price', true) : 'Contact for Price'; ?>
                                </div>
                            </div>
                        </a>
                    </div>
                <?php
                        }
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                    <div class="no-products">
                        <p>No featured products available at the moment. Please check back soon!</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <?php if (class_exists('WooCommerce')) : ?>
                <div style="text-align: center; margin-top: 40px;">
                    <a href="<?php echo wc_get_page_permalink('shop'); ?>" class="cta-button">View All Products</a>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Ingredient Showcase Section -->
    <?php if (class_exists('WooCommerce')) : ?>
    <section class="dark-section">
        <div class="container">
            <h2>Natural Ingredients, Proven Results</h2>
            <p>Our products are formulated with carefully selected, premium CBD and natural ingredients that work synergistically to enhance your skin's natural beauty and wellness.</p>
            
            <?php
            // Get a featured product to showcase ingredients
            $featured_products = wc_get_featured_product_ids();
            if (!empty($featured_products)) {
                $sample_product = $featured_products[0];
                $ingredients = get_field('ingredients', $sample_product);
                
                if ($ingredients && !empty($ingredients)) {
                    echo '<div style="margin-top: 40px;">';
                    echo do_shortcode('[product_ingredients_scroll id="' . $sample_product . '"]');
                    echo '</div>';
                }
            }
            ?>
        </div>
    </section>
    <?php endif; ?>

    <!-- Features Section -->
    <section class="features-section">
        <div class="container">
            <h2 class="section-title">Why Choose Our CBD Beauty Products</h2>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">🌿</div>
                    <h3 class="feature-title">100% Natural & Organic</h3>
                    <p>Made with certified organic hemp and natural botanicals, free from harmful chemicals and synthetic additives.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🧪</div>
                    <h3 class="feature-title">Third-Party Lab Tested</h3>
                    <p>Every batch is rigorously tested for purity, potency, and safety by independent laboratories to ensure premium quality.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">✨</div>
                    <h3 class="feature-title">Clinically Proven Results</h3>
                    <p>Our formulations are backed by scientific research and proven to improve skin hydration, elasticity, and overall health.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🌍</div>
                    <h3 class="feature-title">Sustainably Sourced</h3>
                    <p>We partner with eco-conscious farms and use sustainable practices to protect our planet while delivering exceptional products.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">💚</div>
                    <h3 class="feature-title">Cruelty-Free</h3>
                    <p>Never tested on animals. We believe in ethical beauty that respects all living beings and the environment.</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">🏆</div>
                    <h3 class="feature-title">Award-Winning Formulas</h3>
                    <p>Recognized by beauty experts and loved by customers worldwide for our innovative and effective CBD beauty solutions.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials-section">
        <div class="container">
            <h2 class="section-title">What Our Customers Say</h2>
            <div class="testimonials-grid">
                <?php
                $testimonials = new WP_Query(array(
                    'post_type' => 'testimonial',
                    'posts_per_page' => 3
                ));

                if ($testimonials->have_posts()) :
                    while ($testimonials->have_posts()) : $testimonials->the_post();
                ?>
                    <div class="testimonial-card">
                        <p class="testimonial-text">"<?php the_content(); ?>"</p>
                        <div class="testimonial-author"><?php the_title(); ?></div>
                    </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                else :
                ?>
                    <!-- Default testimonials -->
                    <div class="testimonial-card">
                        <p class="testimonial-text">"These CBD beauty products have completely transformed my skincare routine. My skin feels more hydrated, looks radiant, and I love knowing I'm using natural, sustainable ingredients."</p>
                        <div class="testimonial-author">Sarah Johnson</div>
                    </div>
                    <div class="testimonial-card">
                        <p class="testimonial-text">"I was skeptical at first, but after using the CBD face serum for just two weeks, I noticed a significant improvement in my skin's texture and appearance. The quality is outstanding!"</p>
                        <div class="testimonial-author">Michael Chen</div>
                    </div>
                    <div class="testimonial-card">
                        <p class="testimonial-text">"Finally found CBD beauty products that actually deliver results. The ingredient transparency and lab testing give me confidence in what I'm putting on my skin."</p>
                        <div class="testimonial-author">Emma Davis</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Brand Highlights Section -->
    <section class="dark-section">
        <div class="container">
            <h2>Trusted by Beauty Enthusiasts Worldwide</h2>
            <p>Join thousands of satisfied customers who have discovered the power of premium CBD beauty products. Experience the difference that quality, transparency, and innovation can make for your skin.</p>
            <div style="display: flex; justify-content: center; gap: 40px; margin-top: 40px; flex-wrap: wrap;">
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #fff;">50K+</div>
                    <div style="color: #ccc;">Happy Customers</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #fff;">100%</div>
                    <div style="color: #ccc;">Natural Ingredients</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #fff;">5★</div>
                    <div style="color: #ccc;">Average Rating</div>
                </div>
                <div style="text-align: center;">
                    <div style="font-size: 2.5rem; font-weight: bold; color: #fff;">24/7</div>
                    <div style="color: #ccc;">Customer Support</div>
                </div>
            </div>
        </div>
    </section>
</main>

<?php get_footer(); ?>
