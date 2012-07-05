<?php

	/**
	 * Represents an Administration Area user.
	 * @property integer $id Specifies the user record identifier.
	 * @property string $name Specifies the full user name, read only.
	 * @property string $firstName Specifies the user first name.
	 * @property string $lastName Specifies the user last name.
	 * @property string $middleName Specifies the user middle name.
	 * @property string $email Specifies the user email.
	 * @property string $login Specifies the user login name.
	 * @property Db_DataCollection $photo The user photo. The collection contains zero or one object of {@link Db_File} class.
	 * @documentable
	 * @package core.models
	 * @author LemonStand eCommerce Inc.
	 */
	class Users_User extends Phpr_User
	{
		const disabled = -1;
		
		const shortNameExpr = "trim(concat(ifnull(@firstName, ''), ' ', ifnull(concat(substring(@lastName, 1, 1), '. '), ''), ifnull(concat(substring(@middleName, 1, 1), '. '), '')))";
		const fullNameExpr = "trim(concat(ifnull(@firstName, ''), ' ', ifnull(@lastName, ' '), ' ', ifnull(@middleName, '')))";

		public $implement = 'Db_AutoFootprints';
		protected $added_fields = array();

		public $calculated_columns = array( 
			'short_name'=>"trim(concat(ifnull(firstName, ''), ' ', ifnull(concat(substring(lastName, 1, 1), '. '), ''), ifnull(concat(substring(middleName, 1, 1), '. '), '')))",
			'name'=>"trim(concat(ifnull(firstName, ''), ' ', ifnull(lastName, ' '), ' ', ifnull(middleName, '')))",
			'state'=>'if(status is null or status = 0, "Active", if (status=-1, "Disabled", "Active"))'
		);

		public $belongs_to = array(
			'role'=>array('class_name'=>'Shop_Role', 'foreign_key'=>'shop_role_id')
		);

		public $has_and_belongs_to_many = array(
			'rights'=>array('class_name'=>'Users_Groups')
		);
		
		public $has_many = array(
			'photo'=>array('class_name'=>'Db_File', 'foreign_key'=>'master_object_id', 'conditions'=>"master_object_class='Users_User' and field='photo'", 'order'=>'sort_order, id', 'delete'=>true)
		);

		public $custom_columns = array('password_confirm'=>db_text, 'send_invitation'=>db_bool);
		protected $plain_password = null;
		protected static $usersWoRolesNum = null;
		protected $is_administrator_cache = null;
		protected $is_asministrator_on_load = null;
		
		public $password_restore_mode = false;
		protected $api_added_columns = array();

		public function define_columns($context = null)
		{
			$this->define_column('name', 'Full Name')->order('asc');
			$this->define_column('firstName', 'First Name')->defaultInvisible()->validation()->fn('trim')->required();
			$this->define_column('lastName', 'Last Name')->defaultInvisible()->validation()->fn('trim')->required();
			$this->define_column('middleName', 'Middle Name')->defaultInvisible()->validation()->fn('trim');
			$this->define_column('email', 'Email')->validation()->fn('trim')->required()->email();
			$this->define_column('login', 'Login')->validation()->fn('trim')->required()->unique('Login name "%s" already in use. Please choose another login name.');
			$this->define_column('password', 'Password')->invisible()->validation();
			$this->define_column('password_confirm', 'Password Confirmation')->invisible()->validation();
			$this->define_column('state', 'Status');
			$this->define_column('status', 'Status')->invisible();
			$this->define_column('last_login', 'Last Login')->dateFormat('%x %H:%M');

			$this->define_column('send_invitation', 'Send invitation by email')->invisible();
			
			$this->define_multi_relation_column('rights', 'rights', 'Rights', '@name')->defaultInvisible()->validation();
			$this->define_relation_column('role', 'role', 'Role', db_varchar, '@name')->validation()->required('Please, specify user role.');
			$this->define_multi_relation_column('photo', 'photo', 'Photo', '@name')->invisible();
			
			$this->defined_column_list = array();
			Backend::$events->fireEvent('core:onExtendUserModel', $this, $context);
			$this->api_added_columns = array_keys($this->defined_column_list);
		}
		
		public function define_form_fields($context = null)
		{
			if (!$this->is_new_record())
				$this->is_asministrator_on_load = $this->is_administrator();
			
			if ($context != 'mysettings')
			{
				$this->add_form_field('firstName', 'left')->tab('Contacts');
				$this->add_form_field('lastName', 'right')->tab('Contacts');
				$this->add_form_field('middleName')->tab('Contacts');
				$this->add_form_field('email')->tab('Contacts');

				$this->add_form_field('status')->tab('Account')->renderAs(frm_dropdown);
				$this->add_form_field('login')->tab('Account');
				$this->add_form_field('password', 'left')->tab('Account')->renderAs(frm_password)->noPreview();
				$this->add_form_field('password_confirm', 'right')->tab('Account')->renderAs(frm_password)->noPreview();
				$this->add_form_field('rights')->tab('Account')->renderAs(frm_checkboxlist)->referenceDescriptionField('concat(@description)')->previewNoOptionsMessage('Rights are not set.')->previewNoRelation();

				$this->add_form_field('role')->tab('Shop')->renderAs(frm_radio)->referenceDescriptionField('concat(@description)')->referenceSort('id')->comment('User role determines how the user participates in the order route. You can setup the order route on the <a target="_blank" href="'.url('shop/statuses/').'">Order Statuses and Transitions</a> page.', 'above', true);
				$this->add_form_section('Please specify user permissions', 'User Rights')->tab('Shop');
			
				if ($this->is_new_record())
				{
					$field = $this->add_form_field('send_invitation')->tab('Contacts');
				
					if (!System_EmailParams::isConfigured())
						$field->comment('The message cannot be send because email system is not configured. To configure it please visit the System Settings tab.')->disabled();
					else 
						$field->comment('Use this checkbox to send an invitation to the user by email.');
				}
				
				Core_ModuleManager::buildPermissionsUi($this);
			
				if (!$this->is_new_record())
					$this->load_user_permissions();
			} else 
			{
				$this->add_form_field('email')->tab('My Settings');
				$this->add_form_field('password', 'left')->renderAs(frm_password)->noPreview()->tab('My Settings');
				$this->add_form_field('password_confirm', 'right')->renderAs(frm_password)->noPreview()->tab('My Settings');
			}
			
			$tab = $context == 'mysettings' ? 'My Settings' : 'Contacts';

			$this->add_form_field('photo')->renderAs(frm_file_attachments)->renderFilesAs('single_image')->addDocumentLabel('Upload photo')->tab($tab)->noAttachmentsLabel('Photo is not uploaded')->imageThumbSize(100)->fileDownloadBaseUrl(url('ls_backend/files/get/'));
			
			Backend::$events->fireEvent('core:onExtendUserForm', $this, $context);
			foreach ($this->api_added_columns as $column_name)
			{
				$form_field = $this->find_form_field($column_name);
				if ($form_field)
					$form_field->optionsMethod('get_added_field_options');
			}
		}
		
		public function get_status_options($keyValue=-1)
		{
			$result = array();
			$result[0] = 'Active';
			$result[-1] = 'Disabled';

			return $result;
		}

		public function before_save($deferred_session_key = null)
		{
			$this->plain_password = $this->password;
			
			if (strlen($this->password) || strlen($this->password_confirm))
			{
				if ($this->password != $this->password_confirm)
					$this->validation->setError('Password and confirmation password do not match.', 'password', true);
			}
						
			if (!strlen($this->password))
			{
				if ($this->is_new_record() || $this->password_restore_mode)
					$this->validation->setError('Please provide a password.', 'password', true);
				else
					$this->password = $this->fetched['password'];
			} else
			{
				$this->password = Phpr_SecurityFramework::create()->salted_hash($this->password);
			}

			if (!$this->is_new_record())
			{
				$current_user = Phpr::$security->getUser();
				if ($current_user && $current_user->id == $this->id && $this->is_asministrator_on_load && !$this->rights)
					$this->validation->setError('You cannot cancel administrator rights for your own user account.', 'rights', true);
			}
		}
		
		public function after_save()
		{
			if ($this->rights)
				return;
			
			if ($this->added_fields)
			{
				foreach ($this->added_fields as $code=>$info)
				{
					$module = $info[0];
					Users_Permissions::save_user_permissions($this->id, $module->getId(), $info[1], $this->$code);
				}
			}
		}
		
		public function createPasswordRestoreHash()
		{
			$this->password_restore_hash = Phpr_SecurityFramework::create()->salted_hash(rand(1,400));
			$this->password = null;
			$this->save();
			
			return $this->password_restore_hash;
		}
		
		public function clearPasswordResetHash()
		{
			$this->password_restore_hash = null;
			$this->password = null;
			$this->save();
		}
		
		public function after_create()
		{
			if (!$this->send_invitation)
				return;

			$viewData = array(
				'url'=>Phpr::$request->getRootUrl().url('session/handle/create'), 
				'user'=>$this,
				'password'=>$this->plain_password
			);
			Core_Email::sendOne('system', 'invitation', $viewData, 'Welcome to LemonStand!', $this);
		}
		
		public static function create($values = null) 
		{
			return new self($values);
		}

		public function belongsToGroups($groups)
		{
			$groups = Phpr_Util::splat($groups);
			
			$rights = $this->rights;
			foreach ($rights as $right)
			{
				if (in_array($right->code, $groups))
					return true;
			}
			
			return false;
		}
		
		public static function listAdministrators()
		{
			return self::create()->
				join('groups', "groups.code = 'administrator'")->
				join('groups_users', "groups_users.group_id = groups.id")->
				where('status <> ?', self::disabled)->
				where('users.id = groups_users.user_id')->find_all();
		}

		public function add_field($module, $code, $title, $side = 'full', $type = db_text)
		{
			$module_id = $module->getId();
			
			$original_code = $code;
			$code = $module_id.'_'.$code;
			$this->custom_columns[$code] = $type;
			$this->_columns_def = null;
			$this->define_column($code, $title)->validation();

			$form_field = $this->add_form_field($code, $side)->optionsMethod('get_added_field_options')->tab($module->getModuleInfo()->name)->cssClassName('permission_field');

			$this->added_fields[$code] = array($module, $original_code);

			return $form_field;
		}

		public function get_added_field_options($db_name, $current_key_value = -1)
		{
			$result = Backend::$events->fireEvent('core:onGetUserFieldOptions', $db_name, $current_key_value);
			foreach ($result as $options)
			{
				if (is_array($options) || (strlen($options && $current_key_value != -1)))
					return $options;
			}
			
			if (!isset($db_name, $this->added_fields))
				return array();

			$module = $this->added_fields[$db_name][0];
			$code = $this->added_fields[$db_name][1];
			$class_name = get_class($module);

			$method_name = "get_{$code}_options";
			if (!method_exists($module, $method_name))
				throw new Phpr_SystemException("Method {$method_name} is not defined in {$class_name} class.");

			return $module->$method_name($current_key_value);
		}

		protected function load_user_permissions()
		{
			$permissions = Users_Permissions::get_user_permissions($this->id);
			foreach ($permissions as $permission)
			{
				$field_code = $permission->module_id.'_'.$permission->permission_name;
				if (array_key_exists($field_code, $this->added_fields))
				{
					$this->$field_code = $permission->value;
				}
			}
		}
		
		public function before_delete($id=null) 
		{
			$current_user = Phpr::$security->getUser();
			if ($current_user && $current_user->id == $this->id)
				throw new Phpr_ApplicationException("You cannot delete your own user account.");
			
			if ($this->last_login)
				throw new Phpr_ApplicationException("Users cannot be deleted after first login. You may disable the user account instead of deleting.");
		}

		public function update_last_login()
		{
			Db_DbHelper::query(
				"update users set last_login=:last_login where id=:id", 
				array('id'=>$this->id, 'last_login'=>Phpr_DateTime::now())
			);
		}
		
		public function is_administrator()
		{
			if ($this->is_administrator_cache !== null)
				return $this->is_administrator_cache;
			
			return $this->is_administrator_cache = $this->belongsToGroups(Users_Groups::admin);
		}
		
		public function get_permission($module_id, $permission_name)
		{
			if ($this->is_administrator())
				return true;
			
			if (!is_array($permission_name))
				return Users_Permissions::get_user_permission($this->id, $module_id, $permission_name);
			else
			{
				foreach ($permission_name as $permission)
				{
					if (Users_Permissions::get_user_permission($this->id, $module_id, $permission))
						return true;
				}
				
				return false;
			}
		}
		
		public static function list_users_having_permission($module_id, $permission_name)
		{
			$users = self::create()->find_all();
			$result = array();
			
			foreach ($users as $user)
			{
				if ($user->status == self::disabled)
					continue;
				
				if ($user->get_permission($module_id, $permission_name))
					$result[] = $user;
			}
			
			return $result;
		}

		public function findUser( $Login, $Password )
		{
			return $this->where('login = lower(?)', $Login)->where('password = ?', Phpr_SecurityFramework::create()->salted_hash($Password))->find();
		}
		
		/*
		 * Event descriptions
		 */
		
		/**
		 * Allows to define new columns in the back-end user model.
		 * The event handler should accept two parameters - the user object and the form 
		 * execution context string. To add new columns to the user model, call the {@link Db_ActiveRecord::define_column() define_column()}
		 * method of the user object. Before you add new columns to the model, you should add them to the
		 * database (the <em>users</em> table).
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('core:onExtendUserModel', $this, 'extend_user_model');
		 *   Backend::$events->addEvent('core:onExtendUserForm', $this, 'extend_user_form');
		 * }
		 *  
		 * public function extend_user_model($order, $context)
		 * {
		 *   $order->define_column('x_gender', 'Gender');
		 * }
		 *  
		 * public function extend_user_form($order, $context)
		 * {
		 *   $order->add_form_field('x_gender')->tab('Contacts');
		 * }
		 * </pre>
		 * @event core:onExtendUserModel
		 * @package core.events
		 * @author LemonStand eCommerce Inc.
		 * @see core:onExtendUserForm
		 * @see core:onGetUserFieldOptions
		 * @see http://lemonstandapp.com/docs/extending_existing_models Extending existing models
		 * @see http://lemonstandapp.com/docs/creating_and_updating_database_tables Creating and updating database tables
		 * @param Users_User $user Specifies the user object.
		 * @param string $context Specifies the execution context.
		 */
		private function event_onExtendUserModel($user, $context) {}
			
		/**
		 * Allows to add new fields to the Create/Edit User form in the Administration Area. 
		 * Usually this event is used together with the {@link core:onExtendUserModel} event. 
		 * To add new fields to the user form, call the {@link Db_ActiveRecord::add_form_field() add_form_field()} method of the 
		 * user object.
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('core:onExtendUserModel', $this, 'extend_user_model');
		 *   Backend::$events->addEvent('core:onExtendUserForm', $this, 'extend_user_form');
		 * }
		 *  
		 * public function extend_user_model($order, $context)
		 * {
		 *   $order->define_column('x_gender', 'Gender');
		 * }
		 *  
		 * public function extend_user_form($order, $context)
		 * {
		 *   $order->add_form_field('x_gender')->tab('Contacts');
		 * }
		 * </pre>
		 * @event core:onExtendUserForm
		 * @package core.events
		 * @author LemonStand eCommerce Inc.
		 * @see core:onExtendUserModel
		 * @see core:onGetUserFieldOptions
		 * @see http://lemonstandapp.com/docs/extending_existing_models Extending existing models
		 * @see http://lemonstandapp.com/docs/creating_and_updating_database_tables Creating and updating database tables
		 * @param Users_User $user Specifies the user object.
		 * @param string $context Specifies the execution context.
		 */
		private function event_onExtendUserForm($user, $context) {}
			
		/**
		 * Allows to populate drop-down, radio- or checkbox list fields, which have been added with {@link core:onExtendUserForm} event.
		 * Usually you do not need to use this event for fields which represent 
		 * {@link http://lemonstandapp.com/docs/extending_models_with_related_columns data relations}. But if you want a standard 
		 * field (corresponding an integer-typed database column, for example), to be rendered as a drop-down list, you should 
		 * handle this event.
		 *
		 * The event handler should accept 2 parameters - the field name and a current field value. If the current
		 * field value is -1, the handler should return an array containing a list of options. If the current 
		 * field value is not -1, the handler should return a string (label), corresponding the value.
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('core:onExtendUserModel', $this, 'extend_user_model');
		 *   Backend::$events->addEvent('core:onExtendUserForm', $this, 'extend_user_form');
		 *   Backend::$events->addEvent('core:onGetUserFieldOptions', $this, 'get_user_field_options');
		 * }
		 *  
		 * public function extend_user_model($order, $context)
		 * {
		 *   $order->define_column('x_gender', 'Gender');
		 * }
		 *  
		 * public function extend_user_form($order, $context)
		 * {
		 *   $order->add_form_field('x_gender')->tab('Contacts');
		 * }
		 * 
		 * public function get_user_field_options($field_name, $current_key_value)
		 * {
		 *   if ($field_name == 'x_gender')
		 *   {
		 *     $options = array(
		 *       'u' => 'Unknown',
		 *       'f' => 'Female',
		 *       'm' => 'Male'
		 *     );
		 *     
		 *     if ($current_key_value == -1)
		 *       return $options;
		 *     
		 *     if (array_key_exists($current_key_value, $options))
		 *       return $options[$current_key_value];
		 *   }
		 * }
		 * </pre>
		 * @event core:onGetUserFieldOptions
		 * @package core.events
		 * @author LemonStand eCommerce Inc.
		 * @see core:onExtendUserModel
		 * @see core:onExtendUserForm
		 * @see http://lemonstandapp.com/docs/extending_existing_models Extending existing models
		 * @see http://lemonstandapp.com/docs/creating_and_updating_database_tables Creating and updating database tables
		 * @param string $db_name Specifies the field name.
		 * @param string $field_value Specifies the field value.
		 * @return mixed Returns a list of options or a specific option label.
		 */
		private function event_onGetUserFieldOptions($db_name, $field_value) {}
	}

?>