<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 */

defined( 'ABSPATH' ) || exit;

class Cedar_WooCommerce_Recursive_Discounts {

    public function __construct() {

        add_action( 'woocommerce_product_options_pricing', array( $this, 'admin_display' ) );
        add_action( 'woocommerce_variation_options_pricing', array( $this, 'admin_display' ) );
        add_action( 'woocommerce_process_product_meta', array( $this, 'admin_save' ), 10, 2 );

        add_action( 'woocommerce_single_product_summary', array( $this, 'display' ), 15 );

        add_action( 'woocommerce_before_calculate_totals', array( $this, 'calculate_totals' ), 9999 );
    }

    public function admin_display() {

    	echo '<div class="options_group">';

    	woocommerce_wp_text_input( array(
    		'id'      => 'recursive_quantity_discount',
    		'value'   => get_post_meta( get_the_ID(), 'recursive_quantity_discount', true ),
    		'label'   => 'Recursive Product Discount',
    		'desc_tip' => false,
    		'description' => 'Percentage of recursive product discount',
    	) );

    	echo '</div>';

    }
    public function admin_save( $id, $post ){

    	update_post_meta( $id, 'recursive_quantity_discount', $_POST['recursive_quantity_discount'] );

    }

    public function get_max_limit( $product_id ) {
    	$qty = get_post_meta( $product_id, '_woocommerce_max_qty_product', true );
    	if ( empty( $qty ) ) {
    		$limit = 1;
    	} else {
    		$limit = (int) $qty;
    	}
    	return $limit;
    }

    public function get_min_limit( $product_id ) {
    	$qty = get_post_meta( $product_id, '_woocommerce_min_qty_product', true );
    	if ( empty( $qty ) ) {
    		$limit = 999;
    	} else {
    		$limit = (int) $qty;
    	}
    	return $limit;
    }


    public function display() {
		global $product;
		if ( get_post_meta( get_the_ID(), 'recursive_quantity_discount', true ) == '' || get_post_meta( get_the_ID(), 'recursive_quantity_discount', true ) == '0' ) {
			return;
		}
        $min_limit = $this->get_min_limit( get_the_ID() );
        $max_limit = $this->get_max_limit( get_the_ID() );
		$discount_rate = bcsub( 1, bcdiv( get_post_meta( get_the_ID(), 'recursive_quantity_discount', true ), 100, 8 ), 8 );
		?>
		<input id="cedar-recursive-discount-quantity-control" type="range" min="<?php echo $min_limit; ?>" max="<?php echo $max_limit; ?>" value="1" step="1" data-price="<?php echo $product->get_price(); ?>">
		<script>
			document.addEventListener('DOMContentLoaded', function() {
				var productPrice = document.querySelector('div.product .price'),
					productPriceCurrency = document.querySelector('div.product .price .woocommerce-Price-currencySymbol'),
					slider = document.getElementById('cedar-recursive-discount-quantity-control'),
					quantity = document.querySelector('div.product form.cart [name="quantity"]'),
					originalPrice = slider.getAttribute('data-price');
				slider.value = quantity.value;
				var calcPrice = function () {
					quantity.value = slider.value;
					var price = parseInt(originalPrice),
						savings = 0;

					for ( var i = 1; i < parseInt(slider.value); i++ ) {
						price = price * <?php echo $discount_rate; ?>;
					}
					savings = quantity.value * ( originalPrice - price );
					total = price * quantity.value;
					total = total.toFixed(2);
					price = price.toFixed(2);
					savings = savings.toFixed(2);
					var nf = new Intl.NumberFormat();
					savings = nf.format(savings);
					total = nf.format(total);
					productPrice.innerHTML = '<span class="woocommerce-Price-amount amount">' + productPriceCurrency.outerHTML + price + ' / unit</span>';
					productPrice.innerHTML += '<br><span class="woocommerce-Price-amount amount">Total ' + productPriceCurrency.outerHTML + total + '</span>';
					if ( savings.length > 1 ) {
						productPrice.innerHTML += '&nbsp;<mark class="discount"><span class="woocommerce-Price-amount amount">Save ' + productPriceCurrency.outerHTML + savings + '</span></mark>';
					}
				};
				slider.addEventListener( 'input', function () {
					calcPrice();
					setTimeout(calcPrice, 100);
				});
                quantity.addEventListener( 'change', function () {
                    slider.value = quantity.value;
					calcPrice();
				});
				calcPrice();
			});
		</script>
		<?php
	}

    public function calculate_totals() {
        if ( ( is_admin() && ! defined( 'DOING_AJAX' ) ) || did_action( 'woocommerce_before_calculate_totals' ) >= 2  ) {
            return;
        }
        global $woocommerce;
        $cart = $woocommerce->cart;

    	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
    		$product_id = $cart_item['product_id'];
    		if ( get_post_meta( $cart_item['product_id'], 'recursive_quantity_discount', true ) == '' || get_post_meta( $cart_item['product_id'], 'recursive_quantity_discount', true ) == '0' ) {
    			continue;
    		}
    		$discount_rate = bcsub( 1, bcdiv( get_post_meta( $cart_item['product_id'], 'recursive_quantity_discount', true ), 100, 8 ), 8 );
    		$price = $cart_item['data']->get_price();
    		for ( $i = 1; $i < $cart_item['quantity']; $i++ ) {
    			$price = bcmul( $price, $discount_rate, 8);
    		}
    		$cart_item['data']->set_price( $price );
    	}
    }
}

new Cedar_WooCommerce_Recursive_Discounts;
