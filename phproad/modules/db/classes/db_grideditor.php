<?

	abstract class Db_GridEditor
	{
		protected $model;
		
		public function __construct($model)
		{
			$this->model = $model;
		}
		
		abstract public function format_row_content($row_index, $column_info, $field_name, $row_data, $session_key);
		
		public function render_popup_contents($column_info, $controller, $field_name)
		{
		}
		
		public function handle_event($event, $model, $field_name, $column_info, $controller, $column_name)
		{
			if (substr($event, 0, 2) != 'on')
				throw new Phpr_SystemException('Invalid grid editor event name: '.$event);
				
			if (!method_exists($this, $event))
				throw new Phpr_SystemException(sprintf('Event handler %s not found in grid editor %s.', $event, get_class($this)));
				
			$this->$event($field_name, $model, $column_info, $controller, $column_name);
		}
	}

?>