<?php

	$installer_zip_file_permissions = 0777;
	$installer_zip_folder_permissions = 0777;

	class ZipHelper
	{
		protected static $_initialized = false;
		
		public static function unzip($path, $archivePath, $file_permissions, $folder_permissions)
		{
			global $installer_zip_file_permissions;
			global $installer_zip_folder_permissions;
			
			$installer_zip_file_permissions = $file_permissions;
			$installer_zip_folder_permissions = $folder_permissions;
			
			if (!file_exists($archivePath))
				throw new Exception('Archive file is not found: '.$archivePath);

			if (!is_writable($path))
				throw new Exception('Error unpacking ZIP archive. Directory is not writable: '.$path);

			self::initZip();
			$archive = new PclZip($archivePath);
			if (@$archive->extract(PCLZIP_OPT_PATH, $path, PCLZIP_CB_POST_EXTRACT, 'installer_zipPostExtractCallBack') === 0)
				throw new Exception('Error unpacking archive: '.$archive->errorInfo(true));
		}

		public static function initZip()
		{
			if (self::$_initialized)
				return;
			
			require_once(PATH_INSTALL."/installer_files/libs/pclzip.lib.php");

			if ( !defined('PCLZIP_TEMPORARY_DIR') )
			{
				if (!is_writable(PATH_INSTALL))
					throw new Exception('Error initializing ZIP helper. Directory is not writable: '.PATH_INSTALL);
				
				define('PCLZIP_TEMPORARY_DIR', PATH_INSTALL);
			}
				
			self::$_initialized = true;
		}
	}
	
	function installer_zipPostExtractCallBack($p_event, &$p_header)
	{
		global $installer_zip_file_permissions;
		global $installer_zip_folder_permissions;

		if ($installer_zip_file_permissions !== null && file_exists($p_header['filename']))
		{
			$is_folder = array_key_exists('folder', $p_header) ? $p_header['folder'] : false;
			$mode = $is_folder ? $installer_zip_folder_permissions : $installer_zip_file_permissions;
			@chmod($p_header['filename'], $mode);
		}
		return 1;
	}
?>