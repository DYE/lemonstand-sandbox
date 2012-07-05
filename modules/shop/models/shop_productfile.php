<?

	/**
	 * Represents a file in a downloadable product. 
	 * Objects of this class are available through the {@link Shop_Product::$files} property.
	 * See the {@link http://lemonstandapp.com/docs/order_details_page Creating the Order Details page} article for examples of the class usage.
	 * @property string $size_str Specifies the file size as string.
	 * @documentable
	 * @see http://lemonstandapp.com/docs/order_details_page Creating the Order Details page
	 * @package shop.models
	 * @author LemonStand eCommerce Inc.
	 */
	class Shop_ProductFile extends Db_File
	{
		public static function create($values = null)
		{
			return new self();
		}
		
		public function __get($name)
		{
			if ($name == 'size_str')
				return Phpr_Files::fileSize($this->size);
				
			return parent::__get($name);
		}

		/**
		 * Returns an URL for downloading the file. 
		 * Use this function to create links to product files. 
		 * Please refer the {@link http://lemonstandapp.com/docs/order_details_page Creating the Order Details page}
		 * article for details of the method usage. Customers can only download files from products which belong
		 * to paid orders.
		 * @documentable
		 * @param Shop_Order $order Specifies the order object.
		 * @param string $mode Specifies the file download mode (disposition). Supported values are: <em>attachment</em>, <em>inline</em>.
		 * @return string Returns the URL string.
		 */
		public function download_url($order, $mode = null)
		{
			if (!$mode || ($mode != 'inline' && $mode != 'attachment'))
				return root_url('download_product_file/'.$this->id.'/'.$order->order_hash.'/'.$this->name);
			else
				return root_url('download_product_file/'.$this->id.'/'.$order->order_hash.'/'.$mode.'/'.$this->name);
		}
	}

?>