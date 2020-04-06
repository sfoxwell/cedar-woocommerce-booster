<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 */

defined( 'ABSPATH' ) || exit;

class Cedar_WooCommerce_Contact {

    public function __construct() {

        add_filter( 'woocommerce_general_settings', array( $this, 'display_settings' ) );

    }

    public function display_settings( $settings ) {
        $key = 0;

        foreach( $settings as $values ){
            $new_settings[$key] = $values;
            $key++;

            // Inserting array just after the post code in "Store Address" section
            if ( $values['id'] == 'woocommerce_store_postcode' ) {
                $new_settings[$key] = array(
                    'title'    => __( 'Phone Number' ),
                    'desc'     => __( 'Optional phone number of your business office' ),
                    'id'       => 'woocommerce_store_phone', // <= The field ID (important)
                    'default'  => '',
                    'type'     => 'text',
                    'desc_tip' => true, // or false
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Email' ),
                    'desc'     => __( 'Optional email of your business office' ),
                    'id'       => 'woocommerce_store_email', // <= The field ID (important)
                    'default'  => '',
                    'type'     => 'email',
                    'desc_tip' => true, // or false
                );
                $key++;
            }
        }
        return $new_settings;
    }
}
new Cedar_WooCommerce_Contact;
