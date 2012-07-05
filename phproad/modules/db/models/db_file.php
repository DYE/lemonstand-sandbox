<?php

	/**
	 * Represents a file or image attached to a {@link Db_ActiveRecord model}.
	 * This class used for creating model relations which contain model attachments.
	 * <span class="note">Please note that downloadable product files are presented with another class - {@link Shop_ProductFile}.</span>
	 * The example below demonstrates a typical relation which can be presented as a single file, single image, multiple file or multiple image
	 * widget on the form, depending in the value passed to the field's {@link Db_FormFieldDefinition::renderFilesAs()} method:
	 * <pre>
	 * public $has_many = array(
 	 *  'images'=>array('class_name'=>'Db_File', 'foreign_key'=>'master_object_id', 
	 *    'conditions'=>"master_object_class='Shop_Product' and field='images'", 
	 *    'order'=>'sort_order, id', 'delete'=>true)
	 * );
	 * </pre>
	 * Note that the <em>conditions</em> parameter should define a SQL filter which refers to the model class name ({@link Shop_Product} in the example)
	 * and the relation name (<em>image</em> in the example). File relations should always be defined as <em>has_many</em>. Defining the relation
	 * is enough if you are not going to display files in the model form. If administrators should be able to upload files, you should 
	 * define the model column and form field as described below.
	 * 
	 * After adding the relations a corresponding column should be created with {@link Db_ActiveRecord::define_multi_relation_column()} method:
	 * <pre>
	 * public function define_columns($context = null)
	 * {
	 *   $this->define_multi_relation_column('images', 'images', 'Images', '@name')->invisible();
	 * }
	 * </pre>
	 * And finally, the field should be added to the form with {@link Db_ActiveRecord::define_form_fields()} method. The type of the form widget
	 * is defined with {@link Db_FormFieldDefinition::renderFilesAs()} method.
	 * <pre>
	 * public function define_form_fields($context = null)
	 * {
	 *   $this->add_form_field('images')->renderAs(frm_file_attachments)->renderFilesAs('image_list')
	 *     ->addDocumentLabel('Add image(s)')->tab('Images')->noAttachmentsLabel('There are no images uploaded')
	 *     ->fileDownloadBaseUrl(url('ls_backend/files/get/'));
	 * }
	 * </pre>
	 * @documentable
	 * @see Db_FormFieldDefinition::renderFilesAs()
	 * @see Db_FormFieldDefinition::renderAs()
	 * @see Db_FormFieldDefinition::addDocumentLabel()
	 * @see Db_FormFieldDefinition::noAttachmentsLabel()
	 * @see Db_FormFieldDefinition::fileDownloadBaseUrl()
	 * @see Db_ActiveRecord::define_multi_relation_column()
	 * @author LemonStand eCommerce Inc.
	 * @package core.classes
	 */
	class Db_File extends Db_ActiveRecord 
	{
		public $table_name = 'db_files';
		public $simpleCaching = true;

		/**
		 * @var boolean Determines whether the file on the server can be accessed from the Web.
		 * Public files are stored to uploaded/public directory, which is open for the external access.
		 * The {@link Db_FormBehavior form behavior} considers image files ({@link Db_FormFieldDefinition::renderFilesAs() rendered as} single image or image lists)
		 * as public, and all other files as non public.
		 * @documentable
		 */
		public $is_public;

		/**
		 * @var string Specifies the file name.
		 * @documentable
		 */
		public $name;

		/**
		 * @var integer Specifies the file size, in bytes.
		 * @documentable
		 */
		public $size;
		
		/**
		 * @var string Specifies the file description.
		 * @documentable
		 */
		public $description;
		
		/**
		 * @var string Specifies the file tittle.
		 * @documentable
		 */
		public $title;
		
		/**
		 * @var integer Specifies the file record identifier.
		 * @documentable
		 */
		public $id=0;

		public $implement = 'Db_AutoFootprints';
		
		protected $autoMimeTypes = 
			array('docx'=>'application/msword', 'xlsx'=>'application/excel', 'gif'=>'image/gif', 'png'=>'image/png', 'jpg'=>'image/jpeg', 'jpeg'=>'image/jpeg',
					'jpe'=>'image/jpeg', 'pdf'=>'application/pdf'
			);
			
		public static $image_extensions = array(
			'jpg', 'jpeg', 'png', 'gif'
		);
		
		public $calculated_columns = array( 
			'user_name'=>array('sql'=>'concat(lastName, " ", firstName)', 
				'type'=>db_text, 'join'=>array('users'=>'users.id=db_files.created_user_id'))
		);
		
		public function __construct($values = null, $options = array())
		{
			$front_end = Db_ActiveRecord::$execution_context == 'front-end';
			if ($front_end)
				unset($this->calculated_columns['user_name']);

			parent::__construct($values, $options);
		}

		/**
		 * Creates a new class instance.
		 * @documentable
		 * @param array $values Optional list of column values.
		 * @return Db_File Returns the file object.
		 */
		public static function create($values = null) 
		{
			return new self($values);
		}

		public function fromPost($fileInfo)
		{
			Phpr_Files::validateUploadedFile($fileInfo);
			
			$this->mime_type = $this->evalMimeType($fileInfo);
			$this->size = $fileInfo['size'];
			$this->name = $fileInfo['name'];
			$this->disk_name = $this->getDiskFileName($fileInfo);

			$destPath = $this->getFileSavePath($this->disk_name);

			if ( !@move_uploaded_file($fileInfo["tmp_name"], $destPath) )
				throw new Phpr_SystemException( "Error copying file to $destPath." );

			return $this;
		}
		
		/**
		 * Creates a file object from a disk file.
		 * Use this method for adding files from disk to a list of model's files.
		 * <pre>
		 * $file = Db_File::create()->fromFile(PATH_APP.'/temp/picture.png');
		 * $file->is_public = true;
		 * $file->master_object_class = 'Shop_Product';
		 * $file->field = 'images';
		 * $file->save();
		 * 
		 * $product->images->add($file);
		 * $product->save();
		 * </pre>
		 * @documentable
		 * @param string $file_path Specifies a path to the file.
		 * @return Db_File Returns the initialized file.
		 */
		public function fromFile($file_path)
		{
			$fileInfo = array();
			$fileInfo['name'] = basename($file_path);
			$fileInfo['size'] = filesize($file_path);
			$fileInfo['type'] = null;
			
			$this->mime_type = $this->evalMimeType($fileInfo);
			$this->size = $fileInfo['size'];
			$this->name = $fileInfo['name'];
			$this->disk_name = $this->getDiskFileName($fileInfo);

			$destPath = $this->getFileSavePath($this->disk_name);

			if ( !@copy($file_path, $destPath) )
				throw new Phpr_SystemException( "Error copying file to $destPath." );

			return $this;
		}
		
		protected function getDiskFileName($fileInfo)
		{
			$ext = $this->getFileExtension($fileInfo);
			$name = uniqid(null, true);
			
			return $ext !== null ? $name.'.'.$ext : $name;
		}
		
		protected function evalMimeType($fileInfo)
		{
			$type = $fileInfo['type'];
			$ext = $this->getFileExtension($fileInfo);
			
			$mime_types = array_merge($this->autoMimeTypes, Phpr::$config->get('auto_mime_types', array()));

			if (array_key_exists($ext, $mime_types))
				return $mime_types[$ext];
			
			return $type;
		}
		
		protected function getFileExtension($fileInfo)
		{
			$pathInfo = pathinfo($fileInfo['name']);
			if (isset($pathInfo['extension']))
				return strtolower($pathInfo['extension']);

			return null;
		}

		public function getFileSavePath($diskName)
		{
			if (!$this->is_public)
				return PATH_APP.'/uploaded/'.$diskName;
			else
				return PATH_APP.'/uploaded/public/'.$diskName;
		}
		
		public function after_create() 
		{
			Db_DbHelper::query('update db_files set sort_order=:sort_order where id=:id', array(
				'sort_order'=>$this->id,
				'id'=>$this->id
			));
			$this->sort_order = $this->id;
		}

		public function after_delete()
		{
			$destPath = $this->getFileSavePath($this->disk_name);
			
			if (file_exists($destPath))
				@unlink($destPath);

			$thumbPath = PATH_APP.'/uploaded/thumbnails/db_file_img_'.$this->id.'_*.jpg';
			$thumbs = glob($thumbPath);
			if (is_array($thumbs))
			{
				foreach ($thumbs as $filename) 
				    @unlink($filename);
			}
		}

		/**
		 * Outputs the file to the browser.
		 * Depending on the file type and <em>$disposition</em> parameter value a browser either displays the file contents or offers to save it to the disk.
		 * @param string $disposition Specifies the content disposition HTTP header value: <em>inline</em> or <em>attachment</em>.
		 * @documentable
		 */ 
		public function output($disposition = 'inline')
		{
			$path = $this->getFileSavePath($this->disk_name);
			if (!file_exists($path))
				throw new Phpr_ApplicationException('Error: file not found.');
			
			$encoding = Phpr::$config["FILESYSTEM_CODEPAGE"];
			$fileName = mb_convert_encoding( $this->name, $encoding );
			
			$mime_type = $this->mime_type;
			if (!strlen($mime_type) || $mime_type == 'application/octet-stream')
			{
				$fileInfo = array('type'=>$mime_type, 'name'=>$fileName);
				$mime_type = $this->evalMimeType($fileInfo);
			}

			header("Content-type: ".$mime_type);
			header('Content-Disposition: '.$disposition.'; filename="'.$fileName.'"');
			header('Cache-Control: private');
			header('Cache-Control: no-store, no-cache, must-revalidate');
			header('Cache-Control: pre-check=0, post-check=0, max-age=0');
			header('Accept-Ranges: bytes');
			header('Content-Length: '.$this->size);
//			header("Connection: close");

			Phpr_Files::readFile( $path );
		}
		
		/**
		 * Creates an image thumbnail.
		 * This method is applicable only for image files.
		 * The <em>$width</em> and <em>$height</em> parameters could be either integer numbers or the 'auto' word. If you specify an 
		 * integer number for the width or height, the thumbnail will have the exact width or height value, in pixels. 
		*  Use the 'auto' word to scale an image dimension proportionally. For example, to generate a thumbnail with 
		 * fixed width of 100 pixels and proportional height, use the following code:
		 * <pre>$url = $file->getThumbnailPath(100, 'auto');</pre>
		 * The <em>$as_jpeg</em> parameter allows you to generate PNG images with transparency support. By default the parameter value is 
		 * TRUE and the method generates a JPEG image. The <em>$params</em> array allows to pass parameters to image processing modules 
		 * (which handle the {@link core:onProcessImage} event).
		 *
		 * Behavior of this method can be altered by {@link core:onProcessImage} event handlers.
		 * @documentable
		 * @param mixed $width Specifies the thumbnail width. Use the 'auto' word to scale image width proportionally. 
		 * @param mixed $height Specifies the thumbnail height. Use the 'auto' word to scale height width proportionally. 
		 * @param boolean $as_jpeg Determines whether JPEG or PNG image will be created. 
		 * @param array $params A list of parameters. 
		 * @return string Returns the image URL relative to the website root.
		 */
		public function getThumbnailPath($width, $height, $returnJpeg = true, $params = array('mode' => 'keep_ratio'))
		{
			$processed_images = Backend::$events->fireEvent('core:onProcessImage', $this, $width, $height, $returnJpeg, $params);
			foreach ($processed_images as $image)
			{
				if (strlen($image))
				{
					if (!preg_match(',^(http://)|(https://),', $image))    
						return root_url($image);
					else 
						return $image;
				}
			}

			$ext = $returnJpeg ? 'jpg' : 'png';

			$thumbPath = '/uploaded/thumbnails/db_file_img_'.$this->id.'_'.$width.'x'.$height.'.'.$ext;
			$thumbFile = PATH_APP.$thumbPath;

			if (file_exists($thumbFile))
				return root_url($thumbPath);

			try
			{
				Phpr_Image::makeThumbnail($this->getFileSavePath($this->disk_name), $thumbFile, $width, $height, false, $params['mode'], $returnJpeg);
			}
			catch (Exception $ex)
			{
				@copy(PATH_APP.'/phproad/resources/images/thumbnail_error.gif', $thumbFile);
			}

			return root_url($thumbPath);
		}
		
		/**
		 * Returns the file path relative to LemonStand root directory.
		 * Use <em>PATH_APP</em> constant to obtain the absolute path:
		 * <pre>$absolute_path = PATH_APP.$file->getPath();</pre>
		 * @documentable
		 * @return string Returns the file path.
		 */ 
		public function getPath()
		{
			if (!$this->is_public)
				return '/uploaded/'.$this->disk_name;
			else
				return '/uploaded/public/'.$this->disk_name;
		}

		/**
		 * Copies the file and returns the new file object.
		 * The returned object is not saved to the database, so its properties can be
		 * updated before it is saved.
		 * @documentable
		 * @return Db_File Returns the new file object.
		 */
		public function copy()
		{
			$srcPath = $this->getFileSavePath($this->disk_name);
			$destName = $this->getDiskFileName(array('name'=>$this->disk_name));

			$obj = new Db_File();
			$obj->mime_type = $this->mime_type;
			$obj->size = $this->size;
			$obj->name = $this->name;
			$obj->disk_name = $destName;
			$obj->description = $this->description;
			$obj->sort_order = $this->sort_order;
			$obj->is_public = $this->is_public;
			
			if (!copy($srcPath, $obj->getFileSavePath($destName)))
				throw new Phpr_SystemException( "Error copying file" );

			return $obj;
		}
		
		public static function set_orders($item_ids, $item_orders)
		{
			if (is_string($item_ids))
				$item_ids = explode(',', $item_ids);
				
			if (is_string($item_orders))
				$item_orders = explode(',', $item_orders);

			foreach ($item_ids as $index=>$id)
			{
				$order = $item_orders[$index];
				Db_DbHelper::query('update db_files set sort_order=:sort_order where id=:id', array(
					'sort_order'=>$order,
					'id'=>$id
				));
			}
		}
		
		/**
		 * Returns TRUE if the file is an image.
		 * The method detect images basing on the file extension. The following extensions are considered as images:
		 * <em>jpeg</em>, <em>jpg</em>, <em>gif</em>, <em>png</em>.
		 * @documentable
		 * @return boolean Returns TRUE if the file is an image.
		 */
		public function is_image()
		{
			$pathInfo = pathinfo($this->name);
			$extension = null;
			if (isset($pathInfo['extension']))
				$extension = strtolower($pathInfo['extension']);
				
			return in_array($extension, self::$image_extensions);
		}

		public function before_create($deferred_session_key = null) 
		{
    		Backend::$events->fireEvent('core:onFileBeforeCreate', $this);
		}
		
		/*
		 * Event descriptions
		 */
		
		/**
		 * Allows to process product and other images with third-party image manipulation tools. 
		 * This event is triggered every time when you call the {@link Db_File::getThumbnailPath()} method,
		 * and hence - every time when you call the {@link Shop_Product::image_url()} and {@link Shop_Category::image_url()} methods. 
		 * Thus the event allows to use the usual programming interface with third-party image processing modules.
		 * 
		 * The event handler should return a path to the generated image. The path should be relative to the LemonStand root directory. 
		 * In the event handler you should check whether the thumbnail is not generated yet. Example of the event handler: 
		 * <pre>
		 * public function subscribeEvents()
		 * {
		 *   Backend::$events->addEvent('core:onProcessImage', $this, 'process_image');
		 * }
		 * 
		 * public function process_image($file_obj, $width, $height, $returnJpeg, $params)
		 * {
		 *   // This handler just copies the original image to the uploaded/thumbnails directory
		 *   //
		 *   
		 *   // Generate the thumbnail file name and check whether it does not exist yet  
		 *   $ext = $returnJpeg ? 'jpg' : 'png';
		 *   $thumbnail_path = '/uploaded/thumbnails/db_file_img_'.$file_obj->id.'_'.$width.'x'.$height.'.'.$ext;
		 * 
		 *   // Return the thumbnail path if it does exist
		 *   if (file_exists($thumbnail_path))
		 *     return $thumbnail_path;
		 *       
		 *   // Process image with a third-party image library and save it to the
		 *   // uploaded/thumbnails directory. Please note - to get an absolute path
		 *   // to a file, we prepend the PATH_APP constant.
		 *   
		 *   copy(PATH_APP.$file_obj->getPath(), PATH_APP.$thumbnail_path);
		 * 
		 *   // Return the relative path to the thumbnail
		 *   return $thumbnail_path;
		 * }
		 * </pre>
		 * @event core:onProcessImage
		 * @package core.events
		 * @author LemonStand eCommerce Inc.
		 * @param Db_File $file Specifies the original file object.
		 * @param mixed $width The image width requested in the {@link Db_File::getThumbnailPath()} method call.
		 * @param mixed $height The image height requested in the {@link Db_File::getThumbnailPath()} method call.
		 * @param boolean $as_jpeg Determines whether JPEG or PNG image will be created. 
		 * @param array $params Specifies the <em>$params</em> parameter value specified in the {@link Db_File::getThumbnailPath()} method call. 
		 * You can use this parameter for passing image library specific parameters from the {@link Db_File::getThumbnailPath()} call to the event handler.
		 * @return string Returns path to the generated image.
		 */
		private function event_onProcessImage($file, $width, $height, $as_jpeg, $params) {}

		/**
		 * Triggered before a new file record saved to the database.
		 * You can use this event to validate new files. Throw an exception in the handler
		 * to cancel the file creation.
		 * @event core:onFileBeforeCreate
		 * @package core.events
		 * @author LemonStand eCommerce Inc.
		 * @param Db_File $file Specifies the new file object.
		 */
		private function event_onFileBeforeCreate($file) {}
	}

?>
