<?php
/**
 * @package SSCY
 */
/*
Plugin Name: Burton Media - Woo Reports
Plugin URI: https://github.com/jessejburton/bm-woo-reports
Description: Generate a report of order data from WooCommerce based on product
Author: Jesse James Burton
Author URI: http://www.burtonmediainc.com
Version: 1.0
License: GPLv2 or later
Text Domain: burtonmedia
*/

/*
  ==========================================================
  Exit if file is called directly
  ==========================================================
*/
defined( 'ABSPATH' ) or die( 'Hey! Get outta here!' );

/*
  =======================================================
  Add Menu Item
  =======================================================
*/
function register_custom_menu_page() {
    add_submenu_page(
        'woocommerce',
        __( 'Orders By Product', 'burtonmedia' ),
        'Orders by Product',
        'manage_options',
        'bm-woo-reports/orders-by-product.php',
        '',
        'dashicons-chart-pie',
        6
    );
}
add_action( 'admin_menu', 'register_custom_menu_page' );

/**
 * Display page
 */
function orders_by_product_page(){
    esc_html_e( 'Orders by Product', 'burtonmedia' );
}

/*
 * STYLES
*/
function load_custom_wp_admin_style() {
    wp_register_style( 'bm_report_css', plugins_url('css/styles.css', __FILE__));
    wp_enqueue_style( 'bm_report_css' );
}
add_action( 'admin_enqueue_scripts', 'load_custom_wp_admin_style' );