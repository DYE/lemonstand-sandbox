<?

	/**
	 * Returns a currency representation of a number.
	 * Returns a string containing a numeric value formatted as currency according the system currency settings. 
	 * You can change the system currency settings on System/Settings/Currency page. Default system currency 
	 * is USD and the default format is $10,000.00.
	 * 
	 * The following code outputs a product price.
	 * <pre>Price: <?= format_currency($product->price()) ?></pre>
	 * @documentable
	 * @package shop.helpers
	 * @author LemonStand eCommerce Inc.
	 * @param string $num specifies a value to format.
	 * @param integer $decimals specifies a number of decimal digits. Optional parameter, the default value is 2.
	 * @return string returns the formatted currency value.
	 */
	function format_currency($num, $decimals = 2)
	{
		return Shop_CurrencySettings::format_currency($num, $decimals);
	}
	
	/**
	 * Closes a session of a current customer and optionally redirects browser to a specified address. 
	 * Use this function to create the Logout page.
	 * The following code represents contents of a simplest logout page.
	 * <pre><? customer_logout('/'); ?></pre>
	 * @documentable
	 * @package shop.helpers
	 * @see http://lemonstandapp.com/docs/customer_login_and_logout/ Customer login and logout pages
	 * @author LemonStand eCommerce Inc.
	 * @param string $redirect specifies an URL to redirect the customer to.
	 */
	function customer_logout($redirect = null)
	{
		Phpr::$frontend_security->logout($redirect);
	}
	
	/**
	 * Returns the tax included label text. 
	 * Use this function if product prices on the store pages include tax and you want to let visitors know about it.
	 * You can configure the label text and behavior on the System/Settings/eCommerce Settings page, please see 
	 * {@link http://lemonstandapp.com/docs/configuring_lemonstand_for_tax_inclusive_environments/ Configuring LemonStand for tax inclusive environments}
	 * for details.
	 * The function returns the text which you specify in the <em>Tax included label</em> text field of the eCommerce Settings form in case if the visitor's 
	 * location matches a country and state specified in the configuration form. If a visitor's location 
	 * is not known, the {@link http://lemonstandapp.com/docs/configuring_the_shipping_parameters/ default shipping location} is used.
	 * 
	 * The following code outputs a tax included label next to a product price on the product details page:
	 * <pre>Price:<?= format_currency($product->price()) ?> <?= tax_incl_label() ?></pre>
	 * @documentable
	 * @package shop.helpers
	 * @author LemonStand eCommerce Inc.
	 * @see http://lemonstandapp.com/docs/configuring_lemonstand_for_tax_inclusive_environments/ Configuring LemonStand for tax inclusive environments
	 * @see http://lemonstandapp.com/docs/configuring_the_shipping_parameters/ Configuring the shipping parameters
	 * @see http://lemonstandapp.com/docs/payment_receipt_page/ Payment receipt page
	 * @see http://lemonstandapp.com/docs/order_details_page/ Order details page
	 * 
	 * @param Shop_Order $order - optional reference to the Shop_Order object. 
	 * Pass the order object into this parameter if an order is available, for example on the {@link http://lemonstandapp.com/docs/order_details_page/ Order Details} 
	 * or {@link http://lemonstandapp.com/docs/payment_receipt_page/ Receipt} pages.
	 * @return string Returns the tax included label text or NULL.
	 */
	function tax_incl_label($order = null)
	{
		$display_tax_included = Shop_CheckoutData::display_prices_incl_tax($order);
		if (!$display_tax_included)
			return null;
		
		$config = Shop_ConfigurationRecord::get();

		if (!$order)
		{
			$shipping_info = Shop_CheckoutData::get_shipping_info();
			$shipping_country_id = $shipping_info->country;
			$shipping_state_id = $shipping_info->state;
		} else
		{
			$shipping_country_id = $order->shipping_country_id;
			$shipping_state_id = $order->shipping_state_id;
		}
		
		if (!$config->tax_inclusive_country_id)
			return $config->tax_inclusive_label;

		if ($config->tax_inclusive_country_id != $shipping_country_id)
			return null;
			
		if (!$config->tax_inclusive_state_id)
			return $config->tax_inclusive_label;
			
		if ($config->tax_inclusive_state_id != $shipping_state_id)
			return null;

		return $config->tax_inclusive_label;
	}

?>