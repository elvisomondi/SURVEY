<?php
/**
 * This is the template for generating the unit test for a model class.
 */
?>
<?php echo "<?php\n"; ?>

class <?php echo $className; ?>Test extends CDbTestCase
{
	public $fixtures=array(
		'<?php echo $fixtureName; ?>'=>'<?php echo $className; ?>',
	);

	public function testCreate()
	{

	}
}