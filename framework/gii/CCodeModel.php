<?php
/**
 * CCodeModel is the base class for model classes that are used to generate code.
 
 */
abstract class CCodeModel extends CFormModel
{
	const STATUS_NEW=1;
	const STATUS_PREVIEW=2;
	const STATUS_SUCCESS=3;
	const STATUS_ERROR=4;

	static $keywords=array(
		'__class__',
		'__dir__',
		'__file__',
		'__function__',
		'__line__',
		'__method__',
		'__namespace__',
		'abstract',
		'and',
		'array',
		'as',
		'break',
		'case',
		'catch',
		'cfunction',
		'class',
		'clone',
		'const',
		'continue',
		'declare',
		'default',
		'die',
		'do',
		'echo',
		'else',
		'elseif',
		'empty',
		'enddeclare',
		'endfor',
		'endforeach',
		'endif',
		'endswitch',
		'endwhile',
		'eval',
		'exception',
		'exit',
		'extends',
		'final',
		'final',
		'for',
		'foreach',
		'function',
		'global',
		'goto',
		'if',
		'implements',
		'include',
		'include_once',
		'instanceof',
		'interface',
		'isset',
		'list',
		'namespace',
		'new',
		'old_function',
		'or',
		'parent',
		'php_user_filter',
		'print',
		'private',
		'protected',
		'public',
		'require',
		'require_once',
		'return',
		'static',
		'switch',
		'this',
		'throw',
		'try',
		'unset',
		'use',
		'var',
		'while',
		'xor',
	);


	public $answers;
	
	public $template;
	
	public $files=array();
	
	public $status=self::STATUS_NEW;

	private $_stickyAttributes=array();

	
	abstract public function prepare();

	
	public function rules()
	{
		return array(
			array('template', 'required'),
			array('template', 'validateTemplate', 'skipOnError'=>true),
			array('template', 'sticky'),
		);
	}

	
	public function validateTemplate($attribute,$params)
	{
		$templates=$this->templates;
		if(!isset($templates[$this->template]))
			$this->addError('template', 'Invalid template selection.');
		else
		{
			$templatePath=$this->templatePath;
			foreach($this->requiredTemplates() as $template)
			{
				if(!is_file($templatePath.'/'.$template))
					$this->addError('template', "Unable to find the required code template file '$template'.");
			}
		}
	}

	
	public function classExists($name)
	{
		return class_exists($name,false) && in_array($name, get_declared_classes());
	}

	
	public function attributeLabels()
	{
		return array(
			'template'=>'Code Template',
		);
	}

	
	public function requiredTemplates()
	{
		return array();
	}

	/**
	 * Saves the generated code into files.
	 */
	public function save()
	{
		$result=true;
		foreach($this->files as $file)
		{
			if($this->confirmed($file))
				$result=$file->save() && $result;
		}
		return $result;
	}

	
	public function successMessage()
	{
		return 'The code has been generated successfully.';
	}

	
	public function errorMessage()
	{
		return 'There was some error when generating the code. Please check the following messages.';
	}

	
	public function getTemplates()
	{
		return Yii::app()->controller->templates;
	}

	/**
	 * @return string the directory that contains the template files.
	 * @throws CHttpException if {@link templates} is empty or template selection is invalid
	 */
	public function getTemplatePath()
	{
		$templates=$this->getTemplates();
		if(isset($templates[$this->template]))
			return $templates[$this->template];
		elseif(empty($templates))
			throw new CHttpException(500,'No templates are available.');
		else
			throw new CHttpException(500,'Invalid template selection.');

	}

	/**
	 * @param CCodeFile $file whether the code file should be saved
	 * @return bool whether the confirmation is found in {@link answers} with appropriate {@link operation}
	 */
	public function confirmed($file)
	{
		return $this->answers===null && $file->operation===CCodeFile::OP_NEW
			|| is_array($this->answers) && isset($this->answers[md5($file->path)]);
	}

	
	public function render($templateFile,$_params_=null)
	{
		if(!is_file($templateFile))
			throw new CException("The template file '$templateFile' does not exist.");

		if(is_array($_params_))
			extract($_params_,EXTR_PREFIX_SAME,'params');
		else
			$params=$_params_;
		ob_start();
		ob_implicit_flush(false);
		require($templateFile);
		return ob_get_clean();
	}

	/**
	 * @return string the code generation result log.
	 */
	public function renderResults()
	{
		$output='Generating code using template "'.$this->templatePath."\"...\n";
		foreach($this->files as $file)
		{
			if($file->error!==null)
				$output.="<span class=\"error\">generating {$file->relativePath}<br/>           {$file->error}</span>\n";
			elseif($file->operation===CCodeFile::OP_NEW && $this->confirmed($file))
				$output.=' generated '.$file->relativePath."\n";
			elseif($file->operation===CCodeFile::OP_OVERWRITE && $this->confirmed($file))
				$output.=' overwrote '.$file->relativePath."\n";
			else
				$output.='   skipped '.$file->relativePath."\n";
		}
		$output.="done!\n";
		return $output;
	}

	
	public function sticky($attribute,$params)
	{
		if(!$this->hasErrors())
			$this->_stickyAttributes[$attribute]=$this->$attribute;
	}

	/**
	 * Loads sticky attributes from a file and populates them into the model.
	 */
	public function loadStickyAttributes()
	{
		$this->_stickyAttributes=array();
		$path=$this->getStickyFile();
		if(is_file($path))
		{
			$result=@include($path);
			if(is_array($result))
			{
				$this->_stickyAttributes=$result;
				foreach($this->_stickyAttributes as $name=>$value)
				{
					if(property_exists($this,$name) || $this->canSetProperty($name))
						$this->$name=$value;
				}
			}
		}
	}

	/**
	 * Saves sticky attributes into a file.
	 */
	public function saveStickyAttributes()
	{
		$path=$this->getStickyFile();
		@mkdir(dirname($path),0755,true);
		file_put_contents($path,"<?php\nreturn ".var_export($this->_stickyAttributes,true).";\n");
	}

	/**
	 * @return string the file path that stores the sticky attribute values.
	 */
	public function getStickyFile()
	{
		return Yii::app()->runtimePath.'/gii-'.Yii::getVersion().'/'.get_class($this).'.php';
	}

	
	public function pluralize($name)
	{
		$rules=array(
			'/(m)ove$/i' => '\1oves',
			'/(f)oot$/i' => '\1eet',
			'/(c)hild$/i' => '\1hildren',
			'/(h)uman$/i' => '\1umans',
			'/(m)an$/i' => '\1en',
			'/(s)taff$/i' => '\1taff',
			'/(t)ooth$/i' => '\1eeth',
			'/(p)erson$/i' => '\1eople',
			'/([m|l])ouse$/i' => '\1ice',
			'/(x|ch|ss|sh|us|as|is|os)$/i' => '\1es',
			'/([^aeiouy]|qu)y$/i' => '\1ies',
			'/(?:([^f])fe|([lr])f)$/i' => '\1\2ves',
			'/(shea|lea|loa|thie)f$/i' => '\1ves',
			'/([ti])um$/i' => '\1a',
			'/(tomat|potat|ech|her|vet)o$/i' => '\1oes',
			'/(bu)s$/i' => '\1ses',
			'/(ax|test)is$/i' => '\1es',
			'/s$/' => 's',
		);
		foreach($rules as $rule=>$replacement)
		{
			if(preg_match($rule,$name))
				return preg_replace($rule,$replacement,$name);
		}
		return $name.'s';
	}

	
	public function class2id($name)
	{
		return trim(strtolower(str_replace('_','-',preg_replace('/(?<![A-Z])[A-Z]/', '-\0', $name))),'-');
	}

	
	public function class2name($name,$ucwords=true)
	{
		$result=trim(strtolower(str_replace('_',' ',preg_replace('/(?<![A-Z])[A-Z]/', ' \0', $name))));
		return $ucwords ? ucwords($result) : $result;
	}

	
	public function class2var($name)
	{
		$name[0]=strtolower($name[0]);
		return $name;
	}

	
	public function validateReservedWord($attribute,$params)
	{
		$value=$this->$attribute;
		if(in_array(strtolower($value),self::$keywords))
			$this->addError($attribute, $this->getAttributeLabel($attribute).' cannot take a reserved PHP keyword.');
	}
}