<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>About <?php bloginfo('name'); ?></h3>
                <p><?php echo get_theme_mod('footer_about', 'Premium CBD beauty products for natural wellness and radiant skin. Discover the power of nature with our scientifically formulated products.'); ?></p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <?php
                wp_nav_menu(array(
                    'theme_location' => 'footer',
                    'menu_class' => 'footer-menu',
                    'container' => false,
                    'fallback_cb' => 'cbd_beauty_footer_fallback_menu'
                ));
                ?>
            </div>
            
            <div class="footer-section">
                <h3>Products</h3>
                <ul>
                    <li><a href="#">CBD Oils</a></li>
                    <li><a href="#">Face Serums</a></li>
                    <li><a href="#">Body Lotions</a></li>
                    <li><a href="#">Skincare Sets</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p>Email: <?php echo get_theme_mod('contact_email', 'info@cbdbeauty.com'); ?></p>
                <p>Phone: <?php echo get_theme_mod('contact_phone', '(555) 123-4567'); ?></p>
                <p>Address: <?php echo get_theme_mod('contact_address', '123 Beauty St, Wellness City, WC 12345'); ?></p>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php bloginfo('name'); ?>. All rights reserved.</p>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>
</body>
</html>
