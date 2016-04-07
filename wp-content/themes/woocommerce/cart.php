<div class="container">
<?php 
global $woocommerce;

	// get cart quantity
	$qty = $woocommerce->cart->get_cart_contents_count();

	// get cart total
	$total = $woocommerce->cart->get_cart_total();

	// get cart url
	$cart_url = $woocommerce->cart->get_cart_url();

	// if multiple products in cart
	if($qty>1)
	      echo '<a class="header-cart" href="'.$cart_url.'">'.$qty.' | '.$total.'</a>';

	// if single product in cart
	if($qty==1)
	      echo '<a class="header-cart"  href="'.$cart_url.'">1 | '.$total.'</a>';
?>
</div>