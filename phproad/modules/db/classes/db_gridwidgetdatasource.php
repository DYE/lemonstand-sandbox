<?

	/*
	 * This class represents a memory persistent data source for 
	 * the grid widget.
	 */
	class Db_GridWidgetDataSource
	{
		public $widget;
		
		protected $session_key;
		protected $data_source_id;
		
		public function __construct($widget, $session_key, $data_source_id)
		{
			$this->widget = $widget;
			$this->session_key = $session_key;
			$this->data_source_id = $data_source_id;
		}
		
		/**
		 * Populates data source.
		 * @param mixed $data An array representing the data.
		 */
		public function set_data(&$data)
		{
			$data_id = $this->get_session_data_id();
			
			if (Phpr::$session->has($data_id))
				Phpr::$session->remove($data_id);
			
			Phpr::$session->set($data_id, $data);
		}
		
		public function get_data()
		{
			$data_id = $this->get_session_data_id();
			return isset($_SESSION[$data_id]) ? $_SESSION[$data_id] : array();
		}
		
		public static function dispose_session_data($session_key)
		{
			$keys = array_keys($_SESSION);
			foreach ($keys as $key)
			{
				if (strpos($key, 'grid-data-'.$session_key) === 0)
					Phpr::$session->remove($key);
			}
		}
		
		public static function get_field_data($field, $internal = false)
		{
			if (!is_array($field))
			{
				if (!$internal)
					return trim($field);
					
				return null;
			}
			
			return $internal ? trim($field[1]) : trim($field[0]);
		}
		
		public static function get_row_data($row, $internal = false)
		{
			$result = array();
			foreach ($row as $field=>$value)
				$result[$field] = self::get_field_data($value, $internal);

			return $result;
		}
		
		public function get_data_page($pagination, $page_index, $search_term = null, $search_include_records = null)
		{
			$data_id = $this->get_session_data_id();
			$data = isset($_SESSION[$data_id]) ? $_SESSION[$data_id] : array();
			
			if (strlen($search_term))
				$this->apply_search($search_term, $data, $search_include_records);

			$pagination->setRowCount(count($data));
			if ($page_index !== 'last') 
			    $pagination->setCurrentPageIndex($page_index);
			else
				$pagination->setCurrentPageIndex($pagination->getPageCount()-1);
			    
			$page_data = array_slice($data, $pagination->getFirstPageRowIndex(), $pagination->getPageSize(), true);
			$processed = Backend::$events->fireEvent('core:onPrepareFormGridWidgetDataPage', $this, $page_data);
			foreach ($processed as $processed_page)
				if ($processed_page)
					return $processed_page;
			
			return $page_data;
		}
		
		public function get_record_count()
		{
			$data_id = $this->get_session_data_id();
			return isset($_SESSION[$data_id]) ? count($_SESSION[$data_id]) : 0;
		}
		
		public function commit(&$new_data)
		{
			$data_id = $this->get_session_data_id();
			$data = isset($_SESSION[$data_id]) ? $_SESSION[$data_id] : array();

			foreach ($new_data as $record_id=>$row)
			{
				if (array_key_exists($record_id, $data))
					$data_row = $data[$record_id];
				else
					$data_row = array();
				
				foreach ($row as $name=>$value)
				{
					if (substr($name, -9) == '_internal')
						continue;

					$internal_value = array_key_exists($name.'_internal', $row) ? $row[$name.'_internal'] : null;
					$data_row[$name] = array($value, $internal_value);
				}

				$data[$record_id] = $data_row;
			}
			
			Phpr::$session->set($data_id, $data);
		}
		
		public function append_row($columns, $data_key)
		{
			$data_id = $this->get_session_data_id();
			$data = isset($_SESSION[$data_id]) ? $_SESSION[$data_id] : array();

			$new_row = array();
			foreach ($columns as $key=>$column)
				$new_row[$key] = isset($column['default_text']) ? $column['default_text'] : null;
				
			$data[$data_key] = $new_row;
			Phpr::$session->set($data_id, $data);
		}
		
		public function delete_row($data_key)
		{
			$data_id = $this->get_session_data_id();
			$data = isset($_SESSION[$data_id]) ? $_SESSION[$data_id] : array();
			
			if (array_key_exists($data_key, $data))
				unset($data[$data_key]);

			Phpr::$session->set($data_id, $data);
		}
		
		protected function get_session_data_id()
		{
			return 'grid-data-'.$this->session_key.'-'.$this->data_source_id;
		}
		
		protected function apply_search($search_string, &$data, $search_include_records)
		{
		    $len = strlen($search_string);
			$search_include_records = strlen($search_include_records) ? explode(',', $search_include_records) : array();
		    
			if ($len)
			{

				$words = explode(' ', $search_string);
				
				$result = array();
				$words_filtered = array();
				
				foreach ($words as $word)
				{
					if (!strlen($word))
						continue;
						
					$words_filtered[] = mb_strtolower($word);
				}

				foreach ($data as $index=>$record)
				{
					// If the record index is in the list of $search_include_records,
					// just include it and skip to the next record
					if (in_array($index, $search_include_records))
					{
						$result[$index] = $record;
						continue;
					}
					
					foreach ($record as $field_name=>$field_value)
					{
						$field_value = is_array($field_value) ? $field_value[0] : $field_value;
						
						$field_value = mb_strtolower($field_value);

						foreach ($words_filtered as $word)
						{
							// Word is not found in the field, skip to next field
							if (strpos($field_value, $word) === false)
								continue 2;
						}
						
						// All words found in the field, add record to the result,
						// Skip to the next record
						$result[$index] = $record;
						continue 2;
					}
				}

				$data = $result;
			}
		}
	}

?>