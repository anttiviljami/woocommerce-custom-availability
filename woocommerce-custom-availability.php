<?php
/**
 * Plugin name: WooCommerce Custom Availability
 * Plugin URI: https://github.com/anttiviljami/woocommerce-custom-availability
 * Description: Set custom availability for products
 * Version: 1.1
 * Author: @anttiviljami
 * Author: https://github.com/anttiviljami
 * License: GPLv3
 * Text Domain: woocommerce-custom-availability
 * Tested up to: 4.8
 */

/** Copyright 2017 Antti Kuosmanen
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 3, as
  published by the Free Software Foundation.
  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
  GNU General Public License for more details.
  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/


if ( ! defined( 'ABSPATH' ) ) {
  exit; // Exit if accessed directly.
}

if ( ! class_exists( 'WooCommerce_Custom_Availability' ) ) :

class WooCommerce_Custom_Availability {
  public static $instance;

  public static function init() {
    if ( is_null( self::$instance ) ) {
      self::$instance = new WooCommerce_Custom_Availability();
    }
    return self::$instance;
  }

  private function __construct() {

    $this->includes();

    // load textdomain for translations
    add_action( 'plugins_loaded',  array( $this, 'load_our_textdomain' ) );

    // add single custom fields
    add_action( 'woocommerce_product_options_stock_fields', array( $this, 'custom_availability_field' ) );

    // save handler for custom fields
    add_action( 'save_post', array( $this, 'save_custom_availability_field' ) );

    // add variation custom fields
    add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'custom_availability_variation_field' ), 10, 3 );

    // save handler for the variation fields
    add_action( 'woocommerce_save_product_variation', array( $this, 'save_custom_availability_variation_field' ), 10, 2 );

    // use custom availability in the frontend
    add_filter( 'woocommerce_get_availability', array( $this, 'custom_availability' ), 10, 2 );
  }

  /**
   * Include required core files used in admin
   */
  public function includes() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-custom-availability-list-table.php';
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-woocommerce-custom-availability-page.php';
  }

  /**
   * Load our textdomain
   */
  function load_our_textdomain() {
    load_plugin_textdomain( 'woocommerce-custom-availability', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
  }

  /**
   * Show the custom availability edit field for simple products
   */
  public function custom_availability_field() {
    global $post;

    woocommerce_wp_text_input(
      array(
        'id'          => '_custom_availability_simple',
        'label'       => __( 'Custom Availability', 'woocommerce-custom-availability' ),
        'placeholder' => '',
        'desc_tip'    => 'true',
        'description' => __( 'Override the default availability text.', 'woocommerce-custom-availability' ),
        'value'       => get_post_meta( $post->ID, '_custom_availability', true ),
      )
    );
  }

  /**
   * Handle the custom availability edit field save for simple products
   */
  public function save_custom_availability_field() {
    global $post;
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
      return;
    }

    if ( 'product' != $post->post_type ) {
      return;
    }

    $_custom_availability_simple = ( isset( $_POST['_custom_availability_simple'] ) && ! empty( $_POST['_custom_availability_simple'] ) ) ? sanitize_text_field( $_POST['_custom_availability_simple'] ) : '';
    update_post_meta( $post->ID, '_custom_availability', $_custom_availability_simple );
  }


  /**
   * Show the custom availability edit field for variations
   *
   * @param $loop
   * @param $variation_data
   * @param $variaton
   */
  public function custom_availability_variation_field( $loop, $variation_data, $variation ) {
    woocommerce_wp_text_input(
      array(
        'id'          => '_custom_availability[' . $variation->ID . ']',
        'label'       => __( 'Custom Availability', 'woocommerce-custom-availability' ) . ':',
        'placeholder' => '',
        'desc_tip'    => 'true',
        'description' => __( 'Override the default availability text.', 'woocommerce-custom-availability' ),
        'value'       => get_post_meta( $variation->ID, '_custom_availability', true ),
      )
    );
  }

  /**
   * Handle the custom availability edit field save for variable products
   *
   * @param int $post_id
   */
  public function save_custom_availability_variation_field( $post_id ) {
    $_custom_availability = ( isset( $_POST['_custom_availability'][ $post_id ] ) && ! empty( $_POST['_custom_availability'][ $post_id ] ) ) ? sanitize_text_field( $_POST['_custom_availability'][ $post_id ] ) : '';
    update_post_meta( $post_id, '_custom_availability', $_custom_availability );
  }

  /**
   * Filter the availability text
   *
   * @param string $availability
   * @param WC_Product $product
   */
  public function custom_availability( $availability, $product ) {
    $product_id = isset( $product->variation_id ) ? $product->variation_id : $product->id;
    $custom_availability = get_post_meta( $product_id, '_custom_availability', true );

    if ( ! empty( $custom_availability ) ) {
      $availability['class'] = 'custom-availability';
      $availability['availability'] = esc_attr( $custom_availability );
    }
    return $availability;
  }
}

endif;

// init the plugin
$woocommerce_custom_availability = WooCommerce_Custom_Availability::init();
