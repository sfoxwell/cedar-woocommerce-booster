<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 */

defined( 'ABSPATH' ) || exit;

class Cedar_WooCommerce_Checkout_Options {

    public function __construct() {

        add_filter( 'woocommerce_general_settings', array( $this, 'display_settings' ) );

        if ( get_option( 'woocommerce_single_page_checkout' ) == 'yes' ) {

            add_action( 'wp', array( $this, 'maybe_redirect_to_checkout' ) );

            add_action( 'woocommerce_before_cart_table', array( $this, 'add_cart_title' ) );
            add_action( 'woocommerce_before_checkout_form', array( $this, 'add_cart_to_checkout' ), 5 );

            add_filter('woocommerce_add_to_cart_redirect', array( $this, 'skip_cart_on_add_to_cart' ) );

            add_filter( 'woocommerce_get_cart_url', array( $this, 'get_cart_url' ), 99 );

        }

        if ( get_option( 'woocommerce_expand_coupon_form' ) == 'yes' ) {

            add_filter( 'woocommerce_locate_template', array( $this, 'locate_coupon_template' ), 10, 3 );
        }

        if ( get_option( 'woocommerce_hide_order_notes' ) == 'yes' ) {
            add_filter( 'woocommerce_enable_order_notes_field', '__return_false' );
        }

        if ( get_option( 'woocommerce_disable_selectwoo' ) == 'yes' ) {
            add_action( 'wp_enqueue_scripts', array( $this, 'disable_selectwoo' ), 100 );
        }


        add_filter( 'woocommerce_billing_fields', array( $this, 'checkout_fields_prioritize_email' ), 10, 1 );


    }

    public function display_settings( $settings ) {
        $key = 0;

        foreach( $settings as $values ){
            $new_settings[$key] = $values;
            $key++;

            // Inserting array just after the post code in "Store Address" section
            if ( $values['id'] == 'general_options' && $values['type'] == 'sectionend' ) {
                $new_settings[$key] = array(
                    'title' => __( 'Checkout options', 'woocommerce' ),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'checkout_options',
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Single page checkout', 'cedar' ),
                    'desc'     => __( 'Combine cart and checkout pages', 'cedar' ),
                    'id'       => 'woocommerce_single_page_checkout', // <= The field ID (important)
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Show coupon form', 'cedar' ),
                    'desc'     => __( 'Replace the collapsed coupon form with an expanded one', 'cedar' ),
                    'id'       => 'woocommerce_expand_coupon_form', // <= The field ID (important)
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Hide order notes', 'cedar' ),
                    'desc'     => __( 'Hide the optional order notes field', 'cedar' ),
                    'id'       => 'woocommerce_hide_order_notes', // <= The field ID (important)
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Disable enhanced selects', 'cedar' ),
                    'desc'     => __( 'Disable searchable dropdowns on the checkout form', 'cedar' ),
                    'id'       => 'woocommerce_disable_selectwoo', // <= The field ID (important)
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                );
                $key++;

                $new_settings[$key] = array(
                    'type' => 'sectionend',
                    'id'   => 'checkout_options',
                );
                $key++;
            }
        }
        return $new_settings;
    }

    public function add_cart_title() {
        echo '<h3>' . __( 'Cart', 'cedar' ) . '</h3>';
    }

    public function add_cart_to_checkout() {
        if ( is_wc_endpoint_url( 'order-received' ) ) {
            return;
		}
		echo do_shortcode('[woocommerce_cart]');
    }

    public function skip_cart_on_add_to_cart() {
        if ( get_option( 'woocommerce_cart_redirect_after_add' ) == 1 ) {
			global $woocommerce;
			$checkout_url = wc_get_checkout_url();
			return $checkout_url;
		}
    }

    public function get_cart_url( $url ) {
        global $woocommerce;
    	return wc_get_checkout_url();
    }

    public function maybe_redirect_to_checkout() {
        global $woocommerce;
        if ( is_cart() && $woocommerce->cart->get_cart_total() > 0 ) {
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }
    }

    public function locate_coupon_template( $template, $template_name, $template_path ) {

        $basename = basename( $template );

        if ( $basename == 'form-coupon.php' ) {
            $template = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'templates/checkout/form-coupon.php';
        }

        return $template;
    }

    public function disable_selectwoo() {
		wp_dequeue_style( 'selectWoo' );
		wp_deregister_style( 'selectWoo' );

		wp_dequeue_script( 'selectWoo');
		wp_deregister_script('selectWoo');
	}

    public function checkout_fields_prioritize_email( $address_fields ) {
        $address_fields['billing_email']['priority'] = 9;
        return $address_fields;
    }


}
new Cedar_WooCommerce_Checkout_Options;
