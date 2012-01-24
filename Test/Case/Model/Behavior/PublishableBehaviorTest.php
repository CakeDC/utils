<?php
App::uses('AppModel', 'Model');
App::uses('Model', 'Model');
App::uses('PublishableBehavior', 'Utils.Model/Behavior');

class Date
{
    protected $date;

    public function __construct($date = null)
    {
        $this->date = $date ? $date : time();
    }

    public function today()
    {
        // to something with $this->date
    }
}

class Product extends CakeTestModel {
	public $useTable = 'products';
}

/**
 * PublishableBehavior Test Case
 *
 */
class PublishableBehaviorTestCase extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array('plugin.utils.product');

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();

		$this->Product = ClassRegistry::init('Product');
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Product);

		parent::tearDown();
	}
	


/**
 * startTest method
 *
 * @return void
 */
	public function startTest($method) {
		parent::startTest($method);

		$this->Product = ClassRegistry::init('Product');
	}

/**
 * endTest method
 *
 * @return void
 */
	public function endTest($method) {
		unset($this->Product);

		parent::endTest($method);
	}

/**
 * testPublish method
 *
 * @return void
 */
	public function testFindBehaviorEnabled() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'find' => true));
		Configure::write('Publishable.disable', false);
		debug($this->Product->find('all'));
		//$this->assertEqual($this->Product->find('all'), true);
		//$this->assertEqual($this->Product->field('published'), 1);

	}

/**
 * testPublish method
 *
 * @return void
 */
	public function testFindBehaviorDisabled() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'find' => true));
		Configure::write('Publishable.disable', true);
		debug($this->Product->find('all'));
		//$this->assertEqual($this->Product->find('all'), true);
		//$this->assertEqual($this->Product->field('published'), 1);

	}

/**
 * testPublish method
 *
 * @return void
 */
	public function testPublish() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'find' => true));
		Configure::write('Publishable.disable', false);
		$this->Product->id = 1;
		$this->assertEqual($this->Product->publish(1), true);
		$this->assertTrue($this->Product->field('published'));

	}
/**
 * testUnPublish method
 *
 * @return void
 */
	public function testUnPublish() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'find' => true));
		Configure::write('Publishable.disable', false);
		$this->Product->id = 2;
		$this->assertEqual($this->Product->unPublish(2), true);
		$this->assertFalse($this->Product->field('published'));
	}
///**
// * testEnablePublishable method
// *
// * @return void
// */
//	public function testEnablePublishable() {
//
//	}

/**
 * testPublish method
 *
 * @return void
 */
	public function testFindBehaviorEnabledWithDate() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'field_date' => 'publish_date', 'find' => true));
		Configure::write('Publishable.disable', false);
		debug($this->Product->find('all'));
		//$this->assertEqual($this->Product->find('all'), true);
		//$this->assertEqual($this->Product->field('published'), 1);

	}

/**
 * testPublish method
 *
 * @return void
 */
	public function testFindBehaviorDisabledWithDate() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'field_date' => 'publish_date', 'find' => true));
		Configure::write('Publishable.disable', true);
		debug($this->Product->find('all'));
		//$this->assertEqual($this->Product->find('all'), array());

	}

/**
 * testPublish method
 *
 * @return void
 */
	public function testPublishWithDate() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'field_date' => 'publish_date', 'find' => true));
		Configure::write('Publishable.disable', false);
		$this->Product->id = 3;
		$this->assertEqual($this->Product->publish(3), true);
		sleep(6);
		$date = new Date(strtotime('2010-10-17 00:00:00'));
		$this->assertTrue($this->Product->field('published'));
		echo($date->today());
		$this->assertEquals($this->Product->field('publish_date'), $date->today());
		$this->Product->Behaviors->detach('Utils.Publishable');
		debug($this->Product->findById(3));
		//$this->assertNull($this->Product->field('publish_date'));

	}
/**
 * testUnPublish method
 *
 * @return void
 */
	public function testUnPublishWithDate() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'field_date' => 'publish_date', 'find' => true));
		Configure::write('Publishable.disable', false);
		$this->Product->id = 4;
		$this->assertEqual($this->Product->unPublish(4), true);
		$this->assertFalse($this->Product->field('published'));
		$this->Product->Behaviors->detach('Utils.Publishable');
		debug($this->Product->findById(4));
	}
/**array('id' => 2, 'name'=> 'Cook like Jamie DVD', 'description' =>'Learn to cook like Jamie Oliver', 'published' => 1, 'publish_date' => null)
 * testEnablePublishable method
 *
 * @return void
 */
	public function testSaveWithDate() {
		$this->Product->Behaviors->attach('Utils.Publishable', array(
			'field' => 'published', 'field_date' => 'publish_date', 'find' => true));
		Configure::write('Publishable.disable', false);
		$this->Product->id = 4;
		$this->Product->unPublish(4, array('id' => 4, 'name'=> 'Nigella from the Heart (discontinued)', 'description' =>'Nigella Eat your heart out', 'published' => 0), false);
		$this->Product->id = 3;
		$this->Product->publish(3, array('id' => 3, 'name'=> 'Utimte Fishing (updated for wales)', 'description' =>'Where to Fish in the UK (updated to include more welsh spots)', 'published' => 1), false);
	
		Configure::write('Publishable.disable', true);
		debug($this->Product->findById(4));
		debug($this->Product->findById(3));
	}
///**array('id' => 2, 'name'=> 'Cook like Jamie DVD', 'description' =>'Learn to cook like Jamie Oliver', 'published' => 1, 'publish_date' => null)
// * testEnablePublishable method
// *
// * @return void
// */
//	public function testEnablePublishableWithDate() {
//
//	}
}
