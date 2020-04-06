<?php

/**
 *
 * @link              https://rsmconnect.com
 * @since             0.1.0
 * @package           Cedar WooCommerce Booster
 */

defined( 'ABSPATH' ) || exit;

class Cedar_WooCommerce_Search {

    public $taxonomies = array();
    public $sku = false;

    public function __construct() {

        add_filter( 'woocommerce_general_settings', array( $this, 'display_settings' ) );

        if ( get_option( 'woocommerce_search_by_sku' ) == 'yes' ) {
            $this->sku = true;
        }

        if ( get_option( 'woocommerce_search_by_sku' ) == 'yes' || get_option( 'woocommerce_search_by_category' ) == 'yes' || get_option( 'woocommerce_search_by_tag' ) == 'yes' ) {
            if ( get_option( 'woocommerce_search_by_category' ) == 'yes' ) {
                $this->taxonomies[] = 'product_cat';
            }
            if ( get_option( 'woocommerce_search_by_tag' ) == 'yes' ) {
                $this->taxonomies[] = 'product_tag';
            }
            add_filter( 'posts_join', array( $this, 'posts_join' ), 1, 2 );
            add_filter( 'posts_where', array( $this, 'posts_where' ), 1, 2 );
            add_filter( 'posts_distinct', array( $this, 'posts_distinct' ), 1, 2 );
            add_filter( 'posts_groupby', array( $this, 'tax_search_groupby' ), 1, 2 );
        }

    }

    public function display_settings( $settings ) {
        $key = 0;

        foreach( $settings as $values ){
            $new_settings[$key] = $values;
            $key++;

            // Inserting array just after the post code in "Store Address" section
            if ( $values['id'] == 'checkout_options' && $values['type'] == 'sectionend' ) {
                $new_settings[$key] = array(
                    'title' => __( 'Search options', 'woocommerce' ),
                    'type'  => 'title',
                    'desc'  => '',
                    'id'    => 'search_options',
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Enable search by SKU', 'cedar' ),
                    'desc'     => __( '', 'cedar' ),
                    'id'       => 'woocommerce_search_by_sku', // <= The field ID (important)
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Enable search by category', 'cedar' ),
                    'desc'     => __( '', 'cedar' ),
                    'id'       => 'woocommerce_search_by_category', // <= The field ID (important)
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                );
                $key++;

                $new_settings[$key] = array(
                    'title'    => __( 'Enable search by tag', 'cedar' ),
                    'desc'     => __( '', 'cedar' ),
                    'id'       => 'woocommerce_search_by_tag', // <= The field ID (important)
                    'default'  => 'yes',
                    'type'     => 'checkbox',
                );
                $key++;

                $new_settings[$key] = array(
                    'type' => 'sectionend',
                    'id'   => 'search_options',
                );
                $key++;
            }
        }
        return $new_settings;
    }
    /*
    public function extend_query( $query ) {
        if ( $query->is_search() ) {
            $meta_query_args = array(
                array(
                    'key' => '_sku',
                    'value' => $query->query_vars['s'],
                    'compare' => 'LIKE',
                ),
            );
            $query->set( 'meta_query', $meta_query_args );
            add_filter( 'get_meta_sql', array( $this, 'and_to_or' ), 1 );
        }
        return $query;
    }

    function and_to_or( $sql ) {
        if ( 1 === strpos( $sql['where'], 'AND' ) ) {
            $sql['where'] = substr( $sql['where'], 4 );
            $sql['where'] = ' OR ' . $sql['where'];
        }
        //make sure that this filter will fire only once for the meta query
        remove_filter( 'get_meta_sql', array( $this, 'and_to_or' ), 1 );
        return $sql;
    }
    */
    public function posts_join( $join, $query ){
    	global $wpdb;

        if ( $query->is_search() && $this->sku ) {
            $join .=' LEFT JOIN '.$wpdb->postmeta. ' ON '. $wpdb->posts . '.ID = ' . $wpdb->postmeta . '.post_id ';
        }

    	if( $query->is_search() && $query->is_main_query() ){
    		$join .= "
    		LEFT JOIN
    		  {$wpdb->term_relationships} ON {$wpdb->posts}.ID = {$wpdb->term_relationships}.object_id
    		LEFT JOIN
    		  {$wpdb->term_taxonomy} ON {$wpdb->term_taxonomy}.term_taxonomy_id = {$wpdb->term_relationships}.term_taxonomy_id
    		LEFT JOIN
    		  {$wpdb->terms} ON {$wpdb->terms}.term_id = {$wpdb->term_taxonomy}.term_id ";
    	}
    	return $join;
    }

    public function posts_where( $where, $query ){
      	global $wpdb;
        if ( $query->is_search() && $this->sku ) {
            $where = preg_replace(
            "/\(\s*".$wpdb->posts.".post_title\s+LIKE\s*(\'[^\']+\')\s*\)/",
            "(".$wpdb->posts.".post_title LIKE $1) OR (".$wpdb->postmeta.".meta_key = '_sku' AND ".$wpdb->postmeta.".meta_value LIKE $1)", $where );
        }
      	if( $query->is_search() && $query->is_main_query() ){
            $in = "";
            foreach ( $this->taxonomies as $tax ) {
                $in .= "'$tax'";
                if ( $tax != end($this->taxonomies) ) {
                    $in .= ",";
                }
            }
    		$where .= " OR (
    		{$wpdb->term_taxonomy}.taxonomy IN($in)
    		AND
    		{$wpdb->terms}.name LIKE ('%".$wpdb->escape( get_query_var('s') )."%') ) ";
      	}

      	return $where;
    }

    public function posts_distinct( $distinct, $query ) {
        global $wpdb;

        if ( $query->is_search() && $this->sku ) {
            return 'DISTINCT';
        }
        return $distinct;
    }

    public function tax_search_groupby( $groupby, $query ){
    	global $wpdb;
    	if( $query->is_search() && $query->is_main_query() ){
    		$groupby = "{$wpdb->posts}.ID";
    	}

    	return $groupby;
    }

}
new Cedar_WooCommerce_Search;
