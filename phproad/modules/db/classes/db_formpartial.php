<?php

	/**
	 * Represents a form partial. 
	 * Objects of this class are created by {@link Db_ActiveRecord::add_form_partial() add_form_partial()} method 
	 * the {@link Db_ActiveRecord} class.
	 * @documentable
	 * @author LemonStand eCommerce Inc.
	 * @package core.classes
	 */
	class Db_FormPartial extends Db_FormElement
	{
		/**
		 * @var string Specifies the partial file path.
		 * @documentable
		 */
		public $path;
		
		public function __construct($path)
		{
			$this->path = $path;
		}
	}

?>