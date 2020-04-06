<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 *
 * @wordpress-plugin
 * Plugin Name:       Cedar WooCommerce Booster
 * Plugin URI:        https://rsmconnect.com
 * Description:       WooCommerce Enhancements
 * Version:           0.1.0
 * Author:            RSM
 * Author URI:        https://rsmconnect.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       cedar
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

define( 'PLUGIN_NAME_VERSION', '0.1.0' );

class Cedar_WooCommerce_Booster {

    public function __construct() {
        if ( defined( 'PLUGIN_NAME_VERSION' ) ) {
			$this->version = PLUGIN_NAME_VERSION;
		} else {
			$this->version = '0.1.0';
		}
		$this->plugin_name = 'cedar-woocommerce-booster';

        $this->load_dependencies();
    }

    public function load_dependencies() {

        require_once plugin_dir_path( __FILE__ ) . 'includes/cedar-woocommerce-checkout-options.php';

        require_once plugin_dir_path( __FILE__ ) . 'includes/cedar-woocommerce-contact.php';

        require_once plugin_dir_path( __FILE__ ) . 'includes/cedar-woocommerce-display-options.php';

        require_once plugin_dir_path( __FILE__ ) . 'includes/cedar-woocommerce-min-max-quantities.php';

        require_once plugin_dir_path( __FILE__ ) . 'includes/cedar-woocommerce-recursive-discounts.php';

        require_once plugin_dir_path( __FILE__ ) . 'includes/cedar-woocommerce-search.php';

        require_once plugin_dir_path( __FILE__ ) . 'includes/cedar-woocommerce-sort-shipping-rates.php';

        require_once plugin_dir_path( __FILE__ ) . 'includes/cedar-woocommerce-variation-swatches.php';
    }
}

new Cedar_WooCommerce_Booster;
