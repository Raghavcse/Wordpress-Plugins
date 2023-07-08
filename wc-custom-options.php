<?php
/**
 * @package Custom Options
 * @version 1.0
 * Plugin Name: WC Custom Options
 * Description: Woocommerce and WC Vendor customizations
 * Author: Raghavendra Shukla
 * Author URI: http://raghavspn.wordpress.com/
 * Version: 1.0
 */

$active_plugins = apply_filters( 'active_plugins', get_option( 'active_plugins' ) );
if ( in_array( 'woocommerce/woocommerce.php',  $active_plugins) ) {
    add_filter( 'woocommerce_shipping_methods', 'add_ups_shipping_method' );
    function add_ups_shipping_method( $methods ) {
        $methods[] = 'WC_UPS_Shipping_Method';
        return $methods;
    }

    add_action( 'woocommerce_shipping_init', 'ups_shipping_method_init' );
    function ups_shipping_method_init(){
        require_once 'class-ups-shipping-method.php';
    }
}

class WC_UPS_Shipping_Method extends WC_Shipping_Method{

    public function __construct(){
      $this->id = 'ups_shipping_method';
        $this->method_title = __( 'UPS Shipping Method', 'woocommerce' );
        // Load the settings.
        $this->init_form_fields();
        $this->init_settings();

        // Define user set variables
        $this->enabled	= $this->get_option( 'enabled' );
        $this->title 		= $this->get_option( 'title' );


        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
    }

    public function init_form_fields(){
        $this->form_fields = array(
          'enabled' => array(
            'title' 		=> __( 'Enable/Disable', 'woocommerce' ),
            'type' 		=> 'checkbox',
            'label' 		=> __( 'Enable UPS Shipping', 'woocommerce' ),
            'default' 	=> 'yes'
          ),
          'title' => array(
            'title' 		=> __( 'Method Title', 'woocommerce' ),
            'type' 		=> 'text',
            'description' 	=> __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
            'default'		=> __( 'UPS Shipping', 'woocommerce' ),

          )
      );
    }

    public function is_available( $package ){
        foreach ( $package['contents'] as $item_id => $values ) {
        $_product = $values['data'];
        $weight =	$_product->get_weight();
        if($weight > 10){
            return false;
        }
        }

        return true;
    }

    public function calculate_shipping($package){
        //get the total weight and dimensions
      $weight = 0;
      $dimensions = 0;
      foreach ( $package['contents'] as $item_id => $values ) {
        $_product  = $values['data'];
        $weight =	$weight + $_product->get_weight() * $values['quantity'];
        $dimensions = $dimensions + (($_product->length * $values['quantity']) * $_product->width * $_product->height);

      }
      //calculate the cost according to the table
      switch ($weight) {
          case ($weight < 1):
            switch ($dimensions) {
              case ($dimensions <= 1000):
              $cost = 3;
              break;
              case ($dimensions > 1000):
              $cost = 4;
              break;
            }
           break;
          case ($weight >= 1 && $weight < 3 ):
            switch ($dimensions) {
              case ($dimensions <= 3000):
              $cost = 10;
              break;
            }
          break;
          case ($weight >= 3 && $weight < 10):
            switch ($dimensions) {
              case ($dimensions <= 5000):
              $cost = 25;
              break;
              case ($dimensions > 5000):
              $cost = 50;
              break;
            }
           break;

        }
      // send the final rate to the user.
      $this->add_rate( array(
        'id' 	=> $this->id,
        'label' => $this->title,
        'cost' 	=> $cost
      ));
    }

}
