<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 */

defined( 'ABSPATH' ) || exit;

class Cedar_WooCommerce_Min_Max_Quantities {

    function __construct() {

        add_action( 'woocommerce_product_options_inventory_product_data', array( $this, 'display_fields' ) );

        add_action( 'woocommerce_process_product_meta', array( $this, 'save_fields' ) );

        add_filter( 'woocommerce_quantity_input_args', array( $this, 'input_args' ), 10, 2 );

        add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'add_to_cart_validation' ), 1, 5 );

        add_filter( 'woocommerce_update_cart_validation', array( $this, 'update_cart_validation' ), 1, 4 );

    }

    public function display_fields() {
    	echo '<div class="options_group">';
    	woocommerce_wp_text_input(
    		array(
    			'id'          => '_woocommerce_min_qty_product',
    			'label'       => __( 'Minimum Quantity', 'cedar' ),
    			'placeholder' => '',
    			'desc_tip'    => true,
    			'description' => __( 'Optional. Set a minimum quantity limit allowed per order. Enter a number, 1 or greater.', 'cedar' )
    		)
    	);
    	echo '</div>';
    	echo '<div class="options_group">';
    	woocommerce_wp_text_input(
    		array(
    			'id'          => '_woocommerce_max_qty_product',
    			'label'       => __( 'Maximum Quantity', 'cedar' ),
    			'placeholder' => '',
    			'desc_tip'    => true,
    			'description' => __( 'Optional. Set a maximum quantity limit allowed per order. Enter a number, 1 or greater.', 'cedar' )
    		)
    	);
    	echo '</div>';
    }


    public function save_fields( $post_id ) {
    	$val_min = trim( get_post_meta( $post_id, '_woocommerce_min_qty_product', true ) );
    	$new_min = sanitize_text_field( $_POST['_woocommerce_min_qty_product'] );
    	$val_max = trim( get_post_meta( $post_id, '_woocommerce_max_qty_product', true ) );
    	$new_max = sanitize_text_field( $_POST['_woocommerce_max_qty_product'] );

    	if ( $val_min != $new_min ) {
    		update_post_meta( $post_id, '_woocommerce_min_qty_product', $new_min );
    	}
    	if ( $val_max != $new_max ) {
    		update_post_meta( $post_id, '_woocommerce_max_qty_product', $new_max );
    	}
    }

    public function input_args( $args, $product ) {

    	$product_id = $product->get_parent_id() ? $product->get_parent_id() : $product->get_id();

    	$product_min = $this->get_min_limit( $product_id );
    	$product_max = $this->get_max_limit( $product_id );
    	if ( ! empty( $product_min ) ) {
    		// min is empty
    		if ( false !== $product_min ) {
    			$args['min_value'] = $product_min;
    		}
    	}
    	if ( ! empty( $product_max ) ) {
    		// max is empty
    		if ( false !== $product_max ) {
    			$args['max_value'] = $product_max;
    		}
    	}
    	if ( $product->managing_stock() && ! $product->backorders_allowed() ) {
    		$stock = $product->get_stock_quantity();
    		$args['max_value'] = min( $stock, $args['max_value'] );
    	}
    	return $args;
    }

    public function get_max_limit( $product_id ) {
    	$qty = get_post_meta( $product_id, '_woocommerce_max_qty_product', true );
    	if ( empty( $qty ) ) {
    		$limit = false;
    	} else {
    		$limit = (int) $qty;
    	}
    	return $limit;
    }

    public function get_min_limit( $product_id ) {
    	$qty = get_post_meta( $product_id, '_woocommerce_min_qty_product', true );
    	if ( empty( $qty ) ) {
    		$limit = false;
    	} else {
    		$limit = (int) $qty;
    	}
    	return $limit;
    }


    public function add_to_cart_validation( $passed, $product_id, $quantity, $variation_id = '', $variations = '' ) {
    	$product_min = $this->get_min_limit( $product_id );
    	$product_max = $this->get_max_limit( $product_id );
    	if ( ! empty( $product_min ) ) {
    		// min is empty
    		if ( false !== $product_min ) {
    			$new_min = $product_min;
    		} else {
    			// neither max is set, so get out
    			return $passed;
    		}
    	}
    	if ( ! empty( $product_max ) ) {
    		// min is empty
    		if ( false !== $product_max ) {
    			$new_max = $product_max;
    		} else {
    			// neither max is set, so get out
    			return $passed;
    		}
    	}
    	$already_in_cart 	= $this->get_cart_qty( $product_id );
    	$product 			= wc_get_product( $product_id );
    	$product_title 		= $product->get_title();

    	if ( !is_null( $new_max ) && !empty( $already_in_cart ) ) {

    		if ( ( $already_in_cart + $quantity ) > $new_max ) {
    			// oops. too much.
    			$passed = false;
    			wc_add_notice( apply_filters( 'isa_wc_max_qty_error_message_already_had', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s. You already have %4$s.', 'cedar' ),
    						$new_max,
    						$product_title,
    						'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'cedar' ) . '</a>',
    						$already_in_cart ),
    					$new_max,
    					$already_in_cart ),
    			'error' );
    		}
    	}
    	return $passed;
    }
    /*
    * Get the total quantity of the product available in the cart.
    */
    public function get_cart_qty( $product_id , $cart_item_key = '' ) {
    	global $woocommerce;
    	$running_qty = 0; // iniializing quantity to 0
    	// search the cart for the product in and calculate quantity.
    	foreach($woocommerce->cart->get_cart() as $other_cart_item_keys => $values ) {
    		if ( $product_id == $values['product_id'] ) {
    			if ( $cart_item_key == $other_cart_item_keys ) {
    				continue;
    			}
    			$running_qty += (int) $values['quantity'];
    		}
    	}
    	return $running_qty;
    }

    public function update_cart_validation( $passed, $cart_item_key, $values, $quantity ) {
    	$product_min = $this->get_min_limit( $values['product_id'] );
    	$product_max = $this->get_max_limit( $values['product_id'] );
    	if ( ! empty( $product_min ) ) {
    		// min is empty
    		if ( false !== $product_min ) {
    			$new_min = $product_min;
    		} else {
    			// neither max is set, so get out
    			return $passed;
    		}
    	}
    	if ( ! empty( $product_max ) ) {
    		// min is empty
    		if ( false !== $product_max ) {
    			$new_max = $product_max;
    		} else {
    			// neither max is set, so get out
    			return $passed;
    		}
    	}
    	$product = wc_get_product( $values['product_id'] );
    	$already_in_cart = $this->get_cart_qty( $values['product_id'], $cart_item_key );
    	if ( ( $already_in_cart + $quantity ) > $new_max ) {
    		wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You can add a maximum of %1$s %2$s\'s to %3$s.', 'cedar' ),
    					$new_max,
    					$product->get_name(),
    					'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'cedar' ) . '</a>'),
    				$new_max ),
    		'error' );
    		$passed = false;
    	}
    	if ( ( $already_in_cart + $quantity )  < $new_min ) {
    		wc_add_notice( apply_filters( 'wc_qty_error_message', sprintf( __( 'You should have minimum of %1$s %2$s\'s to %3$s.', 'cedar' ),
    					$new_min,
    					$product->get_name(),
    					'<a href="' . esc_url( wc_get_cart_url() ) . '">' . __( 'your cart', 'cedar' ) . '</a>'),
    				$new_min ),
    		'error' );
    		$passed = false;
    	}
    	return $passed;
    }

}

new Cedar_WooCommerce_Min_Max_Quantities;
