<?php
/**
 * CFileHelper provides a set of helper methods for common file system operations.
 */
class CFileHelper
{
	
	public static function getExtension($path)
	{
		return pathinfo($path,PATHINFO_EXTENSION);
	}

	
	public static function copyDirectory($src,$dst,$options=array())
	{
		$fileTypes=array();
		$exclude=array();
		$level=-1;
		extract($options);
		if(!is_dir($dst))
			self::createDirectory($dst,isset($options['newDirMode'])?$options['newDirMode']:null,true);

		self::copyDirectoryRecursive($src,$dst,'',$fileTypes,$exclude,$level,$options);
	}

	
	public static function removeDirectory($directory,$options=array())
	{
		if(!isset($options['traverseSymlinks']))
			$options['traverseSymlinks']=false;
		$items=glob($directory.DIRECTORY_SEPARATOR.'{,.}*',GLOB_MARK | GLOB_BRACE);
		foreach($items as $item)
		{
			if(basename($item)=='.' || basename($item)=='..')
				continue;
			if(substr($item,-1)==DIRECTORY_SEPARATOR)
			{
				if(!$options['traverseSymlinks'] && is_link(rtrim($item,DIRECTORY_SEPARATOR)))
					unlink(rtrim($item,DIRECTORY_SEPARATOR));
				else
					self::removeDirectory($item,$options);
			}
			else
				unlink($item);
		}
		if(is_dir($directory=rtrim($directory,'\\/')))
		{
			if(is_link($directory))
				unlink($directory);
			else
				rmdir($directory);
		}
	}

	public static function findFiles($dir,$options=array())
	{
		$fileTypes=array();
		$exclude=array();
		$level=-1;
		$absolutePaths=true;
		extract($options);
		$list=self::findFilesRecursive($dir,'',$fileTypes,$exclude,$level,$absolutePaths);
		sort($list);
		return $list;
	}


	protected static function copyDirectoryRecursive($src,$dst,$base,$fileTypes,$exclude,$level,$options)
	{
		if(!is_dir($dst))
			self::createDirectory($dst,isset($options['newDirMode'])?$options['newDirMode']:null,false);

		$folder=opendir($src);
		if($folder===false)
			throw new Exception('Unable to open directory: ' . $src);
		while(($file=readdir($folder))!==false)
		{
			if($file==='.' || $file==='..')
				continue;
			$path=$src.DIRECTORY_SEPARATOR.$file;
			$isFile=is_file($path);
			if(self::validatePath($base,$file,$isFile,$fileTypes,$exclude))
			{
				if($isFile)
				{
					copy($path,$dst.DIRECTORY_SEPARATOR.$file);
					if(isset($options['newFileMode']))
						@chmod($dst.DIRECTORY_SEPARATOR.$file,$options['newFileMode']);
				}
				elseif($level)
					self::copyDirectoryRecursive($path,$dst.DIRECTORY_SEPARATOR.$file,$base.'/'.$file,$fileTypes,$exclude,$level-1,$options);
			}
		}
		closedir($folder);
	}


	protected static function findFilesRecursive($dir,$base,$fileTypes,$exclude,$level,$absolutePaths)
	{
		$list=array();
		$handle=opendir($dir.$base);
		if($handle===false)
			throw new Exception('Unable to open directory: ' . $dir);
		while(($file=readdir($handle))!==false)
		{
			if($file==='.' || $file==='..')
				continue;
			$path=substr($base.DIRECTORY_SEPARATOR.$file,1);
			$fullPath=$dir.DIRECTORY_SEPARATOR.$path;
			$isFile=is_file($fullPath);
			if(self::validatePath($base,$file,$isFile,$fileTypes,$exclude))
			{
				if($isFile)
					$list[]=$absolutePaths?$fullPath:$path;
				elseif($level)
					$list=array_merge($list,self::findFilesRecursive($dir,$base.'/'.$file,$fileTypes,$exclude,$level-1,$absolutePaths));
			}
		}
		closedir($handle);
		return $list;
	}

	
	protected static function validatePath($base,$file,$isFile,$fileTypes,$exclude)
	{
		foreach($exclude as $e)
		{
			if($file===$e || strpos($base.'/'.$file,$e)===0)
				return false;
		}
		if(!$isFile || empty($fileTypes))
			return true;
		if(($type=self::getExtension($file))!=='')
			return in_array($type,$fileTypes);
		else
			return false;
	}


	public static function getMimeType($file,$magicFile=null,$checkExtension=true)
	{
		if(function_exists('finfo_open'))
		{
			$options=defined('FILEINFO_MIME_TYPE') ? FILEINFO_MIME_TYPE : FILEINFO_MIME;
			$info=$magicFile===null ? finfo_open($options) : finfo_open($options,$magicFile);

			if($info && ($result=finfo_file($info,$file))!==false)
				return $result;
		}

		if(function_exists('mime_content_type') && ($result=mime_content_type($file))!==false)
			return $result;

		return $checkExtension ? self::getMimeTypeByExtension($file) : null;
	}

	
	public static function getMimeTypeByExtension($file,$magicFile=null)
	{
		static $extensions,$customExtensions=array();
		if($magicFile===null && $extensions===null)
			$extensions=require(Yii::getPathOfAlias('system.utils.mimeTypes').'.php');
		elseif($magicFile!==null && !isset($customExtensions[$magicFile]))
			$customExtensions[$magicFile]=require($magicFile);
		if(($ext=self::getExtension($file))!=='')
		{
			$ext=strtolower($ext);
			if($magicFile===null && isset($extensions[$ext]))
				return $extensions[$ext];
			elseif($magicFile!==null && isset($customExtensions[$magicFile][$ext]))
				return $customExtensions[$magicFile][$ext];
		}
		return null;
	}

	
	public static function getExtensionByMimeType($file,$magicFile=null)
	{
		static $mimeTypes,$customMimeTypes=array();
		if($magicFile===null && $mimeTypes===null)
			$mimeTypes=require(Yii::getPathOfAlias('system.utils.fileExtensions').'.php');
		elseif($magicFile!==null && !isset($customMimeTypes[$magicFile]))
			$customMimeTypes[$magicFile]=require($magicFile);
		if(($mime=self::getMimeType($file))!==null)
		{
			$mime=strtolower($mime);
			if($magicFile===null && isset($mimeTypes[$mime]))
				return $mimeTypes[$mime];
			elseif($magicFile!==null && isset($customMimeTypes[$magicFile][$mime]))
				return $customMimeTypes[$magicFile][$mime];
		}
		return null;
	}

	
	public static function createDirectory($dst,$mode=null,$recursive=false)
	{
		if($mode===null)
			$mode=0777;
		$prevDir=dirname($dst);
		if($recursive && !is_dir($dst) && !is_dir($prevDir))
			self::createDirectory(dirname($dst),$mode,true);
		$res=mkdir($dst, $mode);
		@chmod($dst,$mode);
		return $res;
	}
}
