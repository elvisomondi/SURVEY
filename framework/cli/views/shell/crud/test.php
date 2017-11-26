<?php
/**
 * This is the template for generating the functional test for controller.
 */
?>
<?php echo "<?php\n"; ?>

class <?php echo $modelClass; ?>Test extends WebTestCase
{
	public $fixtures=array(
		'<?php echo $fixtureName; ?>'=>'<?php echo $modelClass; ?>',
	);

	public function testShow()
	{
		$this->open('?r=<?php echo $controllerID; ?>/view&id=1');
	}

	public function testCreate()
	{
		$this->open('?r=<?php echo $controllerID; ?>/create');
	}

	public function testUpdate()
	{
		$this->open('?r=<?php echo $controllerID; ?>/update&id=1');
	}

	public function testDelete()
	{
		$this->open('?r=<?php echo $controllerID; ?>/view&id=1');
	}

	public function testList()
	{
		$this->open('?r=<?php echo $controllerID; ?>/index');
	}

	public function testAdmin()
	{
		$this->open('?r=<?php echo $controllerID; ?>/admin');
	}
}
