<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 */

defined( 'ABSPATH' ) || exit;

class Cedar_WooCommerce_Sort_Shipping_Rates {

    public function __construct() {

        add_filter( 'woocommerce_shipping_settings', array( $this, 'display_settings' ) );

        add_filter( 'woocommerce_package_rates' , array( $this, 'sort_rates' ), 10, 2 );

    }


    public function display_settings( $settings ) {
	   $key = 0;

	   foreach( $settings as $values ){
		   $new_settings[$key] = $values;
		   $key++;

		   // Inserting array just after the post code in "Store Address" section
		   if ( $values['id'] == 'woocommerce_shipping_cost_requires_address' ) {
			   $new_settings[$key] = array(
				   'title'    => __( 'Price sorting', 'cedar' ),
				   'desc'     => __( 'Order shipping rates by price', 'cedar' ),
				   'id'       => 'woocommerce_shipping_rates_orderby_price', // <= The field ID (important)
				   'default'  => 'yes',
				   'type'     => 'checkbox',
			   );
			   $key++;
		   }
	   }
	   return $new_settings;
	}

	public function sort_rates( $rates, $package ) {

		if ( get_option( 'woocommerce_shipping_rates_orderby_price' ) != 'yes' ) {
			return $rates;
		}

		if ( ! $rates ) {
			return;
		}

		$prices = array();
		foreach( $rates as $rate ) {
			$prices[] = $rate->cost;
		}
		array_multisort( $prices, $rates );

		return $rates;
	}


}
new Cedar_WooCommerce_Sort_Shipping_Rates;
