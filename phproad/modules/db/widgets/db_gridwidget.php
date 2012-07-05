<?

	class Db_GridWidget extends Db_FormWidget
	{
		/**
		 * @var array Column configuration. 
		 * Each column is represented with an associative array. The <strong>title</strong> and <strong>type</strong> elements are required. Array keys 
		 * represent the column name. <strong>width</strong> is an optional element. Other elements depend on an editor type used for the column. 
		 * Supported types are <strong>checkbox</strong>, <strong>text</strong> and <strong>dropdown</strong>.
		 * This dropdown editor requires the <strong>option_keys</strong> and <strong>option_values</strong> elements
		 * to be set. The dropdown editor also supports <strong>allow_deselect</strong> (boolean) and <strong>default_text</strong> (string) options.
		 * The text editor supports autocompletion. To enable autocompletion, add the <strong>autocomplete</strong> parameter to the column configuration 
		 * and specify its type - either "predefined" or "remote". Predefined values should be specified with the <strong>options</strong> element (array).
		 * Remote autocompletion requires the get_grid_autocomplete_values($db_name, $grid_column_name, $term, $row_data) to be implemented in the model.
		 * Remote autocompletion supports the autocomplete_custom_values column parameter. If it is true, the get_grid_autocomplete_values()  method should
		 * return an associative array of values and names for autocompletion. If it is false, the method should return a plain.
		 * Another option supported by autocompletion is minLength.
		 * The checkbox column type supports header_control (boolean, adds checkbox to the table header) and checked_class (string, CSS class name, marks row with a specified CSS class) parameters.
		 * You can use column grouping for grids connected to a dataset. Add the 'column_group' element to the column definition array.
		 */
		public $columns;
		
		/**
		 * @var boolean Determines whether drag & drop row sorting is enabled.
		 * If a data source used, this parameter automatically sets to false.
		 */
		public $sortable = false;
		
		/**
		 * @var boolean Determines whether the grid is sortable.
		 * If a data source used, this parameter automatically sets to false.
		 */
		public $scrollable = false;
		
		/**
		 * @var string Specifies CSS class name for the scrollable viewport HTML element.
		 */
		public $scrollable_viewport_class = '';
		
		/**
		 * @var boolean Determines whether the automatic row adding feature is enabled.
		 */
		public $allow_row_adding = true;
		
		/**
		 * @var boolean Determines whether rows are deletable.
		 */
		public $deletable = true;
		
		/**
		 * @var boolean Determines input data array indexes should be maintained.
		 */
		public $maintain_data_indexes = false;

		/**
		 * @var string Specified the confirmation message which should be displayed before a row is deleted.
		 */
		public $delete_row_message = null;
		
		/**
		 * @var boolean Determines whether CSV operations are supported in the grid.
		 */
		public $enable_csv_operations = true;

		/**
		 * @var string Specifies a name for exported CSV files.
		 */
		public $csv_file_name = 'data';

		/**
		 * @var integer Maximum number of rows. If the number of rows exceeds this value, the widget
		 * automatically blocks its content, leaving only CSV operations.
		 */
		public $max_row_number = 300;
		
		/**
		 * @var boolean Hides the widget toolbar.
		 */
		public $disable_toolbar = false;
		
		/**
		 * @var boolean Focus first table cell on display.
		 */
		public $focus_first = false;

		/**
		 * @var string Path to the custom help popup partial.
		 */
		public $help_partial_path = false;

		/**
		 * @var string Path to the custom toolbar buttons partial.
		 */
		public $toolbar_partial_path = false;

		/**
		 * @var boolean Determines whether a datasource (Db_GridWidgetDataSource) should be used.
		 */
		public $use_data_source = false;

		/**
		 * @var string Specifies the datasource identifier.
		 */
		public $data_source_id = null;
		
		/**
		 * @var integer Specifies a number of rows to display on a single page. 
		 * Pagination works only if a data source is used.
		 */
		public $page_size = 10;
		
		/**
		 * @var boolean Enables horizontal scrolling of the data table.
		 * This option works only if a data source is used.
		 */
		public $horizontal_scroll = false;
		
		/**
		 * @var boolean Specifies whether word wrapping is allowed in the column titles.
		 */
		public $title_word_wrap = true;
		
		/**
		 * @var boolean Enables search control.
		 * This option works only if a data source is used.
		 */
		public $enable_search = false;
				
		/**
		 * @var integer Specifies the exact width of the data table, in pixels.
		 */
		public $table_width = null;
		
		/**
		 * @var array Configuration for column groups (specified with column_group parameter).
		 * Example: 'column_group_configuration'=>array('Group 1'=>array('class'=>'key'))
		 */
		public $column_group_configuration = array();
		
		protected $editor_cache = null;
		protected $data_source = null;
		protected $message = null;
		
		protected function load_resources()
		{
			$this->controller->addJavaScript('/phproad/javascript/jquery.class.js');
			$this->controller->addJavaScript('/phproad/javascript/jquery.caret-range.js');
			$this->controller->addJavaScript('/phproad/javascript/jquery-ui.autocomplete.min.js');
			$this->controller->addJavaScript($this->map_resource_file('javascript/jquery.grid.js?'.module_build('core')));
			$this->controller->addJavaScript($this->map_resource_file('javascript/jquery.grid.editors.js?'.module_build('core')));

			$this->controller->addCss($this->map_resource_file('css/grid.css?'.module_build('core')));
		}
		
		public function render()
		{
			$this->prepare_data_fields();
			$this->view_data['client_script_options'] = $this->get_client_script_options();
			$this->render_partial('grid_container');
		}
		
		/**
		 * Returns data required for marking an error grid row and cell.
		 */
		public static function get_cell_error_data($model, $db_column_name, $grid_field_name, $row_index, $page_index = null)
		{
			return array('widget'=>'grid', 'name'=>get_class($model).'['.$db_column_name.']', 'column'=>$grid_field_name, 'row'=>$row_index, 'page_index'=>$page_index);
		}
		
		protected function get_client_script_options()
		{
			$options = array();
			$options['sortable'] = $this->sortable && !$this->use_data_source;
			$options['scrollable'] = $this->scrollable && !$this->use_data_source;
			$options['name'] = get_class($this->model).'['.$this->column_name.']';
			$options['dataFieldName'] = $this->column_name;
			$options['focusFirst'] = $this->focus_first;
			
			if ($this->scrollable_viewport_class)
				$options['scrollableViewportClass'] = $this->scrollable_viewport_class;

			$options['allowAutoRowAdding'] = $this->allow_row_adding;
			$options['rowsDeletable'] = $this->deletable;
				
			if ($this->delete_row_message)
				$options['deleteRowMessage'] = $this->delete_row_message;

			$columns = array();
			$plain_columns = $this->get_plain_column_list();
			foreach ($plain_columns as $field=>$col_data)
			{
				$col_data['field'] = $field;
				$columns[] = $col_data;
			}
				
			$options['columns'] = $columns;
			$options['useDataSource'] = $this->use_data_source;
			if ($this->use_data_source)
			{
				$options['pageSize'] = $this->page_size;
				$options['recordCount'] = $this->get_data_source()->get_record_count();
			}

			return json_encode($options);
		}
		
		protected function prepare_data_fields($page_index = 0)
		{
			$this->view_data['columns'] = $this->columns;
			$this->view_data['container_id'] = $this->controller->formGetElementId('grid_container_'.$this->column_name, get_class($this->model));
			$this->view_data['pagination_container_id'] = $this->controller->formGetElementId('grid_pagination_container_'.$this->column_name, get_class($this->model));
			$this->view_data['message_container_id'] = $this->controller->formGetElementId('grid_message_container_'.$this->column_name, get_class($this->model));
			$this->view_data['tbody_id'] = $this->controller->formGetElementId('grid_body_'.$this->column_name, get_class($this->model));
			
			if (!$this->use_data_source)
				$grid_data = $this->model->{$this->column_name} ? $this->model->{$this->column_name} : array();
			else
			{
				$pagination = new Phpr_Pagination($this->page_size);
				$data_source = $this->get_data_source();
				$grid_data = $data_source->get_data_page($pagination, $page_index, post('phpr_grid_search'), post('phpr_grid_search_updated_records'));
				$this->view_data['data_source'] = $data_source;
				$this->view_data['pagination'] = $pagination;
			}

			$this->view_data['grid_data'] = $grid_data;
			$this->view_data['maintain_data_indexes'] = $this->maintain_data_indexes;
			$this->view_data['grid_widget'] = $this;
			$this->view_data['form_model'] = $this->model;
		}
	
		public function get_editor_object($model, $column_info)
		{
			if (!isset($column['editor_class']))
				throw new Phpr_ApplicationException(sprintf('Editor class is not defined for %s column', $column_info['title']));
			
			$class = $column['editor_class'];
			
			if (array_key_exists($class, $this->editor_cache))
				return $this->editor_cache[$class];

			return $this->editor_cache[$class] = new $class($model);
		}
		
		public function get_data_source()
		{
			if (!$this->use_data_source || !strlen($this->data_source_id))
				throw new Phpr_SystemException('Data source is not configured in the Grid Widget.');
			
			if ($this->data_source !== null)
				return $this->data_source;

			return $this->data_source = new Db_GridWidgetDataSource($this, $this->controller->formGetEditSessionKey(), $this->data_source_id);
		}
		
		public function set_message($message)
		{
			$this->message = $message;
		}

		public function get_message()
		{
			return $this->message;
		}

		public function has_column_groups()
		{
			if (!$this->use_data_source)
				return false;
			
			foreach ($this->columns as $column)
				if (isset($column['column_group']))
					return true;
					
			return false;
		}
		
		public function split_columns_by_groups()
		{
			$groups = array();
			
			foreach ($this->columns as $column_id=>$column)
			{
				$group_title = isset($column['column_group']) ? $column['column_group'] : 'Undefined group';
				if (!isset($groups[$group_title]))
					$groups[$group_title] = array();
					
				$groups[$group_title][$column_id] = $column;
			}
			
			return $groups;
		}
		
		public function get_plain_column_list()
		{
			if (!$this->has_column_groups())
				return $this->columns;
				
			$result = array();
			$groups = $this->split_columns_by_groups();
			foreach ($groups as $group_title=>$group_columns)
			{
				$index = 0;
				foreach ($group_columns as $column_id=>$column)
				{
					if ($index == 0)
					{
						if (!isset($column['cell_css_class']))
							$column['cell_css_class'] = null;

						$column['cell_css_class'] .= ' grid-left-separator';
					}
					$index++;

					$result[$column_id] = $column;
				}
			}
			
			return $result;
		}
	
		/*
		 * Event handlers
		 */
		
		protected function on_autocomplete($field, $model)
		{
			$result = $model->get_grid_autocomplete_values($field, post('autocomplete_column'), post('autocomplete_term'), post('autocomplete_row_data', array()));
			if (!is_array($result))
				$result = array();
				
			if (post('autocomplete_custom_values'))
			{
				$name_values = array();
				foreach ($result as $name=>$value)
					$name_values[] = array('label'=>$value, 'value'=>$name);
					
				$result = $name_values;
			}
				
			header('Content-type: application/json');
			echo json_encode($result);
		}
		
		protected function on_export_csv($field, $model)
		{
			try 
			{
				$this->cleanup_grid_export();
				$model_class = get_class($model);

				$data = Phpr::$request->post_array_item($model_class, $field, array());
				
				$file_content = array('data'=>$data, 'columns'=>$this->columns, 'iwork'=>post('iwork'), 'filename'=>$this->csv_file_name);
				$file_content = (object)$file_content;
			
				$key = time();
				$tmp_obj_name = 'csvexp_'.$model_class.'_'.$key.'.exp';
				if (!@file_put_contents(PATH_APP.'/temp/'.$tmp_obj_name, serialize($file_content)))
					throw new Phpr_ApplicationException('Error creating data file');

				$widget_model_class = null;
				if (method_exists($model, 'get_widget_model_class'))
					$widget_model_class = $model->get_widget_model_class();

				$url = root_url(Phpr::$router->getControllerRootUrl()).'/form_widget_request/on_get_csv_file/'.$this->column_name.'/'.$key.'/?widget_model_class='.urlencode($widget_model_class);
				Phpr::$response->redirect($url);
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		public function on_get_csv_file($key, $param_2 = null)
		{
			try
			{
				if (!preg_match('/^[0-9]+$/', $key))
					throw new Phpr_ApplicationException('File not found');

				$tmp_obj_name = 'csvexp_'.mb_strtolower(get_class($this->model)).'_'.$key.'.exp';
				$path = PATH_APP.'/temp/'.$tmp_obj_name;

				if (!file_exists($path))
					throw new Phpr_ApplicationException('File not found');
					
				$file = @file_get_contents($path);
				$contents = @unserialize($file);
				if (!$contents)
					throw new Phpr_ApplicationException('Invalid file format');

				header("Content-type: application/octet-stream");
				header('Content-Disposition: inline; filename="'.$contents->filename.'.csv"');
				header('Cache-Control: no-store, no-cache, must-revalidate');
				header('Cache-Control: pre-check=0, post-check=0, max-age=0');
				header('Accept-Ranges: bytes');
				header('Content-Length: '.filesize($path));
				header("Connection: close");

				$data = $contents->data;
				$columns = $contents->columns;
				$iwork = $contents->iwork;
				$separator = $iwork ? ',' : ';';

				$titles = array();
				foreach ($columns as $column)
					$titles[] = isset($column['title']) ? $column['title'] : null;

				Phpr_Files::outputCsvRow($titles, $separator);

				foreach ($data as $row_index=>$values)
				{
					$row = array();
					
					foreach ($columns as $column_id=>$column)
						$row[] = array_key_exists($column_id, $values) ? $values[$column_id] : null;

					Phpr_Files::outputCsvRow($row, $separator);
				}

				@unlink($path);
				die();
			}
			catch (exception $ex)
			{
				die ($ex->getMessage());
			}
		}
		
		protected function cleanup_grid_export()
		{
			$files = @glob(PATH_APP.'/temp/csvexp_*.exp');
			if (is_array($files) && $files)
			{
				foreach ($files as $filename) 
				{
					$matches = array();
					if (preg_match('/([0-9]+)\.exp$/', $filename, $matches))
					{
						if ((time()-$matches[1]) > 60)
							@unlink($filename);
					}
				}
			}
		}
		
		protected function on_display_csv_import_popup($field, $model)
		{
			$this->controller->form_unique_prefix = 'csv_grid_import';
			$this->controller->form_model_class = 'Db_CsvFileImport';
			$model = $this->view_data['form_model'] = new Db_CsvFileImport();
			$model->init_columns_info();
			$model->define_form_fields();
			$session_key = $this->view_data['form_session_key'] = $this->controller->formGetEditSessionKey();

			$files = $model->list_related_records_deferred('csv_file', $session_key);
			try
			{
				foreach ($files as $existing_file)
					$model->csv_file->delete($existing_file, $session_key);
			} catch (Exception $ex) {}

			$this->view_data['grid_widget'] = $this;
			$this->view_data['container_id'] = $this->controller->formGetElementId('grid_container_'.$this->column_name, get_class($this->model));
			$this->render_partial('load_grid_csv_file');
		}
		
		protected function on_csv_file_uploaded($field, $model)
		{
			try
			{
				$model = new Db_CsvFileImport();
				$files = $model->list_related_records_deferred('csv_file', $this->controller->formGetEditSessionKey());
				if (!$files->count)
					throw new Phpr_ApplicationException('File is not uploaded');

				$file = PATH_APP.$files[0]->getPath();
				if (!file_exists($file))
					throw new Phpr_ApplicationException('Unable to open the uploaded file');

				$handle = null;

				try
				{
					/*
					 * Validate and parse the file
					 */

					$delimeter = Phpr_Files::determineCsvDelimeter($file);
					if (!$delimeter)
						throw new Phpr_ApplicationException('Unable to detect a delimiter');

					$handle = @fopen($file, "r");
					if (!$handle)
						throw new Phpr_ApplicationException('Unable to open the uploaded file');

					$file_data = array();
					$columns = array();
					$counter = 0;

					while (($data = fgetcsv($handle, 10000, $delimeter)) !== FALSE) 
					{
						if (Phpr_Files::csvRowIsEmpty($data))
							continue;

						$counter++;

						if ($counter == 1)
							$columns = $data;
						else
							$file_data[] = $data;
					}
					
					/*
					 * Generate column map and import data
					 */

					foreach ($this->columns as $column_id=>$column_info)
					{
						$column_title = mb_strtoupper(trim($column_info['title']));
						foreach ($columns as $file_column_index=>$file_column)
						{
							$file_column = mb_strtoupper(trim($file_column));
							if ($file_column == $column_title)
								$column_map[$column_id] = $file_column_index;
						}
					}

					$fetched_column_data = array();
					foreach ($file_data as $data_row)
					{
						$fetched_data_row = array();
						
						foreach ($column_map as $column_id=>$column_index)
						{
							if (array_key_exists($column_index, $data_row))
								$fetched_data_row[$column_id] = $data_row[$column_index];
						}

						if (!Phpr_Files::csvRowIsEmpty($fetched_data_row))
							$fetched_column_data[] = $fetched_data_row;
					}

					$this->model->$field = $fetched_column_data;

					/*
					 * Render the field
					 */
					
					$this->prepare_data_fields();
					
					$_POST['form_unique_prefix'] = '';

					$this->controller->preparePartialRender($this->view_data['container_id']);
					$this->render_partial('data_table');
				} catch (Exception $ex)
				{
					if ($handle)
						@fclose($handle);

					throw $ex;
				}
			}
			catch (Exception $ex)
			{
				Phpr::$response->ajaxReportException($ex, true, true);
			}
		}
		
		protected function on_display_grid_help($field, $model)
		{
			$this->view_data['grid_widget'] = $this;
			
			if (!$this->help_partial_path)
				$this->render_partial('grid_help');
			else
				$this->controller->renderPartial($this->help_partial_path);
		}

		protected function on_show_popup_editor($field, $model)
		{
			$column = post('phpr_popup_column');
			$editor_class = $this->columns[$column]['editor_class'];
			
			$editor = new $editor_class($model);
			$editor->render_popup_contents($this->columns[$column], $this->controller, $field);
		}
		
		protected function on_editor_event($field, $model)
		{
			$column = post('phpr_grid_column');
			$editor_class = $this->columns[$column]['editor_class'];
			
			$editor = new $editor_class($model);
			$editor->handle_event(post('phpr_grid_editor_event'), $this->model, $field, $this->columns[$column], $this->controller, $column);
		}

		protected function on_navigate_to_page($field, $model)
		{
			$model_class = get_class($model);
			$data = Phpr::$request->post_array_item($model_class, $field, array());
			$data_source = $this->get_data_source();

			$data_source->commit($data);

			if (post('phpr_grid_event_name'))
				Backend::$events->fireEvent(post('phpr_grid_event_name'), $data_source);

			if (post('phpr_append_row'))
				$data_source->append_row($this->columns, post('phpr_new_row_key'));

			if (post('phpr_delete_row'))
				$data_source->delete_row(post('phpr_delete_row'));
				
			if (post('phpr_grid_event'))
				Backend::$events->fireEvent(post('phpr_grid_event'), $this, $data_source);
			
			$this->prepare_data_fields(post('phpr_page_index', 0));
			
			$this->controller->preparePartialRender($this->view_data['tbody_id']);
			$this->render_partial('data_table_body');

			$this->controller->preparePartialRender($this->view_data['pagination_container_id']);
			$this->render_partial('pagination');
			
			$this->controller->preparePartialRender($this->view_data['message_container_id']);
			$this->render_partial('message');
		}
	}

?>