<?php
/**
 * CViewRenderer is the base class for view renderer classes.
 */
abstract class CViewRenderer extends CApplicationComponent implements IViewRenderer
{
	
	public $useRuntimePath=true;
	/**
	 * @var integer the chmod permission for temporary directories and files
	 * generated during parsing. Defaults to 0755 (owner rwx, group rx and others rx).
	 */
	public $filePermission=0755;
	/**
	 * @var string the extension name of the view file. Defaults to '.php'.
	 */
	public $fileExtension='.php';

	/**
	 * Parses the source view file and saves the results as another file.
	 * @param string $sourceFile the source view file path
	 * @param string $viewFile the resulting view file path
	 */
	abstract protected function generateViewFile($sourceFile,$viewFile);

	
	public function renderFile($context,$sourceFile,$data,$return)
	{
		if(!is_file($sourceFile) || ($file=realpath($sourceFile))===false)
			throw new CException(Yii::t('yii','View file "{file}" does not exist.',array('{file}'=>$sourceFile)));
		$viewFile=$this->getViewFile($sourceFile);
		if(@filemtime($sourceFile)>@filemtime($viewFile))
		{
			$this->generateViewFile($sourceFile,$viewFile);
			@chmod($viewFile,$this->filePermission);
		}
		return $context->renderInternal($viewFile,$data,$return);
	}

	/**
	 * Generates the resulting view file path.
	 * @param string $file source view file path
	 * @return string resulting view file path
	 */
	protected function getViewFile($file)
	{
		if($this->useRuntimePath)
		{
			$crc=sprintf('%x', crc32(get_class($this).Yii::getVersion().dirname($file)));
			$viewFile=Yii::app()->getRuntimePath().DIRECTORY_SEPARATOR.'views'.DIRECTORY_SEPARATOR.$crc.DIRECTORY_SEPARATOR.basename($file);
			if(!is_file($viewFile))
				@mkdir(dirname($viewFile),$this->filePermission,true);
			return $viewFile;
		}
		else
			return $file.'c';
	}
}
