<?php
/**
 * Additional information tab
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/single-product/tabs/additional-information.php.
 */

defined( 'ABSPATH' ) || exit;

global $product;

$heading = apply_filters( 'woocommerce_product_additional_information_heading', __( 'Additional information', 'woocommerce' ) );

?>

<?php if ( $heading ) : ?>
	<h2><?php echo esc_html( $heading ); ?></h2>
<?php endif; ?>

<?php do_action( 'woocommerce_product_additional_information', $product ); ?>

<?php
// Add ingredients carousel if ingredients exist
$ingredients = get_field('ingredients', $product->get_id());
if ($ingredients && !empty($ingredients)) :
?>
    <div class="product-ingredients-section">
        <h3>Key Ingredients</h3>
        <?php echo do_shortcode('[product_ingredients_scroll id="' . $product->get_id() . '"]'); ?>
    </div>
<?php endif; ?>
