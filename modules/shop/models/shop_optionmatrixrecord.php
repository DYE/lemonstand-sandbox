<?php

	/**
	 * Represents {@link http://lemonstandapp.com/docs/understanding_option_matrix/ Option Matrix} record.
	 * Usually you don't need to access objects of this class directly. 
	 * @documentable
	 * @property integer $id Specifies the record database identifier.
	 * @property Db_DataCollection $images A collection of images assigned to the category. 
	 * Each element in the collection is an object of the {@link Db_File} class. You can use this property directly to 
	 * output category images, or use the {@link Shop_Category::image_url() image_url()} method. Not proxiable.
	 * @property boolean $disabled Determines whether the record is disabled.
	 * @property string $sku Specifies the product SKU.
	 * @property float $base_price Specifies the base price of the record.
	 * @property float $cost Specifies the product cost.
	 * @property boolean $on_sale Determines whether the product is on sale.
	 * @property string $sale_price_or_discount. Specifies the sale price or discount in the following format: 10, -10, 10%.
	 * @property integer $in_stock Specifies the number of items in stock.
	 * @property Phpr_DateTime $expected_availability_date Specifies the expected availability date.
	 * @property float $weight Specifies the product weight.
	 * @property float $width Specifies the product width.
	 * @property float $height Specifies the product height.
	 * @property float $depth Specifies the product depth.
	 * @see http://lemonstandapp.com/docs/understanding_option_matrix/ Understanding Option Matrix
	 * @package shop.models
	 * @author LemonStand eCommerce Inc.
	 */
	class Shop_OptionMatrixRecord extends Db_ActiveRecord implements Db_MemoryCacheable
	{
		public $table_name = 'shop_option_matrix_records';
		
		protected static $record_cache = array();
		protected static $supported_field_cache = array();

		public $has_many = array(
			'images'=>array('class_name'=>'Db_File', 'foreign_key'=>'master_object_id', 'conditions'=>"master_object_class='Shop_OptionMatrixRecord' and field='images'", 'order'=>'sort_order, id', 'delete'=>true),
			'record_options'=>array('class_name'=>'Shop_OptionMatrixOption', 'delete'=>true, 'order'=>'shop_option_matrix_options.id', 'foreign_key'=>'matrix_record_id')
		);
		
		public $api_columns = array();

		public $custom_columns = array(
			'grid_data'=>db_text
		);
		
		public static function create()
		{
			return new self();
		}
		
		public function define_columns($context = null)
		{
			$front_end = Db_ActiveRecord::$execution_context == 'front-end';

			$this->define_column('grid_data', 'Option Matrix')->invisible()->validation();
			$this->define_multi_relation_column('images', 'images', 'Images', $front_end ? null : '@name')->invisible();
		}
		
		public function define_form_fields($context = null)
		{
			if ($context != 'grid')
				$this->add_form_field('images')->renderAs(frm_file_attachments)->renderFilesAs('image_list');
			
			$columns = array();
			
			/*
			 * General parameters
			 */
			$columns['disabled'] = array('title'=>'Disabled', 'type'=>'checkbox', 'width'=>85, 'checked_class'=>'status-disabled', 'header_control'=>true, 'column_group'=>'Product');
			$columns['sku'] = array('title'=>'SKU', 'type'=>'text', 'width'=>100, 'column_group'=>'Product');
			$columns['images'] = array('title'=>'Images', 'type'=>'popup', 'editor_class'=>'Db_GridImagesEditor', 'images_field'=>'images', 'width'=>100, 'default_text'=>'0 images', 'column_group'=>'Product');
			
			/*
			 * Pricing
			 */

			$columns['base_price'] = array('title'=>'Price', 'type'=>'text', 'align'=>'right', 'editor_class'=>'Shop_GridTierPriceEditor', 'width'=>100, 'column_group'=>'Pricing');
			$columns['cost'] = array('title'=>'Cost', 'type'=>'text', 'align'=>'right', 'width'=>100, 'column_group'=>'Pricing');
			$columns['on_sale'] = array('title'=>'On Sale', 'type'=>'checkbox', 'width'=>80, 'header_control'=>true, 'column_group'=>'Pricing');
			$columns['sale_price_or_discount'] = array('title'=>'Sale Price or Discount', 'type'=>'text', 'align'=>'right', 'width'=>150, 'validation_type'=>'discount', 'column_group'=>'Pricing');

			/*
			 * Inventory
			 */

			$columns['in_stock'] = array('title'=>'In Stock', 'type'=>'text', 'width'=>80, 'align'=>'right', 'column_group'=>'Inventory Tracking');
			$columns['expected_availability_date'] = array('title'=>'Expected date', 'align'=>'right', 'type'=>'text', 'width'=>95, 'editor_class'=>'Db_GridDateEditor', 'column_group'=>'Inventory Tracking');

			/*
			 * Shipping
			 */
			
			$columns['weight'] = array('title'=>'Weight', 'type'=>'text', 'align'=>'right', 'width'=>60, 'column_group'=>'Shipping');
			$columns['width'] = array('title'=>'Width', 'type'=>'text', 'align'=>'right', 'width'=>50, 'column_group'=>'Shipping');
			$columns['height'] = array('title'=>'Height', 'type'=>'text', 'align'=>'right', 'width'=>50, 'column_group'=>'Shipping');
			$columns['depth'] = array('title'=>'Depth', 'type'=>'text', 'align'=>'right', 'width'=>50, 'column_group'=>'Shipping');
			
			/*
			 * API
			 */
			
			$new_api_columns = Backend::$events->fireEvent('shop:onExtendOptionMatrix');

			if ($new_api_columns && is_array($new_api_columns))
			{
				foreach ($new_api_columns as $api_columns_definition)
				{
					if (is_array($api_columns_definition))
					{
						foreach ($api_columns_definition as $column_id=>$column_configuration)
						{
							$columns[$column_id] = $column_configuration;
							$this->api_columns[$column_id] = $column_configuration;
						}
					}
				}
			}
			
			/*
			 * Set validation types basing on the database column types
			 */
			
			foreach ($columns as $column_name=>&$column)
			{
				if (!isset($column['validation_type']))
				{
					$db_column = $this->column($column_name);
					if ($db_column)
						$column['validation_type'] = $db_column->type;
				}
			}

			/*
			 * Add grid form field
			 */
			
			$this->add_form_field('grid_data')->renderAs(frm_widget, array(
				'class'=>'Db_GridWidget', 
				'sortable'=>true,
				'scrollable'=>true,
				'maintain_data_indexes'=>true,
				'enable_csv_operations'=>false,
				'enable_search'=>true,
				'disable_toolbar'=>false,
				'csv_file_name'=>'option-matrix',
				'columns'=>$columns,
				'use_data_source'=>true,
				'data_source_id'=>'option-matrix-grid-data',
				'horizontal_scroll'=>true,
				'page_size'=>15,
				'focus_first'=>true,
				'title_word_wrap'=>false,
				'column_group_configuration'=>array(
					'Options'=>array('class'=>'key')
				)
			))->noLabel();
		}
		
		public function reinitialize($record_id, $skip_fields)
		{
			$this->reset_relations();
			$this->reset_plain_fields($skip_fields);

			if ($record_id === null)
				$this->set_new_record();
			else {
				$row_data = Db_DbHelper::queryArray('select * from shop_option_matrix_records where id=?', $record_id);
				$this->fill_external($row_data);
			}
		}

		private function eval_tier_price($product, $customer_group_id, $quantity)
		{
			if (!strlen($this->base_price))
				return $product->eval_tier_price($customer_group_id, $quantity);
			
			$product = is_object($product) ? $product : Shop_Product::find_by_id($product);
			$price = Shop_TierPrice::eval_tier_price($this->tier_price_compiled, $customer_group_id, $quantity, $product->name, $this->base_price, $product->tier_price_compiled);

			if (!strlen($price))
				return $product->eval_tier_price($customer_group_id, $quantity);
				
			return $price;
		}
		
		public function list_group_price_tiers($product, $group_id)
		{
			$product = is_object($product) ? $product : Shop_Product::find_by_id($product);
			$base_price = $this->base_price;
			if (!$base_price)
				return $product->list_group_price_tiers($group_id);

			return Shop_TierPrice::list_group_price_tiers($this->tier_price_compiled, $group_id, $product->name, $base_price, $product->tier_price_compiled);
		}
		
		public function set_compiled_price_rules($price_rules, $rule_map)
		{
			$this->price_rules_compiled = serialize($price_rules);
			$this->price_rule_map_compiled = serialize($rule_map);

			Db_DbHelper::query('update shop_option_matrix_records set price_rules_compiled=:price_rules_compiled, price_rule_map_compiled=:price_rule_map_compiled where id=:id', array(
				'price_rules_compiled'=>$this->price_rules_compiled,
				'price_rule_map_compiled'=>$this->price_rule_map_compiled,
				'id'=>$this->id
			));
		}
		
		/**
		 * Determines whether a specified field can be loaded from the record.
		 * Not supported properties are loaded from a base product.
		 * @documentable
		 * @param string $field_name Specifies the field name.
		 * @return mixed Returns TRUE if the field could be loaded from the record. 
		 * Otherwise returns name of a product field. 
		 */
		public function is_property_supported($field_name)
		{
			if (array_key_exists($field_name, self::$supported_field_cache))
				return self::$supported_field_cache[$field_name];
			
			if ($this->has_column($field_name) || isset($this->has_models[$field_name]))
				return $supported_field_cache[$field_name] = true;
				
			$supported_fields = array(
				'price',
				'sale_price',
				'is_on_sale',
				'is_out_of_stock',
				'volume'
			);
			if (in_array($field_name, $supported_fields))
				return $supported_field_cache[$field_name] = true;
				
			return $field_name;
		}
		
		/**
		 * A static method for returning the record sale price. This method
		 * is used internally.
		 */
		public static function get_sale_price_static($product, $test_record, $data, $customer_group_id = null, $no_tax = false)
		{
			$test_record->on_sale = $data->on_sale;
			$test_record->sale_price_or_discount = $data->sale_price_or_discount;
			$test_record->price_rules_compiled = $data->price_rules_compiled;
			$test_record->tier_price_compiled = $data->tier_price_compiled;
			$test_record->base_price = $data->base_price;

			return $test_record->get_sale_price($product, 1, $customer_group_id, $no_tax);
		}
		
		/**
		 * A static method for returning the record price. This method
		 * is used internally.
		 */
		public static function get_price_static($product, $test_record, $data, $customer_group_id = null, $no_tax = false)
		{
			$test_record->on_sale = $data->on_sale;
			$test_record->sale_price_or_discount = $data->sale_price_or_discount;
			$test_record->price_rules_compiled = $data->price_rules_compiled;
			$test_record->tier_price_compiled = $data->tier_price_compiled;
			$test_record->base_price = $data->base_price;

			return $test_record->get_price($product, 1, $customer_group_id, $no_tax);
		}
		
		/**
		 * Copies Option Matrix records from one product to another
		 */
		public static function copy_records_to_product($src_product, $dest_product)
		{
			$options = Db_DbHelper::objectArray('select id, name from shop_custom_attributes where product_id=:product_id', array('product_id'=>$dest_product->id));
			$product_option_ids = array();
			foreach ($options as $option)
				$product_option_ids[$option->name] = $option->id;
			
			/*
			 * Load the list of product Option Matrix records
			 */
			
			$records = Db_DbHelper::queryArray('select * from shop_option_matrix_records where product_id=:product_id', array('product_id'=>$src_product->id));
			$record_fields_insert_str = null;
			$record_fields_values_str = null;
			
			$record_option_fields_insert_str = null;
			$record_option_fields_values_str = null;
			
			foreach ($records as $record)
			{
				if ($record_fields_insert_str === null)
				{
					$record_field_map = array();
					
					foreach ($record as $field=>$value)
					{
						if ($field == 'id')
							continue;

						$record_field_map[] = $field;
					}
						
					$record_fields_insert_str = implode(', ', $record_field_map);
					$record_fields_values_str = ':'.implode(', :', $record_field_map);
				}
				
				$record['product_id'] = $dest_product->id;

				/*
				 * Create Option Matrix record
				 */

				Db_DbHelper::query("insert into shop_option_matrix_records($record_fields_insert_str) values ($record_fields_values_str)", $record);
				$new_record_id = mysql_insert_id();
				
				/*
				 * Copy Option Matrix option records
				 */
				
				$record_options = Db_DbHelper::queryArray('
					select 
						shop_option_matrix_options.*,
						shop_custom_attributes.name
					from 
						shop_option_matrix_options, 
						shop_custom_attributes 
					where
						matrix_record_id=:id
						and shop_custom_attributes.id=shop_option_matrix_options.option_id
				', 
				array('id'=>$record['id']));
				
				foreach ($record_options as $record_option)
				{
					if (!array_key_exists($record_option['name'], $product_option_ids))
						continue;
						
					if ($record_option_fields_insert_str === null)
					{
						$record_field_map = array();

						foreach ($record_option as $field=>$value)
						{
							if ($field == 'id' || $field == 'name')
								continue;

							$record_field_map[] = $field;
						}

						$record_option_fields_insert_str = implode(', ', $record_field_map);
						$record_option_fields_values_str = ':'.implode(', :', $record_field_map);
					}
					
					$record_option['matrix_record_id'] = $new_record_id;
					$record_option['option_id'] = $product_option_ids[$record_option['name']];

					unset($record_option['id']);
					Db_DbHelper::query("insert into shop_option_matrix_options($record_option_fields_insert_str) values ($record_option_fields_values_str)", $record_option);
				}
				
				/*
				 * Copy Option Matrix record files
				 */
				
				$files_ids = Db_DbHelper::scalarArray('
					select 
						id 
					from 
						db_files 
					where 
						master_object_class=:master_object_class 
						and master_object_id=:master_object_id
				', array(
					'master_object_class'=>'Shop_OptionMatrixRecord',
					'master_object_id'=>$record['id']
				));
				
				foreach ($files_ids as $file_id)
				{
					$file = Db_File::create()->find($file_id);
					if (!$file)
						continue;
					
					try
					{
						$file_copy = $file->copy();
						$file_copy->master_object_id = $new_record_id;
						$file_copy->master_object_class = 'Shop_OptionMatrixRecord';
						$file_copy->field = $file->field;;
						$file_copy->save();
					} catch (exception $ex) {}
				}
			}
		}
		
		/*
		 * Interface methods
		 */
		
		/**
		 * Returns Option Matrix record by option values.
		 * The <em>$options</em> parameter should contain a list of product options and option values in the
		 * following format: ['Option name 1'=>'option value 1', 'Option name 2'=>'option value 2']
		 * or: ['option_key_1'=>'option value 1', 'option_key_2'=>'option value 2'].
		 * Option keys and values are case sensitive. See also <em>$option_keys</em> parameter.
		 * @documentable
		 * @param array $options Specifies product option values in the
		 * @param mixed $product Product object (Shop_Product) or product identifier.
		 * @param boolean $option_keys Indicates whether array keys in the $options parameter represent option keys (md5(name)) rather than option names. 
		 * Otherwise $options keys are considered to be plain option name.
		 * @return Shop_OptionMatrixRecord returns the Option Matrix record object or NULL.
		 */
		public static function find_record($options, $product, $option_keys = false)
		{
			$obj = self::create();
			$product_id = is_object($product) ? $product->id : $product;
			$obj->where('product_id=?', $product_id);
			$obj->order('id');
			
			if (!$option_keys)
			{
				$options_processed = array();
				foreach ($options as $key=>$value)
					$options_processed[md5($key)] = $value;
					
				$options = $options_processed;
			}

			foreach ($options as $option_key=>$option_value)
			{
				$obj->where(sprintf(
					'exists(
						select 
							shop_option_matrix_options.id 
						from 
							shop_option_matrix_options, 
							shop_custom_attributes 
						where 
							shop_custom_attributes.id=shop_option_matrix_options.option_id 
							and shop_custom_attributes.product_id=shop_option_matrix_records.product_id
							and shop_custom_attributes.option_key=\'%s\' 
							and shop_option_matrix_options.option_value=\'%s\' 
							and shop_option_matrix_options.matrix_record_id=shop_option_matrix_records.id
					)', 
					mysql_real_escape_string($option_key), 
					mysql_real_escape_string($option_value)));
			}

			$result = $obj->find();
			if (!$result)
				return null;
				
			return $result;
		}

		/**
		 * Returns product price, taking into account tier pricing. Returns product price with tax included,
		 * if the {@link http://lemonstandapp.com/docs/configuring_lemonstand_for_tax_inclusive_environments/ Display catalog/cart prices including tax} 
		 * option is enabled unless the <em>$no_tax</em> parameter value is FALSE.
		 * @documentable
		 * @param mixed $product Product object ({@link Shop_Product}) or product identifier.
		 * @param integer $quantity Quantity for the tier price calculations.
		 * @param integer $customer_group_id {@link Shop_CustomerGroup Customer group} identifier.
		 * @param boolean $no_tax Forces the function to not include tax into the result even if the {@link http://lemonstandapp.com/docs/configuring_lemonstand_for_tax_inclusive_environments/ Display catalog/cart prices including tax} option is enabled.
		 * @return float Returns product price.
		 */
		public function get_price($product, $quantity = 1, $customer_group_id = null, $no_tax = false)
		{
			if ($customer_group_id === null)
				$customer_group_id = Cms_Controller::get_customer_group_id();
				
			$price = $this->eval_tier_price($product, $customer_group_id, $quantity);
			if ($no_tax)
				return $price;

			$include_tax = Shop_CheckoutData::display_prices_incl_tax();
			if (!$include_tax)
				return $price;

			return Shop_TaxClass::get_total_tax($product->tax_class_id, $price) + $price;
		}
		
		/**
		 * Returns product sale price. Returns price with tax included,
		 * if the {@link http://lemonstandapp.com/docs/configuring_lemonstand_for_tax_inclusive_environments/ Display catalog/cart prices including tax} 
		 * option is enabled unless the <em>$no_tax</em> parameter value is FALSE.
		 * @documentable
		 * @param mixed $product Product object ({@link Shop_Product}) or product identifier.
		 * @param integer $quantity Quantity for the tier price calculations.
		 * @param integer $customer_group_id {@link Shop_CustomerGroup Customer group} identifier.
		 * @param boolean $no_tax Forces the function to not include tax into the result even if the {@link http://lemonstandapp.com/docs/configuring_lemonstand_for_tax_inclusive_environments/ Display catalog/cart prices including tax} option is enabled.
		 * @return float Returns product sale price.
		 */
		public function get_sale_price($product, $quantity = 1, $customer_group_id = null, $no_tax = false)
		{
			if ($customer_group_id === null )
				$customer_group_id = Cms_Controller::get_customer_group_id();

			if($this->on_sale && strlen($this->sale_price_or_discount))
			{
				$price = $this->get_price($product, $quantity, $customer_group_id, true);
				$price = round(Shop_Product::get_set_sale_price($price, $this->sale_price_or_discount), 2);
				
				return $no_tax ? $price : Shop_TaxClass::apply_tax_conditional($product->tax_class_id, $price);
			}

			/*
			 * If this record has no applied price rules, fallback to the standard record or product price.
			 * (It is possible that we should fallback to the product's sale price instead)
			 */
			if (!strlen($this->price_rules_compiled))
				return $this->get_price($product, $quantity, $customer_group_id, $no_tax);

			$price_rules = array();
			try
			{
				$price_rules = unserialize($this->price_rules_compiled);
			} catch (Exception $ex)
			{
				$product = is_object($product) ? $product : Shop_Product::find_by_id($product);
				throw new Phpr_ApplicationException('Error loading price rules for the "'.$product->name.'" product');
			}
			
			if (!array_key_exists($customer_group_id, $price_rules))
				return $this->get_price($product, $quantity, $customer_group_id, $no_tax);

			$price_tiers = $price_rules[$customer_group_id];
			$price_tiers = array_reverse($price_tiers, true);

			foreach ($price_tiers as $tier_quantity=>$price)
			{
				if ($tier_quantity <= $quantity)
				{
					$price = round($price, 2);
					return $no_tax ? $price : Shop_TaxClass::apply_tax_conditional($product->tax_class_id, $price);
				}
			}

			return $this->get_price($product, $quantity, $customer_group_id, $no_tax);
		}
		
		/**
		 * Returns the difference between the regular price and sale price of the product.
		 * @documentable
		 * @param mixed $product Product object ({@link Shop_Product}) or product identifier.
		 * @param integer $quantity Quantity for the tier price calculations.
		 * @return float Returns the sale reduction value.
		 */
		public function get_sale_reduction($product, $quantity = 1, $customer_group_id = null)
		{
			$sale_price = $this->get_sale_price($product, $quantity, $customer_group_id, true);
			$original_price = $this->get_price($product, $quantity, $customer_group_id, true);

			return $original_price - $sale_price;
		}
		
		/**
		 * Returns TRUE if there are active catalog-level price rules affecting the product price or if the product is on sale ('On Sale' checkbox).
		 * @documentable
		 * @param mixed $product Product object (Shop_Product) or product identifier.
		 * @return boolean Returns TRUE if the product is on sale.
		 */
		public function is_on_sale($product)
		{
			return $this->get_price($product) <> $this->get_sale_price($product);
		}

		/**
		 * Returns TRUE if inventory tracking for the product is enabled and the product is out of stock.
		 * @documentable
		 * @param mixed $product Product object ({@link Shop_Product}) or product identifier.
		 * @return boolean Returns TRUE if the product is out of stock.
		 */
		public function is_out_of_stock($product)
		{
			$product = is_object($product) ? $product : Shop_Product::find_by_id($product);
			if (!$product->track_inventory)
				return false;

			$in_stock = SHop_OptionMatrix::get_property($this, 'in_stock', $product);

			if ($product->stock_alert_threshold !== null)
				return $in_stock <= $product->stock_alert_threshold;

			if ($in_stock <= 0)
			 	return true;

			return false;
		}

		/**
		 * Returns the product volume.
		 * @documentable
		 * @param mixed $product Product object ({@link Shop_Product}) or product identifier.
		 * @return float Returns the volume.
		 */
		public function get_volume($product)
		{
			$product = is_object($product) ? $product : Shop_Product::find_by_id($product);
			
			$width = Shop_OptionMatrix::get_property($this, 'width', $product);
			$height = Shop_OptionMatrix::get_property($this, 'height', $product);
			$depth = Shop_OptionMatrix::get_property($this, 'depth', $product);
			
			return $width*$height*$depth;
		}
		
		/**
		 * Returns options associated with the record as string.
		 * The returned string has the following format: <em>Color: green, Size: large</em>.
		 * @documentable
		 * @param string Returns options as string.
		 */
		public function options_as_string()
		{
			return Db_DbHelper::scalar("
				select 
					group_concat(
						concat(shop_custom_attributes.name, ': ', shop_option_matrix_options.option_value)
					separator ', ')
				from 
					shop_custom_attributes, 
					shop_option_matrix_options, 
					shop_option_matrix_records
				where 
					shop_option_matrix_records.id=:id
					and shop_option_matrix_options.matrix_record_id=shop_option_matrix_records.id
					and shop_custom_attributes.id=shop_option_matrix_options.option_id
				order by
					shop_custom_attributes.sort_order", array('id'=>$this->id));
		}
		
		/**
		 * Returns options associated with the record as array of option keys and values.
		 * @documentable
		 * @param boolean $option_keys Specifies whether options should be presented with option keys instead of names.
		 * @return array Returns an array of option keys and values.
		 */
		public function get_options($option_keys = true)
		{
			$options = Db_DbHelper::objectArray("
				select 
					shop_custom_attributes.option_key as option_key,
					shop_custom_attributes.name as option_name,
					shop_option_matrix_options.option_value as option_value
				from 
					shop_custom_attributes, 
					shop_option_matrix_options, 
					shop_option_matrix_records
				where 
					shop_option_matrix_records.id=:id
					and shop_option_matrix_options.matrix_record_id=shop_option_matrix_records.id
					and shop_custom_attributes.id=shop_option_matrix_options.option_id
				order by
					shop_custom_attributes.sort_order", array('id'=>$this->id));
					
			$result = array();
			foreach ($options as $option)
			{
				$key = $option_keys ? $option->option_key : $option->option_name;
				$result[$key] = $option->option_value;
			}
				
			return $result;
		}

		/*
		 * Db_MemoryCacheable implementation
		 */
		
		/*
		 * Returns a record by its identifier. If the record exists in the cache,
		 * returns the cached value. If it doesn't exist, finds the record, 
		 * adds it to the cache and returns the record.
		 * @param int $record_id Specifies the record identifier. Can be NULL 
		 * if a new record is requested.
		 */
		public function get_record_cached($record_id)
		{
			if (!strlen($record_id))
				$record_id = -1;

			if (array_key_exists($record_id, self::$record_cache))
				return self::$record_cache[$record_id];
				
			if ($record_id > -1)
				return self::$record_cache[$record_id] = self::create()->find($record_id);
				
			return self::$record_cache[$record_id] = self::create();
		}
		
		public function before_delete($id=null)
		{
			$bind = array(
				'id'=>$this->id
			);

			$count = Db_DbHelper::scalar('select count(*) from shop_order_items, shop_orders where option_matrix_record_id is not null and option_matrix_record_id=:id and shop_orders.id = shop_order_items.shop_order_id', $bind);
			if ($count)
				throw new Phpr_ApplicationException('Cannot delete product because there are orders referring to it.');
		}
		
		/**
		 * Allows to add new columns to the Option Matrix table. 
		 * Before you add new columns you should add corresponding columns to <em>shop_option_matrix_records</em> table. 
		 * The event handler should return an array of new column definitions. Array keys should correspond the 
		 * table column names. Array values are associative arrays containing the column configuration. Example: 
		 * <pre>
		 * public function subscribeEvents() 
		 * {
		 *   Backend::$events->add_event('shop:onExtendOptionMatrix', $this, 'extend_option_matrix');
		 * }
		 * 
		 * public function extend_option_matrix() 
		 * {
		 *   $result = array(
		 *     'x_custom_int_column'=>array(
		 *         'title'=>'Custom integer', 
		 *         'type'=>'text', 
		 *         'align'=>'right', 
		 *         'width'=>50, 
		 *         'column_group'=>'Custom'
		 *     ),
		 *     'x_custom_date_column'=>array(
		 *         'title'=>'Custom date', 
		 *         'align'=>'right', 
		 *         'type'=>'text', 
		 *         'width'=>95, 
		 *         'editor_class'=>'Db_GridDateEditor', 
		 *         'column_group'=>'Custom'
		 *     ),
		 *     'x_custom_drop_down'=>array(
		 *         'title'=>'Custom date', 
		 *         'type'=>'dropdown', 
		 *         'width'=>100, 
		 *         'column_group'=>'Custom', 
		 *         'option_keys'=>array(1, 2), 
		 *         'option_values'=>array('Value 1', 'Value 2')
		 *     )
		 *   );
		 *   
		 *   return $result;
		 * }
		 * </pre>
		 * Column definitions support the following parameters:
		 * <ul>
		 *   <li><em>title</em> - defines the column title, required.</li>
		 *   <li><em>type</em> - defines the column type, required. Supported values are <em>text</em>, <em>dropdown</em>, <em>checkbox</em>.</li>
		 *   <li><em>align</em> - defines the column value alignment, required. Supported values are "left", "right".</li>
		 *   <li><em>width</em> - defines the column width, required.</li>
		 *   <li><em>column_group</em> - defines the column group name, required.</li>
		 *   <li><em>editor_class</em> - class name for a popup editor. Required value for date columns is <em>Db_GridDateEditor</em>. It is the only supported popup editor for API fields.</li>
		 *   <li><em>option_keys</em> - defines option keys for drop-down menus. Required if the column type is <em>dropdown</em>.</li>
		 *   <li><em>option_values</em> - defines option values for drop-down menus. Required if the column type is <em>dropdown</em>.</li>
		 * </ul>
		 * API columns are supported by CSV operations and can be accessed with {@link Shop_Product::om()}, {@link Shop_OrderItem::om()} and {@link Shop_CartItem::om()} methods.
		 * @event shop:onExtendOptionMatrix
		 * @package shop.events
		 * @author LemonStand eCommerce Inc.
		 * @see http://lemonstandapp.com/docs/extending_existing_models Extending existing models
		 * @see http://lemonstandapp.com/docs/creating_and_updating_database_tables Creating and updating database tables
		 * @see Shop_OptionMatrixManager
		 */
		private function event_onExtendOptionMatrix() {}
	}

?>