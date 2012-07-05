<?php

	/**
	 * Represents a form section. 
	 * Form sections have a title and description.
	 * Objects of this class are created by {@link Db_ActiveRecord::add_form_custom_area() add_form_custom_area()} method 
	 * the {@link Db_ActiveRecord} class. Form area contents should be defined in
	 * a partial with name _form_section_<em>id</em>.htm in the controller's views directory,
	 * where <em>id</em> is an area identifier.
	 * @documentable
	 * @author LemonStand eCommerce Inc.
	 * @package core.classes
	 */
	class Db_FormCustomArea extends Db_FormElement
	{
		/**
		 * @var string Specifies the area identifier.
		 * @documentable
		 */
		public $id;
		
		public function __construct($id)
		{
			$this->id = $id;
		}
	}

?>