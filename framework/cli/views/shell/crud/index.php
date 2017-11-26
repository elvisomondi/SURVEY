<?php
/**
 * This is the template for generating the index view for crud
 */
?>
<?php
echo "<?php\n";
$label=$this->class2name($modelClass,true);
$route=$modelClass.'/index';
$route[0]=strtolower($route[0]);
echo "\$this->breadcrumbs=array(
	'$label',
);\n";
?>

$this->menu=array(
	array('label'=>'Create <?php echo $modelClass; ?>', 'url'=>array('create')),
	array('label'=>'Manage <?php echo $modelClass; ?>', 'url'=>array('admin')),
);
?>

<h1><?php echo $label; ?></h1>

<?php echo "<?php"; ?> $this->widget('zii.widgets.CListView', array(
	'dataProvider'=>$dataProvider,
	'itemView'=>'_view',
)); ?>
