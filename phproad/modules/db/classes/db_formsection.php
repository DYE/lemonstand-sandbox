<?php

	/**
	 * Represents a form section. 
	 * Form sections have a title and description.
	 * Objects of this class are created by {@link Db_ActiveRecord::add_form_section() add_form_section()} method 
	 * the {@link Db_ActiveRecord} class.
	 * @documentable
	 * @author LemonStand eCommerce Inc.
	 * @package core.classes
	 */
	class Db_FormSection extends Db_FormElement
	{
		/**
		 * @var string Specifies the section title
		 * @documentable
		 */
		public $title;

		/**
		 * @var string Specifies the section description
		 * @documentable
		 */
		public $description;
		
		public function __construct($title, $description)
		{
			$this->title = $title;
			$this->description = $description;
		}
	}

?>