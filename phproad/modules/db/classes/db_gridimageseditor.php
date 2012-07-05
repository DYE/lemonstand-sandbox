<?

	class Db_GridImagesEditor extends Db_GridEditor
	{
		public function __construct($model)
		{
			if (!($model instanceof Db_MemoryCacheable))
				throw new Phpr_SystemException(sprintf('Model %s should implement the Db_MemoryCacheable interface in order to be used with Db_GridImages editor'), get_class($model));
			
			parent::__construct($model);
		}
		
		public function format_row_content($row_index, $column_info, $field_name, $row_data, $session_key)
		{
			$images = $this->get_image_list($column_info, $row_index, $session_key);
			
			if ($images->count === 0)
				return '0 images';
				
			return $images->count.' image(s)';
		}
		
		public function render_popup_contents($column_info, $controller, $field_name)
		{
			if (!$this->model)
				throw new Phpr_ApplicationException('Record not found.');
			
			$controller->formSetViewDataElement('form_model', $this->model);
			$images = $this->get_image_list($column_info, post('phpr_grid_row_index'), $controller->formGetEditSessionKey());
			$controller->renderPartial(PATH_APP.'/phproad/modules/db/partials/_images_editor_content.htm', array(
				'images'=>$images,
				'db_field_name'=>$column_info['images_field'],
				'field_name'=>$field_name,
				'grid_column'=>post('phpr_popup_column'),
				'form_model'=>$this->model,
				'row_index'=>post('phpr_grid_row_index')
			));
		}
		
		protected function get_image_list($column_info, $row_index, $session_key)
		{
			$row_model = $this->model->get_record_cached($row_index < 0 ? null : $row_index);
			if (!$row_model)
				throw new Phpr_ApplicationException('Record not found.');
			
			if (!isset($column_info['images_field']))
				throw new Phpr_SystemException(sprintf('Images field is not defined for %s column', $column_info['title']));

			return $row_model->list_related_records_deferred($column_info['images_field'], $this->get_record_session_key($session_key, $row_index));
		}
		
		protected function get_record_session_key($session_key, $row_index)
		{
			return $session_key.'-'.$row_index;
		}
		
		/*
		 * Event handlers
		 */
		
		protected function on_update_image_list($field, $model, $column_info, $controller, $column_name)
		{
			$row_index = post('phpr_row_index');
			$row_model = $this->model->get_record_cached($row_index < 0 ? null : $row_index);
			
			$images = $this->get_image_list($column_info, post('phpr_row_index'), $controller->formGetEditSessionKey());
			$controller->renderPartial(PATH_APP.'/phproad/modules/db/partials/_images_editor_image_list.htm', array(
				'images'=>$images,
				'form_model'=>$row_model,
				'field_name'=>$field,
				'grid_column'=>$column_name
			));
		}
		
		protected function on_delete_image($field, $model, $column_info, $controller, $column_name)
		{
			$row_index = post('phpr_row_index');
			$row_model = $this->model->get_record_cached($row_index < 0 ? null : $row_index);

			if ($file = Db_File::create()->find(post('file_id')))
				$row_model->{$column_name}->delete($file, $this->get_record_session_key($controller->formGetEditSessionKey(), $row_index));
			
			$this->on_update_image_list($field, $model, $column_info, $controller, $column_name);
		}
		
		protected function on_get_cell_text($field, $model, $column_info, $controller, $column_name)
		{
			echo $this->format_row_content(post('phpr_row_index'), $column_info, $field, array(), $controller->formGetEditSessionKey());
		}
	}

?>