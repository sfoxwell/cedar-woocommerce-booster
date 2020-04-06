<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 */

defined( 'ABSPATH' ) || exit;

class Cedar_WooCommerce_Variation_Swatches {

    function __construct() {

        add_action( 'woocommerce_variation_options_pricing', array( $this, 'display_fields' ), 10, 3 );

        add_action( 'woocommerce_save_product_variation', array( $this, 'save_fields' ), 10, 2 );

        add_filter( 'woocommerce_available_variation', array( $this, 'add_variation_data' ) );

    }

    function display_fields( $loop, $variation_data, $variation ) {
        woocommerce_wp_text_input( array(
            'id'    => 'color_swatch[' . $loop . ']',
            'class' => 'short',
            'label' => __( 'Color swatch', 'woocommerce' ),
            'value' => get_post_meta( $variation->ID, 'color_swatch', true )
        ) );
    }

    function save_fields( $variation_id, $i ) {
        $custom_field = $_POST['color_swatch'][$i];
        if ( isset( $custom_field ) ) {
            update_post_meta( $variation_id, 'color_swatch', esc_attr( $custom_field ) );
        }
    }
    function add_variation_data( $variations ) {
        $variations['custom_field'] = '<div class="woocommerce_color_swatch">Color Swatch: <span>' . get_post_meta( $variations[ 'variation_id' ], 'color_swatch', true ) . '</span></div>';
        return $variations;
    }

}

new Cedar_WooCommerce_Variation_Swatches;
