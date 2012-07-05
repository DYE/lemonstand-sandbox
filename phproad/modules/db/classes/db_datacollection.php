<?

	/**
	 * Represents a collection of ActiveRecord objects.
	 * Objects of this class are returned by {@link Db_ActiveRecord::find_all()} method 
	 * and some other methods and {@link http://lemonstandapp.com/docs/creating_data_relations/ relations}.
	 * @documentable
	 * @author LemonStand eCommerce Inc.
	 * @package core.classes
	 */
	class Db_DataCollection implements ArrayAccess, IteratorAggregate, Countable 
	{
		public $objectArray = array();
		
		/**
		 * @var Db_ActiveRecord A reference to the parent model.
		 * This field is set only for data collections created by relations.
		 * @documentable.
		 */
		public $parent = null;
		public $relation = '';

		/**
		 * Constructor
		 * Create collection from array if passed
		 *
		 * @param mixed[] $array
		 */
		public function __construct($array = null) 
		{
			if (is_array($array))
				$this->objectArray = $array;
		}
	
		/**
		 * These are the required iterator functions
		 */
	 
		function offsetExists($offset) 
		{
			if (isset($this->objectArray[$offset]))
				return true;
			else
				return false;
		}
	 
		function offsetGet($offset) 
		{
			if ($this->offsetExists($offset))
				return $this->objectArray[$offset];
			else
				return (false);
		}
	 
		function offsetSet($offset, $value) 
		{
			if (!is_null($this->parent) && ($this->parent instanceof Db_ActiveRecord))
				$this->parent->bind($this->relation, $value);
			
			if($offset)
				$this->objectArray[$offset] = $value;
			else
				$this->objectArray[] = $value;
		}
	 
		function offsetUnset($offset) 
		{
			unset($this->objectArray[$offset]);
		}
	 
		function getIterator() 
		{
			return new ArrayIterator($this->objectArray);
		}

		/**
		 * End required iterator functions
		 */

		/**
		 * Returns a first element in the collection.
		 * @documentable
		 * @return Db_ActiveRecord Returns the model object or NULL.
		 */
		function first() 
		{
			if (count($this->objectArray) > 0)
				return $this->objectArray[0];
			else
				return null;
		}

		/**
		 * Returns the number of records in the collection.
		 * @documentable
		 * @return integer
		 */
		function count() 
		{
			return count($this->objectArray);
		}

		function position(&$object) 
		{
			return array_search($object, $this->objectArray);
		}
	
		function limit($count) 
		{
			$limit = 0;
			$limited = array();
			
			foreach($this->objectArray as $item) 
			{
				if ($limit++ >= $count) break;
				$limited[] = $item;
			}
			return new Db_DataCollection($limited);
		}

		function skip($count)
		{
			$skipped = array();
			foreach($this->objectArray as $item) 
			{
				if ($count-- > 0) 
					continue;
					
				$skipped[] = $item;
			}
			
			return new Db_DataCollection($skipped);
		}

		function except($value, $key = 'id') 
		{
			return $this->exclude(array($value), $key);
		}

		function find($value, $field = 'id')
		{
			foreach($this->objectArray as $object)
				if ($object->{$field} == $value) return $object;

			return null;
		}

		function find_by($field = 'id', $value) 
		{
			return $this->find($value, $field);
		}

		/**
		 * Convert the collection to an array.
		 * This method can return a list of model objects or their fields, depending on the parameter values.
		 * If the <em>$field</em> parameter is NULL, the method returns an array of model objects.
		 * Alternatively you can specify the column name and the method will return a list of this column values.
		 *
		 * The <em>$key</em> field determines which values should be used as the array keys. By default it matches
		 * the record index in the collection. If a field name passed, the field value is used as the array keys.
		 * 
		 * <pre>
		 * $array = $collection->as_array(); // Array of model objects
		 * $array = $collection->as_array('name'); // Array of 'name' column values
		 * $array = $collection->as_array('name', 'id'); // Array keys match the 'id' column values.
		 * </pre>
		 * @documentable
		 * @param string $field Specifies a name of field which values should be used as array element values.
		 * @param string $key_field Specifies a of the field which values should be used as array element keys.
		 * @return array Returns an array of model objects or scalar values.
		 */
		function as_array($field = null, $key_field = null)
		{
			if ($field === null && $key_field === null)
				return $this->objectArray;

			$result = array();
			foreach($this->objectArray as $index=>$item)
			{
				$value = $field === null ? $item : $item->$field;
				$key = $key_field === null ? $index : $item->$key_field;
				
				$result[$key] = $value;
			}

			return $result;
		}
		
		/**
		 * Returns an array with keys matching records primary keys
		 * @return mixed[]
		 */
		function as_mapped_array()
		{
			if (!count($this->objectArray))
				return $this->objectArray;
			
			return $this->as_array(null, $this->objectArray[0]->primary_key);
		}

		/**
		 * Convert collection to associative array
		 *
		 * @param string|mixed[] $field			 optional
		 * @param string $key optional
		 * @param string $subkey			optional
		 * @return mixed[]
		 */
		function as_dict($field = '', $key = '', $subkey = '') 
		{
			if ($field == '')
				return $this->objectArray;

			$result = array();
			foreach($this->objectArray as $item) 
			{
				$k = $key;
				if ($k == '') 
					$k = $item->primary_key;
					
				if (is_string($field)) 
				{
					if ($subkey != '') 
					{
						if (!isset($result[$item->$k]))
							$result[$item->$k] = array();

						$result[$item->$k][$item->$subkey] = $item->$field;
					} else
						$result[$item->$k] = $item->$field;
				} 
				elseif (is_array($field)) 
				{
					$res = array();
					foreach($field as $model_field)
						$res[$model_field] = $item->$model_field;

					if (!isset($result[$item->$k]))
						$result[$item->$k] = array();

					if ($subkey == '')
						$result[$item->$k][] = $res;
					else
						$result[$item->$k][$item->$subkey] = $res;
				} 
				else
					continue;
			}
			return $result;
		}
	
		function exclude($values, $key = 'id') 
		{
			$result = array();
			foreach($this->objectArray as $item) 
			{
				if (!in_array($item->{$key}, $values))
					$result[] = $item;
			}
			
			$this->objectArray = $result;
			return $this;
		}

		function has($value, $field) 
		{
			$items = $this->as_array($field);
			return in_array($value, $items);
		}
	
		/**
		 * Magic method: get properties from first object in collection
		 *
		 * @param string $key
		 * @return mixed
		 */
		function __get($key) 
		{
			switch($key) 
			{
				case "first":
					return $this->first();
				case "count":
					return $this->count();
			}
			
			if (count($this->objectArray) > 0)
				return @$this->objectArray[0]->$key;

			return null;
		}
	
		/**
		 * Magic method: call methods from first object in collection
		 *
		 * @param string $name
		 * @param mixed[] $arguments
		 * @return mixed
		 */
		function __call($name, $arguments) 
		{
			if (count($this->objectArray) > 0)
				return call_user_func_array(array(&$this->objectArray[0], $name), $arguments);
				
			return null;
		}

		/**
		 * Adds an object to the collection.
		 * This method is applicable only when the collection is created by a model's relation.
		 * The model should be saved in order the relation changes to apply.
		 * @documentable
		 * @param Db_ActiveRecord $record Specifies a record to add.
		 * @param string $deferred_session_key Optional deferred session key.
		 * If the key is specified, it should be used in {@link Db_ActiveRecord::save()} method call.
		 */
		public function add($record, $deferred_session_key=null)
		{
			if (is_null($this->parent) || !($this->parent instanceof Db_ActiveRecord)) return;
			$this->parent->bind($this->relation, $record, $deferred_session_key);
		}

		/**
		 * Deletes an object from the collection
		 * This method is applicable only when the collection is created by a model's relation.
		 * The model should be saved in order the relation changes to apply.
		 * @documentable
		 * @param Db_ActiveRecord $record Specifies a record to remove.
		 * @param string $deferred_session_key Optional deferred session key.
		 * If the key is specified, it should be used in {@link Db_ActiveRecord::save()} method call.
		 */
		public function delete($record, $deferred_session_key=null)
		{
			if (is_null($this->parent) || !($this->parent instanceof Db_ActiveRecord)) 
				return;
				
			$this->parent->unbind($this->relation, $record, $deferred_session_key);
		}

		/**
		 * Removes all objects from the collection
		 * This method is applicable only when the collection is created by a model's relation.
		 * The model should be saved in order the relation changes to apply.
		 * @documentable
		 * @param string $deferred_session_key Optional deferred session key.
		 * If the key is specified, it should be used in {@link Db_ActiveRecord::save()} method call.
		 */
		public function clear($deferred_session_key=null)
		{
			if (is_null($this->parent) || !($this->parent instanceof Db_ActiveRecord)) 
				return;
				
			$this->parent->unbind_all($this->relation, $deferred_session_key);
			$this->objectArray = array();
		}

		public function item($key) 
		{
			if (isset($this->objectArray[$key]))
				return $this->objectArray[$key];
				
			return null;
		}
	
		public function total() 
		{
			if ($this->parent == null) 
				return count($this);
				
			if (!isset($this->_total))
				$this->_total = $this->parent->count();

			return $this->_total;
		}

		function sql_count() 
		{
			return (!is_null($this->parent) ? $this->parent->count() : 0);
		}
	}
?>