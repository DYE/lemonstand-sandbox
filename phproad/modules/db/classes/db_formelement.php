<?php

	/**
	 * Base class for all form elements
	 * @documentable
	 * @author LemonStand eCommerce Inc.
	 * @package core.classes
	 */
	class Db_FormElement
	{
		/**
		 * @var string Specifies the form tab name.
		 * @documentable
		 */
		public $tab;
		
		/**
		 * @var boolean Makes the element invisible in the form preview.
		 * @documentable
		 */
		public $noPreview = false;

		/**
		 * @var boolean Hides the element from forms.
		 * @documentable
		 */
		public $noForm = false;
		
		/**
		 * @var integer Specifies the element position in a form.
		 * By default this parameter is assigned automatically.
		 * @documentable
		 */
		public $sortOrder = null;

		/**
		 * @var boolean Determines whether the element should be placed to the collapsable form area.
		 * @documentable
		 */
		public $collapsable = false;

		/**
		 * Specifies a caption of the tab to place the field to.
		 * If you use tabs, you should call this method for all form field in the model.
		 * @documentable
		 * @param string $caption Specifies the tab caption.
		 * @return Db_FormElement Returns the updated form element object.
		 */
		public function tab($tabCaption)
		{
			$this->tab = $tabCaption;
			return $this;
		}
		
		/**
		 * Hides the element from the form preview.
		 * @documentable
		 * @return Db_FormElement Returns the updated form element object.
		 */
		public function noPreview()
		{
			$this->noPreview = true;
			return $this;
		}

		/**
		 * Hides the element from the form.
		 * @documentable
		 * @return Db_FormElement Returns the updated form element object.
		 */
		public function noForm()
		{
			$this->noForm = true;
			return $this;
		}
		
		/**
		 * Sets the element position on the form. 
		 * For elements without any position  specified, the position is calculated automatically, 
		 * basing on the {@link Db_ActiveRecord::add_form_field() add_form_field()} method call order. 
		 * The first element sort order value is 10, second's - 20 and so on.
		 * @documentable
		 * @param integer $value Specifies the element sort order.
		 * @return Db_FormElement Returns the updated form element object.
		 */
		public function sortOrder($value)
		{
			$this->sortOrder = $value;
			return $this;
		}
		
		/**
		 * Places the element to the form or tab collapsable area.
		 * @documentable
		 * @param boolean $value Determines whether the element should be placed to the collapsable area.
		 * @return Db_FormElement Returns the updated form element object.
		 */
		public function collapsable($value = true)
		{
			$this->collapsable = $value;
			return $this;
		}
	}

?>