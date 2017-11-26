<?php
require_once(Yii::getPathOfAlias('system.vendors.markdown.markdown').'.php');
if(!class_exists('HTMLPurifier_Bootstrap',false))
{
	require_once(Yii::getPathOfAlias('system.vendors.htmlpurifier').DIRECTORY_SEPARATOR.'HTMLPurifier.standalone.php');
	HTMLPurifier_Bootstrap::registerAutoload();
}

/**
 * CMarkdownParser is a wrapper of {@link http://michelf.com/projects/php-markdown/extra/ MarkdownExtra_Parser}.
 */
class CMarkdownParser extends MarkdownExtra_Parser
{
	
	public $highlightCssClass='hl-code';

	public $purifierOptions=null;

	
	public function safeTransform($content)
	{
		$content=$this->transform($content);
		$purifier=new HTMLPurifier($this->purifierOptions);
		$purifier->config->set('Cache.SerializerPath',Yii::app()->getRuntimePath());
		return $purifier->purify($content);
	}

	/**
	 * @return string the default CSS file that is used to highlight code blocks.
	 */
	public function getDefaultCssFile()
	{
		return Yii::getPathOfAlias('system.vendors.TextHighlighter.highlight').'.css';
	}

	
	public function _doCodeBlocks_callback($matches)
	{
		$codeblock = $this->outdent($matches[1]);
		if(($codeblock = $this->highlightCodeBlock($codeblock)) !== null)
			return "\n\n".$this->hashBlock($codeblock)."\n\n";
		else
			return parent::_doCodeBlocks_callback($matches);
	}

	
	public function _doFencedCodeBlocks_callback($matches)
	{
		return "\n\n".$this->hashBlock($this->highlightCodeBlock($matches[2]))."\n\n";
	}

	
	protected function highlightCodeBlock($codeblock)
	{
		if(($tag=$this->getHighlightTag($codeblock))!==null && ($highlighter=$this->createHighLighter($tag)))
		{
			$codeblock = preg_replace('/\A\n+|\n+\z/', '', $codeblock);
			$tagLen = strpos($codeblock, $tag)+strlen($tag);
			$codeblock = ltrim(substr($codeblock, $tagLen));
			$output=preg_replace('/<span\s+[^>]*>(\s*)<\/span>/', '\1', $highlighter->highlight($codeblock));
			return "<div class=\"{$this->highlightCssClass}\">".$output."</div>";
		}
		else
			return "<pre>".CHtml::encode($codeblock)."</pre>";
	}

	
	protected function getHighlightTag($codeblock)
	{
		$str = trim(current(preg_split("/\r|\n/", $codeblock,2)));
		if(strlen($str) > 2 && $str[0] === '[' && $str[strlen($str)-1] === ']')
			return $str;
	}

	
	protected function createHighLighter($options)
	{
		if(!class_exists('Text_Highlighter', false))
		{
			require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter').'.php');
			require_once(Yii::getPathOfAlias('system.vendors.TextHighlighter.Text.Highlighter.Renderer.Html').'.php');
		}
		$lang = current(preg_split('/\s+/', substr(substr($options,1), 0,-1),2));
		$highlighter = Text_Highlighter::factory($lang);
		if($highlighter)
			$highlighter->setRenderer(new Text_Highlighter_Renderer_Html($this->getHighlightConfig($options)));
		return $highlighter;
	}

	
	public function getHighlightConfig($options)
	{
		$config = array('use_language'=>true);
		if( $this->getInlineOption('showLineNumbers', $options, false) )
			$config['numbers'] = HL_NUMBERS_LI;
		$config['tabsize'] = $this->getInlineOption('tabSize', $options, 4);
		return $config;
	}

	
	public function getHiglightConfig($options)
	{
		return $this->getHighlightConfig($options);
	}

	
	protected function getInlineOption($name, $str, $defaultValue)
	{
		if(preg_match('/'.$name.'(\s*=\s*(\d+))?/i', $str, $v) && count($v) > 2)
			return $v[2];
		else
			return $defaultValue;
	}
}
