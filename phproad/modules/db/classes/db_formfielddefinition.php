<?php

	define('frm_text', 'text');
	define('frm_password', 'password');
	define('frm_dropdown', 'dropdown');
	define('frm_autocomplete', 'autocomplete');
	define('frm_radio', 'radio');
	define('frm_checkbox', 'checkbox');
	define('frm_checkboxlist', 'checkboxlist');

	define('frm_textarea', 'textarea');
	define('frm_html', 'html');
	define('frm_code_editor', 'code_editor');
	define('frm_grid', 'grid');

	define('frm_datetime', 'datetime');
	define('frm_date', 'date');
	define('frm_time', 'time');
	
	define('frm_onoffswitcher', 'on_off_switcher');
	define('frm_record_finder', 'recordfinder');

	define('frm_file_attachments', 'file_attachments');
	define('frm_widget', 'widget');

	/**
	 * Represents a model form field definition. 
	 * Objects of this class are used for defining form field properties in models.
	 * {@link Db_ListBehavioer List Behavior} use data from 
	 * form definition objects to display fields in forms.
	 *
	 * Almost every class property has a method with a matching name and usually properties are not used directly.
	 * Class methods can be called as a chain:
	 * <pre>$this->add_form_field('price_tiers')->tab('Pricing')->renderAs('price_tiers');</pre>
	 * 
	 * @documentable
	 * @author LemonStand eCommerce Inc.
	 * @package core.classes
	 */
	class Db_FormFieldDefinition extends Db_FormElement
	{
		/**
		 * @var string Specifies the database column or relation name.
		 * @documentable
		 */
		public $dbName;
		
		/**
		 * @var string Specifies the form side the element should be placed to.
		 * Supported values are <em>left</em>, <em>right</em>, <em>full</em>.
		 * @documentable
		 */
		public $formSide;
		
		/**
		 * @var string Specifies the field render mode.
		 * See {@link Db_FormFieldDefinition::renderAs() renderAs() method} for details
		 * about supported render modes.
		 * @documentable
		 */
		public $renderMode = null;
		
		/**
		 * @var string Specifies the element comment.
		 * Comments are displayed on the form below or above the element.
		 * See {@link Db_FormFieldDefinition::comment() comment() method} for details.
		 * @documentable
		 */
		public $comment;
		
		/**
		 * @var string Specifies the comment position.
		 * Supported values are <em>above</em> or <em>below</em>.
		 * See {@link Db_FormFieldDefinition::comment() comment() method} for details.
		 * @documentable
		 */
		public $commentPosition;
		
		/**
		 * @var boolean Determines whether the comment contains HTML tags.
		 * See {@link Db_FormFieldDefinition::comment() comment() method} for details.
		 * @documentable
		 */
		public $commentHTML = false;

		/**
		 * @var string Specifies the element comment for the form preview.
		 * By default the preview comment matches the regular form comment.
		 * See {@link Db_FormFieldDefinition::previewComment() previewComment() method} for details.
		 * @documentable
		 */
		public $previewComment = null;
		
		/**
		 * @var string Specifies a size of textarea fields. 
		 * See {@link Db_FormFieldDefinition::size() size() method} for details.
		 * @documentable
		 */
		public $size;
		
		/**
		 * @var string Specifies a label corresponding an empty option for drop-down elements. 
		 * See {@link Db_FormFieldDefinition::emptyOption() emptyOption() method} for details.
		 * @documentable
		 */
		public $emptyOption = null;

		/**
		 * @var string Specifies a text to display in multi-relation fields in case if no options were selected. 
		 * @documentable
		 */
		public $noOptions = null;
		
		/**
		 * @var string Specifies the common model's method name responsible for returning a list of options for a multi-option field. 
		 * See {@link Db_FormFieldDefinition::optionsMethod() optionsMethod() method} for details.
		 * @documentable
		 */
		public $optionsMethod = null;
		
		/**
		 * @var string Specifies the common model's method name responsible for returning option state for a multi-option field. 
		 * See {@link Db_FormFieldDefinition::optionStateMethod() optionStateMethod() method} for details.
		 * @documentable
		 */
		public $optionStateMethod = null;

		/**
		 * @var string Specifies a SQL expression for filtering reference-type fields.
		 * See {@link Db_FormFieldDefinition::referenceFilter() referenceFilter() method} for details.
		 * @documentable
		 */
		public $referenceFilter = null;
		
		/**
		 * @var string Specifies a SQL expression for ordering options in reference-type fields.
		 * See {@link Db_FormFieldDefinition::referenceSort() referenceSort() method} for details.
		 * @documentable
		 */
		public $referenceSort = null;
		
		/**
		 * @var string Specifies a SQL expression for fetching option descriptions in reference-type fields.
		 * See {@link Db_FormFieldDefinition::referenceDescriptionField() referenceDescriptionField() method} for details.
		 * @documentable
		 */
		public $referenceDescriptionField = null;
		
		/**
		 * @var string Specifies value corresponding the <em>on</em> state of a checkbox field.
		 * @documentable
		 */
		public $checkboxOnState = 1;
		
		/**
		 * @var string Specifies prompt message for a file attachment field.
		 * @documentable
		 */
		public $addAttachmentLabel = 'Add file';
		
		/**
		 * @var string Specifies a message to display in a file attachment field if there are no files attached.
		 * @documentable
		 */
		public $noAttachmentsLabel = 'No files';
		
		/**
		 * @var integer Specifies thumbnail size (both width and height) for an image field.
		 * @documentable
		 */
		public $imageThumbSize = 100;
		
		/**
		 * @var boolean Determines whether the relation preview is available for a relation field on the preview form.
		 * @documentable
		 */
		public $previewNoRelation = false;
		
		/**
		 * @var string Specifies a text to be displayed for a relation field on the preview form when the field has no related records.
		 * @documentable
		 */
		public $relationPreviewNoOptions = 'No options were assigned';
		
		/**
		 * @var boolean Determines where options in a drop-down field should be HTML-encoded before they are displayed.
		 * @documentable
		 */
		public $optionsHtmlEncode = true;
		
		/**
		 * @var boolean Determines whether the field is disabled on the form.
		 * @documentable
		 */
		public $disabled = false;
		public $textareaServices = null;

		/**
		 * @var string Specifies CSS class to be applied to the field container element.
		 * @documentable
		 */
		public $cssClasses = null;
		
		/**
		 * @var string Specifies CSS class to be applied to the field LI element.
		 * @documentable
		 */
		public $cssClassName = null;
		
		/**
		 * @var string Specifies a render mode for a file attachment field.
		 * See {@link Db_FormFieldDefinition::renderFilesAs() renderFilesAs() method} for details.
		 * @documentable
		 */
		public $renderFilesAs = 'file_list';
		
		/**
		 * @var string Specifies language for a code editor field.
		 * See {@link Db_FormFieldDefinition::language() language() method} for details.
		 * @documentable
		 */
		public $language = 'html';
		
		/**
		 * @var array Defines column configuration for a grid field.
		 * See {@link Db_FormFieldDefinition::gridColumns() gridColumns() method} for details.
		 * @documentable
		 */
		public $gridColumns = array();

		/**
		 * @var array Defines configuration of a grid field.
		 * See {@link Db_FormFieldDefinition::gridSettings() gridSettings() method} for details.
		 * @documentable
		 */
		public $gridSettings = array();
		
		/**
		 * @var boolean Determines whether the element form label is invisible.
		 * @documentable
		 */
		public $noLabel = false;
		
		/**
		 * @var boolean Determines a text area content should be hidden in a form.
		 * @documentable
		 */
		public $hideContent = false;
		
		/**
		 * @var string Sets a base URL for file links for a file attachments field.
		 * @documentable
		 */
		public $fileDownloadBaseUrl = null;
		
		/**
		 * @var string Specifies a list of TinyMCE plugins for a HTML field.
		 * @documentable
		 */
		public $htmlPlugins = "paste,searchreplace,advlink,inlinepopups";

		/**
		 * @var string Specifies a list of TinyMCE toolbar #1 buttons for a HTML field.
		 * @documentable
		 */
		public $htmlButtons1 = "cut,copy,paste,pastetext,pasteword,separator,undo,redo,separator,link,unlink,separator,image,separator,bold,italic,underline,separator,formatselect,separator,bullist,numlist,separator,code";

		/**
		 * @var string Specifies a list of TinyMCE toolbar #2 buttons for a HTML field.
		 * @documentable
		 */
		public $htmlButtons2 = null;

		/**
		 * @var string Specifies a list of TinyMCE toolbar #3 buttons for a HTML field.
		 * @documentable
		 */
		public $htmlButtons3 = null;

		/**
		 * @var string Specifies URL of a CSS file to style content of a TinyMCE editor for a HTML field.
		 * The URL should be relative to LemonStand application root.
		 * @documentable
		 */
		public $htmlContentCss = '/phproad/resources/css/htmlcontent.css';
		
		/**
		 * @var string Specifies a list of TinyMCE block formats for a HTML field.
		 * @documentable
		 */
		public $htmlBlockFormats = 'p,address,pre,h1,h2,h3,h4,h5,h6';
		
		/**
		 * @var string Specifies a list of TinyMCE custom styles for a HTML field.
		 * @documentable
		 */
		public $htmlCustomStyles = null;
		
		/**
		 * @var string Specifies a list of TinyMCE font sizes for a HTML field.
		 * @documentable
		 */
		public $htmlFontSizes = null;
		
		/**
		 * @var string Specifies a list of TinyMCE font colors for a HTML field.
		 * @documentable
		 */
		public $htmlFontColors = null;
		
		/**
		 * @var string Specifies a list of TinyMCE background colors for a HTML field.
		 * @documentable
		 */
		public $htmlBackgroundColors = null;

		/**
		 * @var boolean Specifies whether TinyMCE "more colors" feature is available for a HTML field.
		 * @documentable
		 */
		public $htmlAllowMoreColors = true;

		/**
		 * @var string Specifies a list of TinyMCE valid elements for a HTML field.
		 * @documentable
		 */
		public $htmlValidElements = null;

		/**
		 * @var string Specifies a list of TinyMCE valid child elements for a HTML field.
		 * @documentable
		 */
		public $htmlValidChildElements = null;
		
		/**
		 * @var string Specifies whether new lines in the field value should be converted to line breaks in a form preview.
		 * @documentable
		 */
		public $nl2br = false;
		
		/**
		 * @var string Specifies name of a partial to render below the form field caption.
		 * @documentable
		 */
		public $titlePartial = null;
		
		/**
		 * @var string Specifies name of a partial to render instead of the normal form field markup.
		 * @documentable
		 */
		public $formElementPartial = null;
		
		/**
		 * @var string Specifies HTML help string to be displayed in a form preview. 
		 * @documentable
		 */
		public $previewHelp = null;
		
		/**
		 * @var string Specifies a tooltip text for the field comment. 
		 * @documentable
		 */
		public $commentTooltip = null;
		
		/**
		 * @var boolean Determines whether the HTML field should be full-width.
		 * @documentable
		 */
		public $htmlFullWidth = false;
		
		/**
		 * @var array Specifies rendering options for complex fields and widgets.
		 * See documentation for a specific field for details.
		 * @documentable
		 */
		public $renderOptions = array();
		
		/**
		 * @var string Specifies an URL for the field preview link.
		 * @documentable
		 */
		public $previewLink = null;
		
		/**
		 * @var string Specifies JavaScript function name to be executed when Save button is clicked in a HTML field.
		 * @documentable
		 */
		public $saveCallback = null;
		
		private $_model;
		private $_columnDefinition;
		
		public function __construct($model, $dbName, $side)
		{
			$modelClass = get_class($model);
			
			$column_definitions = $model->get_column_definitions();
			if (!array_key_exists($dbName, $column_definitions))
				throw new Phpr_SystemException("Column {$modelClass}.{$dbName} cannot be added to a form because it is not defined with define_column method call.");

			$this->_columnDefinition = $column_definitions[$dbName];

			if ($this->_columnDefinition->isReference && !in_array($this->_columnDefinition->referenceType, array('belongs_to', 'has_many', 'has_and_belongs_to_many')))
				throw new Phpr_SystemException( "Error adding form field $dbName. Form fields can only be defined for the belongs_to, has_and_belongs_to_many and has_many relations. {$this->_columnDefinition->referenceType} associations are not supported.");

			$this->dbName = $dbName;
			$this->formSide = $side;
			$this->_model = $model;
		}

		/**
		 * Sets the form side the field should be placed to.
		 * Supported values are <em>left</em>, <em>right</em>, <em>full</em>. The form side can be specified
		 * in {@link Db_ActiveRecord::add_form_field() add_form_field()} method.
		 * @documentable
		 * @param $side Specifies the form side side.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function side($side = 'full')
		{
			$this->formSide = $side;
			return $this;
		}

		/**
		 * Specifies the field render mode. 
		 * By default render modes are guessed automatically basing in the database column or relation types:
		 * <ul>
		 *   <li><em>tinyint</em> columns are rendered as checkboxes.</li>
		 *   <li><em>float</em>, <em>numeric</em> and <em>varchar</em> columns are rendered as input TEXT elements.</li>
		 *   <li><em>textarea</em> columns are rendered as input TEXT elements.</li>
		 *   <li><em>datetime</em> columns are rendered as the datetime widget.</li>
		 *   <li><em>db_date</em> columns are rendered as the date widget.</li>
		 *   <li><em>belongs_to</em> relations are rendered as drop-down fields.</li> 
		 *   <li><em>has_and_belongs_to_many</em> relations are rendered as checkbox lists.</li> 
		 * </ul>
		 * You can override the default render mode by passing a required mode constant to the first argument of this method.
		 * List of supported render mode constants:
		 * <ul>
		 *   <li><em>frm_text</em> - creates a text element.</li>
		 *   <li><em>frm_password</em> - creates a password element.</li>
		 *   <li><em>frm_dropdown</em> - creates a drop-down element.</li>
		 *   <li><em>frm_radio</em> - creates radio buttons.</li>
		 *   <li><em>frm_checkbox</em> - creates a checkbox.</li>
		 *   <li><em>frm_checkboxlist</em> - creates a checkbox list.</li>
		 *   <li><em>frm_textarea</em> - creates a textarea element.</li>
		 *   <li><em>frm_html</em> - creates WYSIWYG editor field.</li>
		 *   <li><em>frm_code_editor</em> - creates a code editor field.</li>
		 *   <li><em>frm_grid</em> - creates a grid element. See {@link Db_FormFieldDefinition::gridColumns() gridColumns()}, {@link Db_FormFieldDefinition::gridSettings() gridSettings()} methods for details.</li>
		 *   <li><em>frm_datetime</em> - creates a date/time field.</li>
		 *   <li><em>frm_date</em> - creates a date field.</li>
		 *   <li><em>frm_onoffswitcher</em> - creates a switcher element.</li>
		 *   <li><em>frm_record_finder</em> - creates a record finder element.</li>
		 *   <li><em>frm_file_attachments</em> - creates a file attachments widget. Note that you should use {@link Db_FormFieldDefinition::renderFilesAs() renderFilesAs()} to specify how exactly the files should be displayed - as images, as a single image, or as a list of files.</li>
		 *   <li><em>frm_widget</em> - creates a form widget.</li>
		 * </ul>
		 * Options for multi-option fields (drop-down menu, checkbox list, or radio-button list) based on relation columns generate the list of options
		 * automatically. If you want a non-relation column to be rendered as a multi-value field you should define
		 * additional public methods in the model class. First method is <em>get_[column_name]_options()</em>. It should return the list of available
		 * options. If this method is defined in the model, it will be used for fetching the available options even for relation columns.
		 * The method should return an associative array.
		 * <pre>
		 * public function define_form_fields($context = null)
		 * {
		 *   $this->add_form_field('notification_mode')-;
		 * }
		 * 
		 * public function get_notification_mode_options($key_index=-1)
		 * {
		 *   $options = array(
		 *     'nobody'=>'Nobody',
		 *     'authors'=>'Authors only',
		 *     'all'=>'All users'
		 *   );
		 *   
		 *   if ($key_index == -1)
		 *    return $options;
		 *    
		 *   return isset($options[$key_index]) ? $options[$key_index] : null;
		 * }
		 * </pre>
		 * The method should accept the parameter specifying the key value. It is used for the preview form feature. If the value is <em>-1</em>
		 * the method returns full list of options. If the value is not <em>-1,</em> it should return a label for the option corresponding the key value.
		 * If you are not going to use preview forms for the model, implementing this feature is not required and you can just
		 * return all options from the method. This method executes before the method specified in {@link Db_FormFieldDefinition::optionsMethod() optionsMethod()}
		 * method and overrides its result.
		 *
		 * For radio-button and checkbox lists the method can also return item descriptions:
		 * <pre>
		 * public function get_notification_mode_options($key_index=-1)
		 * {
		 *   return array(
		 *     'nobody'=>array('Nobody'=>'Do not send new comment notifications.'),
		 *     'authors'=>array('Authors only'=>'Send new comment notifications only to post author.'),
		 *     'all'=>array('All users'=>'Notify all users who have permissions to receive blog notifications.')
		 *   );
		 * }
		 * </pre>
		 * Radio-button and checkbox list fields require the <em>get_[column_name]_option_state()</em> method, which determines whether an option is checked.
		 * The method should accept a single argument - the option value and return a boolean value:
		 * <pre>
		 * public function get_notification_mode_option_state($value)
		 * {
		 *   return $this->notification_mode == $value;
		 * }
		 * </pre>
		 * This method executes before the method specified in {@link Db_FormFieldDefinition::optionStateMethod() optionStateMethod()}
		 * method and overrides its result.
		 * @see Db_FormFieldDefinition::renderFilesAs() renderFilesAs()
		 * @see Db_FormFieldDefinition::optionsMethod() optionsMethod()
		 * @see Db_FormFieldDefinition::optionStateMethod() optionStateMethod()
		 * @documentable
		 * @param string $render_mode Specifies the render mode.
		 * @param array $options A list of render mode specific options.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function renderAs($renderMode, $options = array())
		{
			$this->renderMode = $renderMode;
			$this->renderOptions = $options;
			return $this;
		}

		/**
		 * Sets a language for code editor fields syntax highlighting. 
		 * Supported languages are: <em>css</em>, <em>html</em>, <em>javascript</em>, <em>json</em>, <em>php</em>, <em>xml</em>.
		 * @documentable
		 * @param string $language Specifies the language name.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function language($language)
		{
			$this->language = $language;
			return $this;
		}
		
		/**
		 * Sets a JavaScript function name to be executed when a user clicks Save button on the HTML editor toolbar.
		 * @param string $callback A JavaScript function name
		 * @documentable
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function saveCallback($callback)
		{
			$this->saveCallback = $callback;
			return $this;
		}

		/**
		 * Sets the element comment.
		 * Comments are displayed on the form below or above the element.
		 * @documentable
		 * @param string $text Specifies the comment text.
		 * @param string $position Specifies a comment position. Supported values are: <em>above</em>, <em>below</em>.
		 * @param bool $commentHTML Determines whether the comment contains HTML tags which should be preserved.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function comment($text, $position = 'below', $commentHTML = false)
		{
			$this->comment = $text;
			$this->commentPosition = $position;
			$this->commentHTML = $commentHTML;

			return $this;
		}

		/**
		 * Sets the element comment for the form preview.
		 * By default the preview comment matches the regular form comment.
		 * @documentable
		 * @param string $text Specifies the comment text.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function previewComment($text)
		{
			$this->previewComment = $text;
			return $this;
		}

		/**
		 * Sets a size of textarea fields. 
		 * @documentable
		 * @param string $size Specifies a size selector. Supported values are <em>tiny</em>, <em>small</em>, <em>large</em>, <em>huge</em>, <em>giant</em>.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function size($size)
		{
			$this->size = $size;
			return $this;
		}
		
		/**
		 * Sets a textarea text services. Currently supports 'auto_close_brackets'
		 * @param string $services Specifies a list of services, separated with comma
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function textServices($services)
		{
			$services = explode(',', $services);
			foreach ($services as &$service)
				$service = "'".trim($service)."'";

			$this->textareaServices = implode(',', $services);
			
			return $this;
		}
		
		/**
		 * Sets CSS class to be applied to the field container element.
		 * @documentable
		 * @param string $classes Specifies a string containing CSS class name(s);
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function cssClasses($classes)
		{
			$this->cssClasses = $classes;
			return $this;
		}

		/**
		 * Sets a CSS class name to apply to the field LI element.
		 * @documentable
		 * @param string $classes Specifies a string containing CSS class name(s);
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function cssClassName($className)
		{
			$this->cssClassName = $className;
			return $this;
		}
		
		/**
		 * Specifies a label corresponding an empty option for drop-down elements
		 * Use this method for the default empty option like <em>&lt;please select color&gt;</em>.
		 * @documentable
		 * @param string $text Specifies the option text.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function emptyOption($text)
		{
			$this->emptyOption = $text;
			return $this;
		}
		
		/**
		 * Sets a text to display in multi-relation fields in case if no options were selected.
		 * @documentable
		 * @param string $text Specifies the text to display.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function noOptions($text)
		{
			$this->noOptions = $text;
			return $this;
		}
		
		/**
		 * Sets the common model's method name responsible for returning a list of options for a multi-option field. 
		 * Allows to define a single method responsible for returning options for different drop-down, checkbox list and radio button form fields.
		 * The method should be defined as follows:
		 * <pre>public method method_name($db_name, $key_value = -1) 
		 * {
		 *   if ($db_name == 'color')
		 *     return array(33=>'Red', 34=>'Blue');
		 *
		 *   return false;
		 * }</pre>
		 * The parameter passed to the method is the column name. The method should return options for the specified column
		 * or FALSE if the column should use default options. The second method parameter is the key value. Please see
		 * {@link Db_FormFieldDefinition::renderAs() renderAs()} method description for details about this parameter.
		 * The method must return an array of record identifiers and values.
		 * @documentable
		 * @param string $name Specifies the method name.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function optionsMethod($name)
		{
			$this->optionsMethod = $name;
			return $this;
		}
		
		/**
		 * Sets the common model's method name responsible for returning option state for a multi-option field. 
		 * Allows to define a single method responsible for returning option state for different checkbox list and radio button form fields.
		 * The method should be defined as follows:
		 * <pre>
		 * public method method_name($db_name, $value)
		 * {
		 *   if ($db_value == 'color')
		 *     return $this->color == $value;
		 * }
		 * </pre>
		 * The method must return a boolean value determining the option state.
		 * @documentable
		 * @param string $name Specifies the method name.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function optionStateMethod($name)
		{
			$this->optionStateMethod = $name;
			return $this;
		}

		/**
		 * Sets SQL expression for filtering reference-type fields.
		 * Use this method to filter options for relation-based multi-value fields.
		 * @documentable
		 * @param string $expression Specifies an SQL expression. Example: <em>status is not null and status = 1</em>
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function referenceFilter($expr)
		{
			$this->referenceFilter = $expr;
			return $this;
		}
		
		/**
		 * Sets a SQL expression for fetching option descriptions in reference-type fields.
		 * Option descriptions are supported by the radio button and checkbox list fields.
		 * Specify the expression the parameter. The expression can be a table name or any SQL expression
		 * returning a scalar value, for example CONCAT: <em>concat(login_name, ' (', first_name, ' ', last_name, ')')</em>
		 * @documentable
		 * @param string $expression Specifies the SQL expression.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function referenceDescriptionField($expr)
		{
			$this->referenceDescriptionField = $expr;
			return $this;
		}
		
		/**
		 * Determines whether the relation preview is hidden for a relation field on the preview form.
		 * Relations on preview forms have a link for previewing the related record. This method
		 * allows to disable this feature.
		 * @documentable
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function previewNoRelation()
		{
			$this->previewNoRelation = true;
			return $this;
		}

		/**
		 * Sets an URL for the field preview link. 
		 * If the URL is specified, the field value on the preview form turns into a link.
		 * @documentable
		 * @param string $url Specifies the link URL.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function previewLink($url)
		{
			$this->previewLink = $url;
			return $this;
		}
		
		/**
		 * Disables the form field.
		 * @documentable
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function disabled()
		{
			$this->disabled = true;
		}
		
		/**
		 * Sets a text to be displayed for a relation field on the preview form when the field has no related records.
		 * @documentable
		 * @param string $message Specifies the message text.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function previewNoOptionsMessage($str)
		{
			$this->relationPreviewNoOptions = $str;
			return $this;
		}

		/**
		 * Sets value corresponding the <em>on</em> state of a checkbox field.
		 * @documentable
		 * @param string $value Specifies the value.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function checkboxOnState($value)
		{
			$this->checkboxOnState = $value;
			return $this;
		}

		/**
		 * Sets prompt message for a file attachment field.
		 * @documentable
		 * @param string $label Specifies the label text.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function addDocumentLabel($label)
		{
			$this->addAttachmentLabel = $label;
			return $this;
		}
		
		/**
		 * Sets a message to display in a file attachment field if there are no files attached.
		 * @documentable
		 * @param string $text Specifies the message text.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function noAttachmentsLabel($label)
		{
			$this->noAttachmentsLabel = $label;
			return $this;
		}

		/**
		 * Sets thumbnail size (both width and height) for an image field.
		 * @documentable
		 * @param integer $size Specifies the size.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function imageThumbSize($size)
		{
			$this->imageThumbSize = $size;
			return $this;
		}

		/**
		 * Sets a SQL expression for ordering options in reference-type fields.
		 * Usually you can use the referred table name as the SQL expression, for example:
		 * <pre>$this->add_form_field('customer_group')->referenceSort('name asc');</pre>
		 * @documentable
		 * @param string $expression Specifies an SQL sorting expression. 
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function referenceSort($expr)
		{
			$this->referenceSort = $expr;
			return $this;
		}
		
		/**
		 * Determines where options in a drop-down field should be HTML-encoded before they are displayed.
		 * By default options are always encoded, but you can disable this feature if you need
		 * the option to contain HTML tags which should be outputted as is.
		 * @documentable
		 * @param boolean $encode Determines whether options should be encoded. 
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function optionsHtmlEncode($htmlEncode)
		{
			$this->optionsHtmlEncode = $htmlEncode;
			return $this;
		}
		
		/**
		 * Sets a render mode for a file attachment field.
		 * Supported render modes for file attachments are: 
		 * <ul>
		 *  <li><em>single_file</em> - allows to upload a single file.</li>
		 *  <li><em>single_image</em> - allows to upload a single image.</li>
		 *  <li><em>file_list</em> - allows to upload multiple files.</li>
		 *  <li><em>image_list</em> - allows to upload multiple images.</li>
		 * </ul>
		 * Example:
		 * <pre>$this->add_form_field('files')->renderAs(frm_file_attachments)->renderFilesAs('file_list');</pre>
		 * File columns should have a corresponding <em>has_many</em> relation referring to the {@link Db_File} class. 
		 * See {@link Db_File} class for details about creating model file fields.
		 * @documentable
		 * @see Db_File
		 * @param string $renderMode Specifies the file list render mode value. 
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function renderFilesAs($renderMode)
		{
			$this->renderFilesAs = $renderMode;
			return $this;
		}

		/**
		 * Adds a list of TinyMCE plugins for a HTML field.
		 * Please refer to {@link http://www.tinymce.com/ TinyMCE} documentation for details about plugins. By default the following plugins
		 * are loaded: <em>paste</em>, <em>searchreplace</em>, <em>advlink</em>, <em>inlinepopups</em>.
		 * This method adds plugin to the existing list.
		 * @documentable
		 * @param string $plugins A list of plugins to load.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlPlugins($plugins)
		{
			if (substr($plugins, 0, 1) != ',')
				$plugins = ', '.$plugins;

			$this->htmlPlugins .= $plugins;
			return $this;
		}
		
		/**
		 * Sets a list of buttons to be displayed in the 1st row of HTML field toolbar.
		 * Please refer to {@link http://www.tinymce.com/ TinyMCE} documentation for details about buttons.
		 * @documentable
		 * @param string $buttons A list of buttons to display.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlButtons1($buttons)
		{
			$this->htmlButtons1 = $buttons;
			return $this;
		}

		/**
		 * Sets a list of buttons to be displayed in the 2nd row of HTML field toolbar.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about buttons.
		 * @documentable
		 * @param string $buttons A list of buttons to display.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlButtons2($buttons)
		{
			$this->htmlButtons2 = $buttons;
			return $this;
		}
		
		/**
		 * Sets a list of buttons to be displayed in the 3rd row of HTML field toolbar.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about buttons.
		 * @documentable
		 * @param string $buttons A list of buttons to display.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlButtons3($buttons)
		{
			$this->htmlButtons3 = $buttons;
			return $this;
		}
		
		/**
		 * Sets a custom CSS file to be used to style HTML field content.
		 * The URL should be relative to LemonStand application root.
		 * @documentable
		 * @param string $url Specifies an URL of CSS file
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlContentCss($url)
		{
			$this->htmlContentCss = $url;
			return $this;
		}
		
		/**
		 * Sets a list of TinyMCE block formats for a HTML field.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about block formats.
		 * @documentable
		 * @param string $formats Specifies a comma-separated list of formats
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlBlockFormats($formats)
		{
			$this->htmlBlockFormats = $formats;
			return $this;
		}
		
		/**
		 * Sets a list of TinyMCE custom styles for a HTML field.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about custom styles.
		 * @documentable
		 * @param string $styles Specifies a semicolon-separated list of styles
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlCustomStyles($styles)
		{
			$this->htmlCustomStyles = $styles;
			return $this;
		}
		
		/**
		 * Sets a list of TinyMCE font sizes for a HTML field.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about font sizes.
		 * @documentable
		 * @param string $sizes Specifies a comma-separated list of sizes
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlFontSizes($sizes)
		{
			$this->htmlFontSizes = $sizes;
			return $this;
		}
		
		/**
		 * Specifies a list of TinyMCE font colors for a HTML field.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about font colors.
		 * @documentable
		 * @param string $colors Specifies a comma-separated list of colors: "FF00FF,FFFF00,000000"
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlFontColors($colors)
		{
			$this->htmlFontColors = $colors;
			return $this;
		}

		/**
		 * Sets a list of TinyMCE background colors for a HTML field.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about background colors.
		 * @documentable
		 * @param string $colors Specifies a comma-separated list of colors: "FF00FF,FFFF00,000000".
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlBackgroundColors($colors)
		{
			$this->htmlBackgroundColors = $colors;
			return $this;
		}

		/**
		 * This option enables you to disable the "more colors" link in a HTML field for text and background color menus.
		 * @documentable
		 * @param string $allow Indicates whether the more colors link should be enabled.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlAllowMoreColors($allow)
		{
			$this->htmlAllowMoreColors = $allow;
			return $this;
		}
		
		/**
		 * Sets a list of TinyMCE valid elements for a HTML field.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about valid elements.
		 * @documentable
		 * @param string $value Specifies a list of valid elements, as text.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlValidElements($value)
		{
			$this->htmlValidElements = $value;
			return $this;
		}
		
		/**
		 * Sets a list of TinyMCE valid child elements for a HTML field.
		 * Please refer {@link http://www.tinymce.com/ TinyMCE} documentation for details about valid child elements.
		 * @documentable
		 * @param string $value Specifies a list of valid child elements, as text
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlValidChildElements($value)
		{
			$this->htmlValidChildElements = $value;
			return $this;
		}
		
		/**
		 * Sets a base URL for file links for a file attachments field.
		 * Usual value for this method is <em>url('ls_backend/files/get/')</em>.
		 * @documentable
		 * @see Db_File
		 * @param string $url Specifies the base URL.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function fileDownloadBaseUrl($url)
		{
			$this->fileDownloadBaseUrl = $url;
			return $this;
		}

		/**
		 * Returns the model column definition corresponding the form field.
		 * @documentable
		 * @return Db_ColumnDefinition Returns the column definition object.
		 */
		public function getColDefinition()
		{
			return $this->_columnDefinition;
		}
		
		/**
		 * Hides the element form label.
 		 * @documentable
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function noLabel()
		{
			$this->noLabel = true;
			return $this;
		}
		
		/**
		 * Hides a text area content in a form.
 		 * @documentable
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function hideContent()
		{
			$this->hideContent = true;
			return $this;
		}
		
		/**
		 * Defines column configuration for a grid field.
		 * Grid control requires the corresponding model column to contain an array with the grid data. 
		 * Usually the data is contained in a text filed and serialized before the model data is saved to the database.
		 * Column configuration is defined as an associative array with column identifiers in the array keys and 
		 * column settings in the values. Example of the configuration array:
		 * <pre>
		 * array(
		 *  'country'=>array('title'=>'Country Code', 'align'=>'left'), 
		 *  'state'=>array('title'=>'State Code', 'align'=>'left', 'width'=>'100'), 
		 *  'read_only_col'=>array('title'=>'Read only', 'read_only'=>true)
		 * )
		 * </pre>
		 * @documentable
		 * @see Db_FormFieldDefinition::gridSettings() gridSettings()
		 * @param array $columns The column configuration array.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function gridColumns($columns)
		{
			$this->gridColumns = $columns;
			return $this;
		}
		
		/**
		 * Defines configuration of a grid field.
		 * The grid control configuration is defined as an array. Example configuration:
		 * <pre>array(
		 *   'no_toolbar'=>true, 
		 *   'allow_adding_rows'=>false, 
		 *   'allow_deleting_rows'=>false, 
		 *   'no_sorting'=>false
		 * )</pre>
		 * @documentable
		 * @see Db_FormFieldDefinition::gridColumns() gridColumns()
		 * @param array $settings Specifies the grid settings. 
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function gridSettings($settings)
		{
			$this->gridSettings = $settings;
			return $this;
		}
		
		/**
		 * Determines whether new lines in the field value should be converted to line breaks in a form preview.
		 * This method works only with text areas. 
		 * @documentable
		 * @param boolean $convert Determines whether new lines should be converted to line breaks.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function nl2br($value)
		{
			$this->nl2br = $value;
			return $this;
		}
		
		/**
		 * Specifies name of a partial to render below the form field caption.
		 * The partial file should be placed to the views directory of the controller
		 * which renders the form. Alternatively you can specify the absolute pat to the 
		 * partial.
		 * @documentable
		 * @param string $partial_name Specifies the partial name or an absolute partial's file path.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function titlePartial($partial_name)
		{
			$this->titlePartial = $partial_name;
			return $this;
		}

		/**
		 * Allows to render a specific partial instead of the standard field type specific partial
		 * The partial file should be placed to the views directory of the controller
		 * which renders the form. Alternatively you can specify the absolute pat to the 
		 * partial.
		 * @documentable
		 * @param string $partial_name Specifies the partial name or an absolute partial's file path.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function formElementPartial($partial_name)
		{
			$this->formElementPartial = $partial_name;
			return $this;
		}
		
		/**
		 * Specifies HTML help string to be displayed in a form preview. 
		 * @documentable
		 * @param string $help_string Specifies the help string.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function previewHelp($string)
		{
			$this->previewHelp = $string;
			return $this;
		}
		
		/**
		 * Specifies a tooltip text for the field comment.
		 * @documentable
		 * @param string $help_string Specifies the tooltip string.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function commentTooltip($string)
		{
			$this->commentTooltip = $string;
			return $this;
		}
		
		/**
		 * Determines whether the HTML field should be full-width.
		 * @documentable
		 * @param boolean $full_width Determines whether the field should be full-width.
		 * @return Db_FormFieldDefinition Returns the updated form field definition object.
		 */
		public function htmlFullWidht($value)
		{
			$this->htmlFullWidht = $value;
			return $this;
		}
		
		/**
		 * Returns the validation rule set object corresponding to the form field.
		 * @documentable
		 * @return Phpr_ValidationRules Returns the validation rule set object.
		 */
		public function validation()
		{
			return $this->_columnDefinition->validation();
		}
	}

?>