<?php
//phpinfo();
/**
 * Created by JetBrains PhpStorm.
 * User: jarchik
 * Date: 22.02.13
 * Time: 16:39
 * To change this template use File | Settings | File Templates.
 */
// var_dump($_SERVER['REMOTE_ADDR']);die();
error_reporting(E_ALL);
//foreach($items as $itemId => $item) {
//    $order_items[] = array(
//        //'sku' => $sku,
//        'product_code' => $product_code,
//        //'product_id' => $item->getProductId(),
//        'qty' => $item->getQtyToInvoice()
//    );
//}

//$order_items = array('0' => array('product_code' => 'sm_magento_pro_update_service_6', 'qty' => '3'));
// $order_items = array('0' => array('sku' => 'mag_storemanager_basic_additional', 'qty' => '1', 'product_code' => 'mag_storemanager_basic_additional'));
$order_items = array('0' => array('sku' => 'prestashop_sm_standard', 'qty' => '1', 'product_code' => 'prestashop_sm_standard', 'product_id' => '134', 'qty' => 1));



    $data = array(
      'wc-api' => 'WC_Gateway_NM_TwoCheckout',
      'p' => '',
      'middle_initial' => '',
      'li_0_name' => 'Order 330 - Store Manager for WooCommerce Primary License (Product SKU%3A 290)',
      'sid' => '1765515',
      'test' => '1',
      'ship_zip' => 'GB',
      'key' => 'B9E4C364DAAF23063B85FE0C6EE07CE4',
      'state' => '',
      'email' => 'jarchik%40kommy.net',
      'li_0_type' => 'product',
      'order_number' => '105671149890',
      'currency_code' => 'USD',
      'lang' => 'en',
      'ship_state' => '',
      'invoice_id' => '105671149899',
      'li_0_price' => '119.00',
      'total' => '119.00',
      'ship_street_address2' => '',
      'credit_card_processed' => 'Y',
      'zip' => 'WC2E 9RZ',
      'ship_name' => 'Yaroslav Lvivsky',
      'li_0_quantity' => '1',
      'ship_method' => '',
      'cart_weight' => '0',
      'fixed' => 'Y',
      'ship_country' => 'UKR',
      'last_name' => 'Lvivsky',
      'li_0_product_id' => 'sm_woocm_primary',
      'street_address' => 'Baker str%2C 221B',
      'city' => 'London',
      'li_0_tangible' => 'N',
      'ship_city' => 'London',
      'company' => '',
      'ip_country' => 'Ukraine',
      'country' => 'GBR',
      'merchant_order_id' => '330',
      'li_0_description' => '',
      'ship_street_address' => 'Baker str%2C 221B',
      'demo' => 'Y',
      'pay_method' => 'CC',
      'cart_tangible' => 'N',
      'phone' => '312313212 ',
      'street_address2' => '',
      'x_receipt_link_url' => 'http%3A%2F%2Fstore.emagicone.com%2Fwoo_order.php',
      'first_name' => 'Yaroslav',
      'card_holder_name' => 'Yaroslav Lvivsky',
    );




/*$data  = array (
    'order_id' => '105588165540j1',
    'store_order_id' => '100001876j1',
    'email' => 'yaroslav@emagicone.com',
    'fname' => 'Philip',
    'lname' => 'Susman',
    'items' => 'a:2:{i:0;a:5:{s:3:"sku";s:58:"prestashop_storemanager|prestashop_storemanager_additional";s:12:"product_code";s:58:"prestashop_storemanager|prestashop_storemanager_additional";s:10:"product_id";s:2:"67";s:3:"qty";d:1;s:11:"final_price";s:6:"334.62";}i:1;a:5:{s:3:"sku";s:22:"plugin_ebay_prestashop";s:12:"product_code";s:11:"plugin_ebay";s:10:"product_id";s:2:"85";s:3:"qty";d:1;s:11:"final_price";s:6:"113.80";}}',
    'quantity' => '1',
    'order_total' => '448.88',
    'currency_total' => '448.88',
    'em1_store_order' => '1',
    'EmagiconeAffiliateURL' => 'https://store.emagicone.com/',
    'testj' => '1'
);*/



// $ch = curl_init('http://linux/licenses/storemanager/create_lic_test.php');
// $ch = curl_init('http://linux/licenses/storemanager/create_license.php');
$ch = curl_init('http://local.woocommerce-manager.com/?wc-api=WC_Gateway_NM_TwoCheckout&'. http_build_query($data));



//curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($ch, CURLOPT_COOKIE, 'XDEBUG_SESSION=PHPSTORM');

$LicenseKeys = curl_exec($ch);

curl_close($ch);
var_dump($LicenseKeys);
//die(':-<>');
?>