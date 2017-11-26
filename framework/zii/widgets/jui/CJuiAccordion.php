<?php


Yii::import('zii.widgets.jui.CJuiWidget');


class CJuiAccordion extends CJuiWidget
{
	/**
	 * @var array list of panels (panel title=>panel content).
	 * Note that neither panel title nor panel content will be HTML-encoded.
	 */
	public $panels=array();
	/**
	 * @var string the name of the container element that contains all panels. Defaults to 'div'.
	 */
	public $tagName='div';
	/**
	 * @var string the template that is used to generated every panel header.
	 * The token "{title}" in the template will be replaced with the panel title.
	 * Note that if you make change to this template, you may also need to adjust
	 * the 'header' setting in {@link options}.
	 */
	public $headerTemplate='<h3><a href="#">{title}</a></h3>';
	/**
	 * @var string the template that is used to generated every panel content.
	 * The token "{content}" in the template will be replaced with the panel content.
	 */
	public $contentTemplate='<div>{content}</div>';

	/**
	 * Run this widget.
	 * This method registers necessary javascript and renders the needed HTML code.
	 */
	public function run()
	{
		$id=$this->getId();
		if(isset($this->htmlOptions['id']))
			$id=$this->htmlOptions['id'];
		else
			$this->htmlOptions['id']=$id;

		echo CHtml::openTag($this->tagName,$this->htmlOptions)."\n";
		foreach($this->panels as $title=>$content)
		{
			echo strtr($this->headerTemplate,array('{title}'=>$title))."\n";
			echo strtr($this->contentTemplate,array('{content}'=>$content))."\n";
		}
		echo CHtml::closeTag($this->tagName);

		$options=CJavaScript::encode($this->options);
		Yii::app()->getClientScript()->registerScript(__CLASS__.'#'.$id,"jQuery('#{$id}').accordion($options);");
	}
}