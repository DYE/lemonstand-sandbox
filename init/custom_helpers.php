<?php

	/*
	 * String helpers
	 */

	/**
	 * Returns string in HTML-safe format, converting all applicable characters to HTML entities. 
	 * Always use this function to output data created by users.
	 * The following code outputs a product name.
	 * <pre>Product: <?= h($product->name) ?></pre>
	 * @documentable
	 * @package core.helpers
	 * @author LemonStand eCommerce Inc.
	 * @param string $string specifies a string to process.
	 * @return string returns the processed string.
	 */
	function h($str)
	{
		return Phpr_Html::encode($str);
	}

	function plainText($Str)
	{
		return Phpr_Html::plainText($Str);  
	}

	/*
	 * Date helpers
	 */

	/**
	 * @return string
	 */
	function displayDate( $Date, $Format = '%x' )
	{
		return Phpr_Date::display( $Date, $Format );
	}
	
	/**
	 * @return Phpr_DateTime
	 */
	function gmtNow()
	{
		return Phpr_DateTime::gmtNow();
	}
	
	/*
	 * Other helpers
	 */

	/**
	 * Writes a message to the trace log file.
	 * The message can be either string or any other PHP type - array, object, etc. 
	 * The default location of the trace log file is logs/info.txt. Example:
	 * <pre>
	 *   traceLog('Hello!'); // Writes a string to the log file.
	 * </pre>
	 * @documentable
	 * @package core.helpers
	 * @author LemonStand eCommerce Inc.
	 * @param mixed $message A message to write.
	 * @param mixed $listener The trace log listener. 
	 * The default listener writes to logs/info.txt file.
	 */
	function traceLog($Str, $Listener = 'INFO')
	{
		if (Phpr::$traceLog)
			Phpr::$traceLog->write($Str, $Listener);
	}
	
	function flash()
	{
		return Backend_Html::flash();
	}

	/**
	 * Returns a named POST parameter value.
	 * Returns an element with a name specified in the first parameter from the <em>$_POST</em> array. 
	 * If the element is not found, returns default value, optionally specified in the second parameter. 
	 * This function is useful for form processing.
	 * The following code extracts the <em>name</em> POST element and if there is no such en element, uses string "John".
	 * <pre>$name = post('name', 'John');</pre>
	 * This function is a shortcut for {@link Phpr_Request::post()} method.
	 * @documentable
	 * @package core.helpers
	 * @author LemonStand eCommerce Inc.
	 * @see post_array_item()
	 * @param $name specifies the POST element name.
	 * @param mixed $default specifies a default value. Optional parameter, the default value is NULL.
	 * @return mixed Returns the POST array value or the default value.
	 */
	function post($name, $default = null)
	{
		return Phpr::$request->post($name, $default);
	}
	
	/**
	 * Finds an array in the <em>POST</em> data then finds and returns an element inside this array.
	 * If the array or the element do not exist, returns <em>NULL</em> or a value specified in the $default parameter.
	 * 
	 * This function is useful for extracting form field values if you use array notation for the form input element names.
	 * For example, if you have a form with the following fields
	 * <pre>
	 * <input type="text" name="customer_form[first_name]">
	 * <input type="text" name="customer_form[last_name]">
	 * </pre>
	 * you can extract the first name field value with the following code:
	 * <pre>$first_name = post_array_item('customer_form', 'first_name')</pre>
	 * This function is a shortcut for {@link Phpr_Request::post_array_item()} method.
	 * @documentable
	 * @package core.helpers
	 * @author LemonStand eCommerce Inc.
	 * @see post()
	 * @param string $array_name specifies the array element name in the POST data.
	 * @param string $name specifies the array element key in the first array.
	 * @param mixed $default specifies a default value.
	 * @return mixed returns the found array element value or the default value.
	 */
	function post_array_item($array_name, $name, $default = null)
	{
		return Phpr::$request->post_array_item($array_name, $name, $default);
	}
	
	/*
	 * Form helpers
	 */
	
	/**
	 * Returns <em>selected="selected"</em> string if the parameter values are equal. 
	 * Use this function for programming OPTION elements inside SELECT tag.
	 * The following code creates a select element for selecting a country state.
	 * <pre>
	 * <select>
	 *   <? foreach ($states as $state): ?>
	 *     <option <?= option_state($current_state, $state->id) ?> value="<?= h($state->id) ?>"><?= h($state->name) ?></option>
	 *   <? endforeach ?>
	 * </select>
	 * </pre>
	 * @documentable
	 * @package core.helpers
	 * @see checkbox_state()
	 * @see radio_state()
	 * @author LemonStand eCommerce Inc.
	 * @param mixed $current_value specifies a value of current OPTION element.
	 * @param mixed $selected_value specifies a selected value.
	 * @return string returns <em>selected="selected"</em> string or empty string.
	 */
	function option_state($current_value, $selected_value)
	{
		return PHpr_Form::optionState( $current_value, $selected_value );
	}
	
	/**
	 * Returns <em>checked="checked"</em> string if the parameter value is TRUE. 
	 * Use this function to program form checkboxes.
	 * The following code example creates a checkbox with name "extra_option". The checkbox is automatically checked if the POST array 
	 * contains 'extra_option' element and its value is TRUE.
	 * <pre><input name="extra_option" <?= checkbox_state(post('extra_option')) ?> value="1" type="checkbox"/></pre>
	 * @documentable
	 * @package core.helpers
	 * @see option_state()
	 * @see radio_state()
	 * @author LemonStand eCommerce Inc.
	 * @param boolean $value Specifies a current checkbox state.
	 * @return string returns <em>checked="checked"</em> string or empty string.
	 */
	function checkbox_state($value)
	{
		return Phpr_Form::checkboxState($value);
	}
	
	/**
	 * Returns <em>checked="checked"</em> string if the parameter value is TRUE. 
	 * Use the radio_state() function for programming
	 * radio buttons. The following code creates two radio button elements for selecting a color.
	 * <pre>
	 * <input name="color" <?= radio_state(post('color') == 'red') ?> value="red" type="checkbox"/>
	 * <input name="color" <?= radio_state(post('color') == 'blue') ?> value="blue" type="checkbox"/>
	 * </pre>
	 * @documentable
	 * @package core.helpers
	 * @see checkbox_state()
	 * @see option_state()
	 * @author LemonStand eCommerce Inc.
	 * @param boolean $value specifies a current radio button value.
	 * @return string returns <em>checked="checked"</em> string or empty string.
	 */
	function radio_state($value)
	{
		return Phpr_Form::checkboxState($value);
	}
	
	/*
	 * URL helpers
	 */
	
	/**
	 * Returns file URL relative to the LemonStand root.
	 * The function makes links independent of the actual LemonStand installation directory name, whenever it 
	 * is installed into a subdirectory or to a domain root directory. It is highly recommended to use 
	 * this function for creating links, because it can save you lots of time if you decide to move
	 * your LemonStand installation from a subdirectory to the domain root directory. <span class="note">Using this function 
	 * is required in {@link http://lemonstandapp.com/docs/creating_themes_for_marketplace LemonStand Marketplace themes}.</span>
	 * For example, if you installed LemonStand into the shop subdirectory of <em>http://my_host.com</em> domain, the following call
	 * will return <em>/shop/cart</em> URL:
	 * <pre>root_url('/cart')</pre>
	 * If you move LemonStand installation to the domain root directory the same function call will return <em>/cart</em> URL, so that you
	 * do not need to update your links manually.
	 * @documentable
	 * @package cms.helpers
	 * @author LemonStand eCommerce Inc.
	 * @see site_url()
	 * @param string $value Specifies the URL to process.
	 * @param string $add_host_name_and_protocol Indicates whether the URL should contain the host name and protocol. 
	 * @param string $protocol Optional HTTP protocol name to override the actual protocol name.
	 * @return string Returns an URL of a specified resource relative to the LemonStand domain root.
	 */
	function root_url($value = '/', $add_host_name_and_protocol = false, $protocol = null)
	{
		return Phpr_Url::root_url($value, $add_host_name_and_protocol, $protocol);
	}