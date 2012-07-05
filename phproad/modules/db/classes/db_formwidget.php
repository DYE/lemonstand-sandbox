<?

	/**
	 * Base class for form widgets. Form widgets can render custom form controls,
	 * and provide their life cycle operations.
	 */
	class Db_FormWidget
	{
		public $column_name;
		public $model;

		protected $controller;
		protected $view_path;
		protected $resources_path;
		protected $configuration;
		protected $view_data = array();
		
		public function __construct($controller, $model, $column_name, $configuration)
		{
			$ref_object = new ReflectionObject($this);
			$widget_root_dir = str_replace("\\", "/", dirname($ref_object->getFileName())).'/'.strtolower(get_class($this));
			$this->view_path = $widget_root_dir.'/partials';
			
			$this->resources_path = $widget_root_dir.'/resources';
			if (strpos($this->resources_path, PATH_APP) === 0)
				$this->resources_path = substr($this->resources_path, strlen(PATH_APP));
			
			$this->controller = $controller;
			$this->model = $model;
			$this->column_name = $column_name;
			$this->configuration = $configuration;
			
			foreach ($configuration as $name=>$value)
				$this->$name = $value;
			
			$this->load_resources();
		}
		
		/**
		 * Adds widget-specific resource files. Use $this->controller->addJavaScript() and $this->controller->addCss()
		 * to register new resources.
		 */
		protected function load_resources()
		{
		}
		
		/**
		 * Tries to render a controller partial, and if it does not exist, renders the widget partial with the same name.
		 * @param string $view_name Specifies a view name
		 * @param array $params A list of parameters to pass to the partial file
		 * @param bool $override_controller Indicates that the controller partial should be overridden 
		 * by the widget partial even if the controller partial does exist.
		 * @param bool $throw Indicates that an exception should be thrown in case if the partial does not exist
		 * @return bool
		 */
		public function render_partial($view_name, $params = array(), $override_controller = false, $throw = true)
		{
			$this->render_partial_file($this->controller->getViewsDirPath(), $view_name, $params, $override_controller, $throw);
		}
		
		private function render_partial_file($controller_view_path, $view_name, $params = array(), $override_controller = false, $throw = true)
		{
			$this->controller->viewData = $this->view_data + $this->controller->viewData;
			$controller_view_path = $controller_view_path.'/_'.$view_name.'.htm';

			if (!$override_controller && file_exists($controller_view_path))
				$this->controller->renderPartial($controller_view_path, $params, true, true);
			else
			{
				$view_path = $this->view_path.'/_'.$view_name.'.htm';
				if (!$throw && !file_exists($view_path))
					return;

				$this->controller->renderPartial($view_path, $params, true, true);
			}
		}
		
		/**
		 * Returns full relative path to a resource file
		 * situated in the widget's resources directory.
		 * @param string $path Specifies the relative resource file name, for example '/resources/javascript/widget.js'
		 * @return string Returns full relative path, suitable for passing to the controller's addCss() or addJavaScript() method.
		 */
		protected function map_resource_file($path)
		{
			if (substr($path, 0, 1) != '/')
				$path = '/'.$path;
				
			return $this->resources_path.$path;
		}
		
		public function handle_event($event, $model, $field)
		{
			if (substr($event, 0, 2) != 'on')
				throw new Phpr_SystemException('Invalid widget event name: '.$event);
				
			if (!method_exists($this, $event))
				throw new Phpr_SystemException(sprintf('Event handler %s not found in widget %s.', $event, get_class($this)));
				
			$this->$event($field, $model);
		}
		
		public function render()
		{
		}
	}

?>