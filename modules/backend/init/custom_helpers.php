<?php

	/*
	 * Backend module events object
	 */

	Backend::$events = new Backend_Events();

	/*
	 * Load and initialize modules
	 */

	Core_ModuleManager::listModules();
	
	Backend::$events->fireEvent('core:onInitialize');

	/**
	 * Custom functions
	 */

	/**
	 * Outputs a backend button.
	 * This function is shortcut for Backend_Html::button helper method.
	 */
	function backend_button($caption, $attributes = array(), $ajaxHandler=null, $ajaxParams = null, $formElement = null)
	{
		return Backend_Html::button($caption, $attributes, $ajaxHandler, $ajaxParams, $formElement);
	}
	
	/**
	 * Outputs a backend AJAX button.
	 * This function is shortcut for Backend_Html::ajaxButton helper method.
	 */
	function backend_ajax_button($caption, $ajaxHandler, $attributes = array(), $ajaxParams = null)
	{
		return Backend_Html::ajaxButton($caption, $ajaxHandler, $attributes, $ajaxParams);
	}
	
	/**
	 * Outputs a control panel button.
	 * This function is shortcut for Backend_Html::ctr_button helper method.
	 */
	function backend_ctr_button($caption, $button_class, $attributes = array(), $ajaxHandler=null, $ajaxParams = null, $formElement = null)
	{
		return Backend_Html::ctr_button($caption, $button_class, $attributes, $ajaxHandler, $ajaxParams, $formElement);
	}
	
	/**
	 * Outputs a control panel AJAX button.
	 * This function is shortcut for Backend_Html::ctr_ajaxButton helper method.
	 */
	function backend_ctr_ajax_button($caption, $button_class, $ajaxHandler, $attributes = array(), $ajaxParams = null)
	{
		return Backend_Html::ctr_ajaxButton($caption, $button_class, $ajaxHandler, $attributes, $ajaxParams);
	}
	
	/**
	 * Returns an Administration Area URL.
	 * Administration Area URL depends on the <em>BACKEND_URL</em> {@link http://lemonstandapp.com/docs/lemonstand_configuration_options/ configuration parameter}.
	 * This function prepends the Administration Area ULR to the argument value. Always use this function for creating
	 * links in the back-end. Example:
	 * <pre><a href="<?= url('/shop/orders') ?>">Return to the Order List</a></pre>
	 * @documentable
	 * @param string $url Specifies an URL.
	 * @return Returns an URL converted to the Administration Area URL.
	 * @author LemonStand eCommerce Inc.
	 * @package core.functions
	 */
	function url($url)
	{
		return Backend_Html::url($url);
	}
	
	/**
	 * Returns word "even" each even call for a specified counter.
	 * Example: <tr class="<?= zebra('customer') ?>">
	 * This function is shortcut for Backend_Html::zebra helper method.
	 */
	function zebra($counterName)
	{
		return Backend_Html::zebra($counterName);
	}
	
	/**
	 * Returns module version string
	 * @param string $moduleId Specifies a module identifier
	 * @return string
	 */
	function module_build($moduleId)
	{
		return Core_Version::getModuleVersionCached($moduleId);
	}
	
	/**
	 * Returns the onClick handler for redirecting a browser to a specified URL
	 * Example: <td <?= click_link('http://www.my-site.com') ?>>
	 * This function is shortcut for Backend_Html::click_link helper method.
	 */
	function click_link($url)
	{
		return Backend_Html::click_link($url);
	}
	
	/**
	 * Returns the onClick handler code for redirecting a browser to an URL
	 * which depends on whether the ALT key was pressed
	 * Example: <td onclick="<?= alt_click_link('http://www.my-site.com', 'http://www.my-site2.com') ?>">
	 * This function is shortcut for Backend_Html::alt_click_link helper method.
	 */
	function alt_click_link($url, $alt_url)
	{
		return Backend_Html::alt_click_link($url, $alt_url);
	}
?>