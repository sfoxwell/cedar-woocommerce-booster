<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 */

defined( 'ABSPATH' ) || exit;

class Cedar_WooCommerce_Display_Options {

    public function __construct() {

        add_filter( 'woocommerce_products_general_settings', array( $this, 'display_settings' ) );

        add_action( 'customize_register', array( $this, 'display_customizer_controls' ) );

        if ( get_option( 'woocommerce_enable_breadcrumbs' ) != 'yes' ) {

            remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );

        }

        if ( get_option( 'woocommerce_remove_content_wrapper' ) == 'yes' ) {
            remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
            remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
        }

        add_filter( 'loop_shop_columns', array( $this, 'woocommerce_loop_columns' ) );
        add_filter( 'woocommerce_output_related_products_args', array( $this, 'related_products_args' ) );
        add_filter( 'loop_shop_per_page', array( $this, 'products_per_page' ) );



        add_action('wp', function () {
            if ( get_theme_mod( 'woocommerce_single_product_vertical_tabs' ) ) {
                add_filter( 'woocommerce_locate_template', array( $this, 'locate_tabs_template' ), 10, 3 );
                add_filter( 'woocommerce_product_description_heading', '__return_false' );
                add_filter( 'woocommerce_product_additional_information_heading', '__return_false' );
            }

            if ( get_theme_mod( 'woocommerce_hide_results_count' ) ) {
                remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
            }
            if ( get_theme_mod( 'woocommerce_hide_catalog_ordering' ) ) {
                remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
            }

            if ( get_theme_mod( 'woocommerce_loop_show_variable_form' ) ) {
                add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'loop_add_variable_form' ), 10, 2);
            }
        });

    }



    public function display_settings( $settings ) {
        $key = 0;

        foreach( $settings as $values ){
            $new_settings[$key] = $values;
            $key++;

            // Inserting array just after the post code in "Store Address" section
            if ( $values['id'] == 'woocommerce_placeholder_image' ) {

                $new_settings[$key] = array(
                    'title'    => __( 'Enable breadcrumbs', 'cedar' ),
                    'desc'     => __( 'Show breadcrumbs on shop and product pages', 'cedar' ),
                    'id'       => 'woocommerce_enable_breadcrumbs', // <= The field ID (important)
                    'default'  => 'no',
                    'type'     => 'checkbox',
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Remove wrapper', 'cedar' ),
                    'desc'     => __( 'Remove the default content wrapper markup', 'cedar' ),
                    'id'       => 'woocommerce_remove_content_wrapper', // <= The field ID (important)
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                );
                $key++;

            }
        }
        return $new_settings;
    }

    public function display_customizer_controls( $wp_customize ) {
        $wp_customize->add_setting( 'woocommerce_product_columns_count' );

    	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woocommerce_product_columns_count', array(
    		'label'			=> __( 'Product columns', 'cedar' ),
    		'description'	=> null,
    		'section'		=> 'woocommerce_product_catalog',
    		'settings'		=> 'woocommerce_product_columns_count',
    		'type'			=> 'range',
    		'input_attrs'	=> array(
    			'min'		=> 1,
    			'max'		=> 6
    		),
    	) ) );

    	$wp_customize->add_setting( 'woocommerce_loop_shop_per_page' );

    	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woocommerce_loop_shop_per_page', array(
    		'label'			=> __( 'Products per page', 'cedar' ),
    		'description'	=> 'Number of products to show per page',
    		'section'		=> 'woocommerce_product_catalog',
    		'settings'		=> 'woocommerce_loop_shop_per_page',
    		'type'			=> 'number',
    	) ) );

    	$wp_customize->add_setting( 'woocommerce_loop_show_variable_form' );

    	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woocommerce_loop_show_variable_form', array(
    		'label'			=> __( 'Show Variable Form', 'cedar' ),
    		'description'	=> 'Show the product variable form on shop archive pages.',
    		'section'		=> 'woocommerce_product_catalog',
    		'settings'		=> 'woocommerce_loop_show_variable_form',
    		'type'			=> 'checkbox',
    	) ) );

    	$wp_customize->add_setting( 'woocommerce_hide_results_count' );

    	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woocommerce_hide_results_count', array(
    		'label'			=> __( 'Hide results count', 'cedar' ),
    		'description'	=> false,
    		'section'		=> 'woocommerce_product_catalog',
    		'settings'		=> 'woocommerce_hide_results_count',
    		'type'			=> 'checkbox',
    	) ) );

    	$wp_customize->add_setting( 'woocommerce_hide_catalog_ordering' );

    	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woocommerce_hide_catalog_ordering', array(
    		'label'			=> __( 'Hide catalog ordering', 'cedar' ),
    		'description'	=> false,
    		'section'		=> 'woocommerce_product_catalog',
    		'settings'		=> 'woocommerce_hide_catalog_ordering',
    		'type'			=> 'checkbox',
    	) ) );


        $wp_customize->add_section(
			'woocommerce_single_product',
			array(
				'title'    => __( 'Single Product', 'woocommerce' ),
				'priority' => 10,
				'panel'    => 'woocommerce',
			)
		);

        $wp_customize->add_setting( 'woocommerce_single_product_vertical_tabs' );

    	$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'woocommerce_single_product_vertical_tabs', array(
    		'label'			=> __( 'Use vertical tabs', 'cedar' ),
    		'description'	=> '...',
    		'section'		=> 'woocommerce_single_product',
    		'settings'		=> 'woocommerce_single_product_vertical_tabs',
    		'type'			=> 'checkbox',
    	) ) );
    }

    public function woocommerce_loop_columns() {
        return get_theme_mod( 'woocommerce_product_columns_count' ) != ''
            ? get_theme_mod( 'woocommerce_product_columns_count' )
            : 3;
    }

    public function related_products_args( $args ) {
        $count = $this->woocommerce_loop_columns();
        $defaults = array(
            'posts_per_page' => $count,
            'columns'        => $count,
        );
        $args = wp_parse_args( $defaults, $args );
        return $args;
    }

    public function products_per_page() {
        return get_theme_mod( 'woocommerce_loop_shop_per_page' ) != ''
            ? get_theme_mod( 'woocommerce_loop_shop_per_page' )
            : 12;
    }

    public function locate_tabs_template( $template, $template_name, $template_path ) {

        $basename = basename( $template );

        if ( $basename == 'tabs.php' ) {
            $template = trailingslashit( plugin_dir_path( dirname( __FILE__ ) ) ) . 'templates/single-product/tabs/tabs.php';
        }

        return $template;
    }

	public function loop_add_variable_form($button, $product) {

	    if ( $product->is_type('variable') ) {
	        ob_start();
            ?>
            <input class="is-hidden" type="checkbox" id="config_<?php echo $product->get_id(); ?>" name="config_<?php echo $product->get_id(); ?>">
            <label class="button" for="config_<?php echo $product->get_id(); ?>"><?php _e( 'Select options', 'woocommerce' ); ?></label>
            <div class="form-wrap">
                <?php
    	        woocommerce_template_single_add_to_cart();
                ?>
                <label for="config_<?php echo $product->get_id(); ?>">cancel</label>
            </div>
            <?php
    	        $button = ob_get_clean();
    	        $replacement = sprintf('data-product_id="%d" data-quantity="1" $1 ajax_add_to_cart add_to_cart_button product_type_simple ', $product->get_id());
    	        $button = preg_replace('/(class="single_add_to_cart_button)/', $replacement, $button);
	        //$button = preg_replace( '/Add to cart/', 'Add', $button );
	    }
	    return $button;
	}


}
new Cedar_WooCommerce_Display_Options;
