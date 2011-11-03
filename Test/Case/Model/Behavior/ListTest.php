<?php
class UsersAddon extends CakeTestModel {
/**
 * Name
 *
 * @var string
 * @access public
 */
	public $name = 'UsersAddon';

/**
 * The return value of beforeSave() for the test model to test callbacks
 *
 * @var string
 * @access public
 */
	public $beforeSaveFalse = false;

/**
 * Behaviors to load with this model
 *
 * @var array $actsAs
 * @access public
 */
	public $actsAs = array(
		'Utils.List' => array(
			'positionColumn' => 'position',
			'scope' => 'user_id'));

/**
 * beforeSave callback
 * 
 * @return boolean
 */
	public function beforeSave() {
		return $this->beforeSaveFalse;
	}

}

class ListTest extends CakeTestCase {
/**
 * Holds the instance of the model
 *
 * @var mixed $UsersAddon
 * @access public
 */
	public $UsersAddon = null;

/**
 * Fixtures associated with this test case
 *
 * @var array
 * @access public
 */
	public $fixtures = array('plugin.utils.users_addon');

/**
 * Method executed before each test
 *
 * @access public
 */
	public function setUp() {
		$this->UsersAddon = ClassRegistry::init('UsersAddon');
		$this->UsersAddon->Behaviors->attach('Utils.List', array(
			'positionColumn' => 'position',
			'scope' => 'user_id'));
	}

/**
 * Method executed after each test
 *
 * @return void
 * @access public
 */
	public function tearDown() {
		unset($this->UsersAddon);
		ClassRegistry::flush();
	}

/**
 * Test for moving an item one step up
 *
 * @return void
 * @access public
 */
	public function testMoveUp() {
		$result = $this->UsersAddon->moveUp('149e7472-a9ab-11dd-be1d-00e018bfb339');
		$this->assertTrue(!empty($result));

		$result = $this->UsersAddon->moveUp('non-existing-uuid');
		$this->assertFalse($result);
	}

/**
 * Test for moving an item one step down
 *
 * @return void
 * @access public
 */
	public function testMoveDown() {
		$result = $this->UsersAddon->moveDown('149e7472-a9ab-11dd-be1d-00e018bfb339');
		$this->assertTrue(!empty($result));

		$result = $this->UsersAddon->moveDown('non-existing-uuid');
		$this->assertFalse($result);
	}

/**
 * Test for inserting an item at certain position
 *
 * @return void
 * @access public
 */
	public function testInsertAt() {
		$result = $this->UsersAddon->insertAt(1, '1857670e-a9ab-11dd-b579-00e018bfb339');
		$this->assertTrue(!empty($result));
	}

/**
 * Test to move an item to the bottom of the list
 *
 * @return void
 * @access public
 */
	public function testMoveToBottom() {
		$this->UsersAddon->moveToBottom('0fab7f82-a9ab-11dd-8943-00e018bfb339');
		$result = $this->UsersAddon->read('position', '0fab7f82-a9ab-11dd-8943-00e018bfb339');
		$this->assertEqual($result['UsersAddon']['position'], 3);
	}

/**
 * Test to move an item to the top of the list
 *
 * @return void
 * @access public
 */
	public function testMoveToTop() {
		$this->UsersAddon->moveToTop('1857670e-a9ab-11dd-b579-00e018bfb339');
		$result = $this->UsersAddon->read('position', '1857670e-a9ab-11dd-b579-00e018bfb339');
		$this->assertEqual($result['UsersAddon']['position'], 1);
	}

/**
 * Test to check if an item is the first in the list
 *
 * @return void
 * @access public
 */
	public function testIsFirst() {
		$result = $this->UsersAddon->isFirst('0fab7f82-a9ab-11dd-8943-00e018bfb339');
		$this->assertTrue($result);

		$result = $this->UsersAddon->isFirst('1857670e-a9ab-11dd-b579-00e018bfb339');
		$this->assertFalse($result);
	}

/**
 * Test to check if an item is the last in the list
 *
 * @return void
 * @access public
 */
	public function testIsLast() {
		$result = $this->UsersAddon->isLast('1857670e-a9ab-11dd-b579-00e018bfb339');
		$this->assertTrue($result);

		$result = $this->UsersAddon->isLast('0fab7f82-a9ab-11dd-8943-00e018bfb339');
		$this->assertFalse($result);
	}

/**
 * Test to disable/enabled callbacks
 *
 * @return void
 * @access public
 */
	public function testCallbacks() {
		$this->UsersAddon->Behaviors->detach('Utils.List');
		$this->UsersAddon->Behaviors->attach('Utils.List', array(
			'positionColumn' => 'position',
			'scope' => 'user_id',
			'callbacks' => false,
			'validate' => false));
		$this->UsersAddon->beforeSaveFalse = false;
		$result = $this->UsersAddon->moveDown('149e7472-a9ab-11dd-be1d-00e018bfb339');
		$this->assertTrue(!empty($result));
	}

}
?>