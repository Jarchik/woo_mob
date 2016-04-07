<?php
/**
 * Plugin Name: WooCommerce 2Checkout Payment Gateway Free
 * Plugin URI: http://www.najeebmedia.com/2checkout-payment-gateway-for-woocommerce/
 * Description: 2Checkout is payment gateway for WooCommerce allowing you to take payments via 2Checkout.
 * Version: 1.7
 * Author: Najeeb Ahmad
 * Author URI: http://www.najeebmedia.com/
 */ 


add_action( 'plugins_loaded', 'init_nm_woo_gateway', 0);

function nm_2co_settings( $links ) {
    $settings_link = '<a href="'.admin_url( 'admin.php?page=wc-settings&tab=checkout&section=wc_gateway_nm_twocheckout' ).'">Setup</a>';
  	array_push( $links, $settings_link );
  	return $links;
}
$plugin = plugin_basename( __FILE__ );
add_filter( "plugin_action_links_$plugin", 'nm_2co_settings' );

function init_nm_woo_gateway(){

	if ( ! class_exists( 'WC_Payment_Gateway' ) ) return;

	class WC_Gateway_NM_TwoCheckout extends WC_Payment_Gateway {

		var $seller_id;
		var $demo;
		var $plugin_url;

		public function __construct(){
			
			global $woocommerce;

			$this -> plugin_url = WP_PLUGIN_URL . DIRECTORY_SEPARATOR . 'woocommerce-2checkout-payment';
			
			$this->id 					= 'nmwoo_2co';
			$this->has_fields   		= false;
			$this->checkout_url     	= 'https://www.2checkout.com/checkout/purchase';
			$this->checkout_url_sandbox	= 'https://sandbox.2checkout.com/checkout/purchase';
			$this->icon 				= $this -> plugin_url.'/images/2co_logo.png';
			$this->method_title 		= '2Checkout';
			$this->method_description 	= 'This plugin add 2checkout payment gateway with Woocommerce based shop. Make sure you have set your 2co account according <a href="http://najeebmedia.com/2checkout-payment-gateway-for-woocommerce/" target="_blank">these setting</a>';
				
			$this->title 				= $this->get_option( 'title' );
			$this->description 			= $this->get_option( 'description' );
			$this->seller_id			= $this->get_option( 'seller_id' );
			$this->secret_word			= $this->get_option( 'secret_word' );
			$this -> demo 				= $this -> get_option('demo');
			$this -> pay_method 		= $this -> get_option('pay_method'); 
				
				
			$this->init_form_fields();
			$this->init_settings();
				
			// Save options
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action('process_2co_ipn_request', array( $this, 'successful_request' ), 1 );
			
			// Payment listener/API hook
			add_action( 'woocommerce_api_wc_gateway_nm_twocheckout', array( $this, 'twocheckout_response' ) );
				
		}


		function init_form_fields(){

			$this->form_fields = array(
					'enabled' => array(
							'title' => __( 'Enable', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Yes', 'woocommerce' ),
							'default' => 'yes'
					),
					'inline' => array(
							'title' => __( 'Enable Inline Checkout', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Yes - It is PRO Feature get <a href="http://www.najeebmedia.com/2checkout-payment-gateway-for-woocommerce/" target="_blank">Pro Version</a>', 'woocommerce' ),
							'default' => 'yes'
					),
					'seller_id' => array(
							'title' => __( '2CO Account #', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'This Seller ID issued by 2Checkout', 'woocommerce' ),
							'default' => '',
							'desc_tip'      => true,
					),
					'secret_word' => array(
							'title' => __( 'Secret Word', 'woocommerce' ),
		                    'type' 			=> 'text',
		                    'description' => __( 'Please enter your 2Checkout Secret Word.', 'woocommerce' ),
		                    'default' => '',
		                    'desc_tip'      => true,
		                    'placeholder'	=> ''
					),
					
					'title' => array(
							'title' => __( 'Title', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
							'default' => __( '2Checkout Payment', 'woocommerce' ),
							'desc_tip'      => true,
					),
					'description' => array(
							'title' => __( 'Customer Message', 'woocommerce' ),
							'type' => 'textarea',
							'default' => ''
					),
					'demo' => array(
							'title' => __( 'Enable Demo Mode', 'woocommerce' ),
							'type' => 'checkbox',
							'label' => __( 'Yes', 'woocommerce' ),
							'default' => 'yes'
					),
					'pay_method' => array(
							'title' => __( 'Payment Method', 'woocommerce' ),
							'type' => 'text',
							'description' => __( 'CC for Credit Card, PPI for PayPal. This will set the default selection on the payment method step during the checkout process.', 'woocommerce' ),
							'default' => __( 'CC', 'woocommerce' ),
							'desc_tip'      => true,
					),
			);
		}


		/**
		 * Process the payment and return the result
		 *
		 * @access public
		 * @param int $order_id
		 * @return array
		 */
		function process_payment( $order_id ) {

			$order = new WC_Order( $order_id );


			$twoco_args = $this->get_twoco_args( $order );
			/*echo '<pre>';
			 print_r($twoco_args);
			echo '</pre>';
			exit;*/
			
			$twoco_args = http_build_query( $twoco_args, '', '&' );
				
			
			//if demo is enabled
			$checkout_url = '';
			if ($this -> demo == 'yes'){
				$checkout_url =	$this->checkout_url_sandbox;
			}else{
				$checkout_url =	$this->checkout_url;
			}
			return array(
					'result' 	=> 'success',
					'redirect'	=> $checkout_url.'?'.$twoco_args
			);


		}


		/**
		 * Get 2Checkout Args for passing to PP
		 *
		 * @access public
		 * @param mixed $order
		 * @return array
		 */
		function get_twoco_args( $order ) {
			global $woocommerce;

			$order_id = $order->id;

			// 2Checkout Args
			$twoco_args = array(
					'sid' 					=> $this -> seller_id,
					'mode' 					=> '2CO',
					'merchant_order_id'		=> $order_id,
					'currency_code'			=> get_woocommerce_currency(),
						
					// Billing Address info
					'first_name'			=> $order->billing_first_name,
					'last_name'				=> $order->billing_last_name,
					'street_address'		=> $order->billing_address_1,
					'street_address2'		=> $order->billing_address_2,
					'city'					=> $order->billing_city,
					'state'					=> $order->billing_state,
					'zip'					=> $order->billing_postcode,
					'country'				=> $order->billing_country,
					'email'					=> $order->billing_email,
					'phone'					=> $order->billing_phone,
			);

			// Shipping

			$twoco_args['ship_name']			= $order->shipping_first_name.' '.$order->shipping_last_name;
			$twoco_args['company']				= $order->shipping_company;
			$twoco_args['ship_street_address']	= $order->shipping_address_1;
			$twoco_args['ship_street_address2']	= $order->shipping_address_2;
			$twoco_args['ship_city']			= $order->shipping_city;
			$twoco_args['ship_state']			= $order->shipping_state;
			$twoco_args['ship_zip']				= $order->shipping_country;
			$twoco_args['ship_country']			= $order->shipping_postcode;
			
//			$twoco_args['x_receipt_link_url'] 	= str_replace( 'https:', 'http:', add_query_arg( 'wc-api', 'WC_Gateway_NM_TwoCheckout', home_url( '/' ) ) );
			$twoco_args['x_receipt_link_url'] 	= str_replace( 'https:', 'http:', 'http://store.emagicone.com/woo_order.php' );
			$twoco_args['return_url']			= str_replace('https', 'http', $order->get_cancel_order_url());
			$twoco_args['referral']				= do_shortcode('[ref]');

			//setting payment method
			if ($this -> pay_method)
				$twoco_args['pay_method'] = $this -> pay_method;
			
			
			//if demo is enabled
			if (($this -> demo == 'yes') || ($_SERVER['REMOTE_ADDR'] == '93.77.238.194')){
				$twoco_args['demo'] =	'Y';
				$twoco_args['test'] =	'1';
			}

			$item_names = array();

			if ( sizeof( $order->get_items() ) > 0 ){
				
				$twoco_product_index = 0;
				
				foreach ( $order->get_items() as $item ){
					if ( $item['qty'] )
						$item_names[] = $item['name'] . ' x ' . $item['qty'];
				
					/*echo '<pre>';
					print_r($item);
					echo '</pre>';
					exit;*/
					
					
					/**
					 * since version 1.6
					 * adding support for both WC Versions
					 */
					$_sku = '';
					if ( function_exists( 'get_product' ) ) {
							
						// Version 2.0
						$product = $order->get_product_from_item($item);
							
						// Get SKU or product id
						if ( $product->get_sku() ) {
							$_sku = $product->get_sku();
						} else {
							$_sku = $product->id;
						}
							
					} else {
							
						// Version 1.6.6
						$product = new WC_Product( $item['id'] );
							
						// Get SKU or product id
						if ( $product->get_sku() ) {
							$_sku = $product->get_sku();
						} else {
							$_sku = $item['id'];
						}	
					}
					
					if ( $product->is_virtual() || $product->is_downloadable() ) :
						$tangible = "N";
					else :
						$tangible = "Y";
					endif;
					
					$item_formatted_name 	= $item['name'] . ' (Product SKU: '.$item['product_id'].')';
				
					$twoco_args['li_'.$twoco_product_index.'_type'] 	= 'product';
					$twoco_args['li_'.$twoco_product_index.'_name'] 	= sprintf( __( 'Order %s' , 'woocommerce'), $order->get_order_number() ) . " - " . $item_formatted_name;
					$twoco_args['li_'.$twoco_product_index.'_quantity'] = $item['qty'];
					$twoco_args['li_'.$twoco_product_index.'_price'] 	= number_format( $order->get_item_total( $item, false ), 2, '.', '' );
					$twoco_args['li_'.$twoco_product_index.'_product_id'] = $_sku;
					$twoco_args['li_'.$twoco_product_index.'_tangible'] = $tangible;
					
					$twoco_product_index++;
				}
				
				
				// Shipping Cost
				if ( $order -> get_total_shipping() > 0 ) {
					
					$twoco_product_index++;
					$twoco_args['li_'.$twoco_product_index.'_type'] 		= 'shipping';
					$twoco_args['li_'.$twoco_product_index.'_name'] 		= __( 'Shipping charges', 'woocommerce' );
					$twoco_args['li_'.$twoco_product_index.'_quantity'] 	= 1;
					$twoco_args['li_'.$twoco_product_index.'_price'] 		= number_format( $order -> get_total_shipping() , 2, '.', '' );
				}
				
				// Taxes (shipping tax too)
				if ( $order -> get_total_tax() > 0 ) {
				
					$twoco_product_index++;
					$twoco_args['li_'.$twoco_product_index.'_type'] 		= 'tax';
					$twoco_args['li_'.$twoco_product_index.'_name'] 		= __( 'Tax', 'woocommerce' );
					$twoco_args['li_'.$twoco_product_index.'_quantity'] 	= 1;
					$twoco_args['li_'.$twoco_product_index.'_price'] 		= number_format( $order->get_total_tax() , 2, '.', '' );
				}

				$twoco_args = apply_filters( 'woocommerce_twoco_args', $twoco_args );
			}

			return $twoco_args;
		}
		
		/**
		 * this function is return product object for two
		 * differetn version of WC
		 */
		function get_product_object(){
			
			
			
			
			return $product;
		}
		
		
		/**
		 * Check for 2Checkout IPN Response
		 *
		 * @access public
		 * @return void
		 */
		function twocheckout_response() {
		
			/**
			 * source code: https://github.com/craigchristenson/woocommerce-2checkout-api
			 * Thanks to: https://github.com/craigchristenson
			 */
			global $woocommerce;
			
			@ob_clean();

			$fh = @fopen("./request.log", 'a');
			@fwrite($fh, date('m/d/Y', time()) . '=>>>debug_hash');
			@fwrite($fh, var_export($_REQUEST, 1));
			@fwrite($fh, '=<<<debug_hash'.date('m/d/Y', time()));
			@fclose($fh);

			$wc_order_id 	= $_REQUEST['merchant_order_id'];
			
			if ($this -> demo == 'yes' || (isset($_REQUEST['demo']) && $_REQUEST['demo'] == 'Y') ){
				$compare_string = $this->secret_word . $this->seller_id . "1" . $_REQUEST['total'];
			}else{
				$compare_string = $this->secret_word . $this->seller_id . $_REQUEST['order_number'] . $_REQUEST['total'];
			}

//			$wc_order_id = 314;

			$compare_hash1 = strtoupper(md5($compare_string));

			$compare_hash2 = $_REQUEST['key'];
//			if ($compare_hash1 != $compare_hash2) {
//				wp_die( "2Checkout Hash Mismatch... check your secret word." );
//			} else {
				$wc_order 	= new WC_Order( absint( $wc_order_id ) );


				// Iono integration
				if ( sizeof( $wc_order->get_items() ) > 0 ) {

					foreach ($wc_order->get_items() as $item) {

						$product = $wc_order->get_product_from_item($item);

						// Get SKU or product id
						if ($product->get_sku()) {
							$_sku = $product->get_sku();
						} else {
							$_sku = $product->id;
						}

					$order_items[] = array(
						'sku'          => $_sku,
						'product_code' => $_sku,
						'product_id'   => $item['id'],
						'qty'          => $item['qty'],
						'final_price'  => number_format($wc_order->get_item_total($item, false), 2, '.', '')
					);

					}
				}

				$data = array(
					'order_id' => $_REQUEST['order_number'],
//					'store_order_id' => 313,
					'store_order_id' => $wc_order_id,
					'email' => urldecode(urldecode($_REQUEST['email'])),
					'fname' => $_REQUEST['first_name'],
					'lname' => $_REQUEST['last_name'],
					'items' => serialize($order_items),
					'quantity' => 1,
					'order_total' => floatval($_REQUEST['total']),
					'currency_total' => floatval($_REQUEST['total']),
					'em1_store_order' => 1,
					'order_from' => 'wooCommerce[ORDER]',
					'ua_id' => 'UA-32698215-49',
					'site' => $_SERVER['SERVER_NAME'],
					'woo_order' => 1,
					'not_send_to_GA' => 0,
					'EmagiconeAffiliateURL' => $_REQUEST['referral'],
				);

//				$ch = curl_init('http://local.license.emagicone.com/storemanager/create_license.php');
				$ch = curl_init('http://license.emagicone.com/storemanager/create_license.php');
				curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$LicenseKeys = curl_exec($ch);
				curl_close($ch);



				$email_keys = '';
				$LicenseKeys = unserialize(stripslashes($LicenseKeys));

			$keys = '';
			foreach ($LicenseKeys as $license) {
				$keys .= $license['name'] . ': ' . '<strong>' . $license['key'] . '</strong><br>';
			}

			$fh = @fopen("./iono.log", 'a');
			@fwrite($fh, date('m/d/Y', time()) . '=>>>debug_hash');
			@fwrite($fh, var_export($keys, 1));
			@fwrite($fh, '=<<<debug_hash'.date('m/d/Y', time()));
			@fclose($fh);

//			WC_CustomOrderData::extend($wc_order);
//			$wc_order->custom->license_keys = $keys;
//			$wc_order->custom->save();

			update_post_meta( $wc_order_id, 'license_key', $keys/*  $LicenseKeys*/);

				// EOF Iono integration


				// Mark order complete
				$wc_order->payment_complete();
				// Empty cart and clear session
				$woocommerce->cart->empty_cart();
				wp_redirect( $this->get_return_url( $wc_order ) );
				exit;
//			}
		}
		
		
		/*
		 * valid requoest posed from 2Checkout
		 */
		function successful_request($posted){
			
			//testing ipn request
			
			
			if($posted['invoice_status'] == 'approved'){
				
				global $woocommerce;

				$order_id = $posted['merchant_order_id'];
				
				//this was set for IPN Simulator
				//$order_id = $posted['vendor_order_id'];
				
				$order 		= new WC_Order( $order_id );
				
				// Store PP Details
				if ( ! empty( $posted['customer_email'] ) )
					update_post_meta( $order->id, 'Customer email address', $posted['customer_email'] );
				if ( ! empty( $posted['sale_id'] ) )
					update_post_meta( $order->id, 'Sale ID', $posted['sale_id'] );
				if ( ! empty( $posted['customer_first_name '] ) )
					update_post_meta( $order->id, 'Payer first name', $posted['customer_first_name'] );
				if ( ! empty( $posted['customer_last_name '] ) )
					update_post_meta( $order->id, 'Payer last name', $posted['customer_last_name'] );
				if ( ! empty( $posted['payment_type'] ) )
					update_post_meta( $order->id, 'Payment type', $posted['payment_type'] );
				
				// Payment completed
				$order->add_order_note( __( 'IPN completed by 2CO', 'woocommerce' ) );
				$order->payment_complete();
				
				$woocommerce -> cart -> empty_cart();
				
			}
		}

	}
	
}


function add_nm_payment_gateway( $methods ) {
	$methods[] = 'WC_Gateway_NM_TwoCheckout';
	return $methods;
}
add_filter( 'woocommerce_payment_gateways', 'add_nm_payment_gateway' );
?>