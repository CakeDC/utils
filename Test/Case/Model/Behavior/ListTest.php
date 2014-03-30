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
 * @var UsersAddon $UsersAddon
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
		$this->UsersAddon->Behaviors->load('Utils.List', array(
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
		$result = $this->UsersAddon->moveUp('useraddon-2');
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
		$result = $this->UsersAddon->moveDown('useraddon-2');
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
		$result = $this->UsersAddon->insertAt(1, 'useraddon-3');
		$this->assertTrue(!empty($result));
		$result = $this->UsersAddon->read('position', 'useraddon-3');
		$this->assertEquals($result['UsersAddon']['position'], 1);

		// insert somewhere in the middle
		$result = $this->UsersAddon->insertAt(2, 'useraddon-3');
		$this->assertTrue(!empty($result));
		$result = $this->UsersAddon->read('position', 'useraddon-3');
		$this->assertEquals($result['UsersAddon']['position'], 2);

		// insert at last position
		$position = $this->UsersAddon->find('count');
		$result = $this->UsersAddon->insertAt($position, 'useraddon-3');
		$this->assertTrue(!empty($result));
		$result = $this->UsersAddon->read('position', 'useraddon-3');
		$this->assertEquals($result['UsersAddon']['position'], $position);
	}

/**
 * Test to move an item to the bottom of the list
 *
 * @return void
 * @access public
 */
	public function testMoveToBottom() {
		$this->UsersAddon->moveToBottom('useraddon-1');
		$result = $this->UsersAddon->read('position', 'useraddon-1');
		$this->assertEquals($result['UsersAddon']['position'], 3);
	}

/**
 * Test to move an item to the top of the list
 *
 * @return void
 * @access public
 */
	public function testMoveToTop() {
		$this->UsersAddon->moveToTop('useraddon-3');
		$result = $this->UsersAddon->read('position', 'useraddon-3');
		$this->assertEquals($result['UsersAddon']['position'], 1);
	}

/**
 * Test to check if an item is the first in the list
 *
 * @return void
 * @access public
 */
	public function testIsFirst() {
		$result = $this->UsersAddon->isFirst('useraddon-1');
		$this->assertTrue($result);

		$result = $this->UsersAddon->isFirst('useraddon-3');
		$this->assertFalse($result);
	}

/**
 * Test to check if an item is the last in the list
 *
 * @return void
 * @access public
 */
	public function testIsLast() {
		$result = $this->UsersAddon->isLast('useraddon-3');
		$this->assertTrue($result);

		$result = $this->UsersAddon->isLast('useraddon-1');
		$this->assertFalse($result);
	}

/**
 * Test to disable/enabled callbacks
 *
 * @return void
 * @access public
 */
	public function testCallbacks() {
		$this->UsersAddon->Behaviors->unload('Utils.List');
		$this->UsersAddon->Behaviors->load('Utils.List', array(
			'positionColumn' => 'position',
			'scope' => 'user_id',
			'callbacks' => false,
			'validate' => false));
		$this->UsersAddon->beforeSaveFalse = false;
		$result = $this->UsersAddon->moveDown('useraddon-1');
		$this->assertTrue(!empty($result));
	}

/**
 * Tests that insert into list set position to end of list.
 *
 * @return void
 * @access public
 */
	public function testAutoInsertAtEndOfList() {
		$data = array(
			'UsersAddon' => array(
				'addon_id' => 'addon-4',
				'user_id' => 'user-1',
				'active' => 1));
		$this->UsersAddon->beforeSaveFalse = true;
		$this->UsersAddon->create($data);
		$result = $this->UsersAddon->save($data);
		$this->assertTrue(!empty($result));
		$this->assertEquals($result['UsersAddon']['position'], 4);
	}

/**
 * Tests that insert into list set position to end of list.
 *
 * @return void
 * @access public
 */
	public function testAutoInsertAtTopOfList() {
		$this->UsersAddon->Behaviors->unload('Utils.List');
		$this->UsersAddon->Behaviors->load('Utils.List', array(
			'positionColumn' => 'position',
			'addToTop' => true,
			'scope' => 'user_id'));
		$data = array(
			'UsersAddon' => array(
				'addon_id' => 'addon-5',
				'user_id' => 'user-1',
				'active' => 1));
		$this->UsersAddon->beforeSaveFalse = true;
		$this->UsersAddon->create($data);
		$result = $this->UsersAddon->save($data);
		$this->assertTrue(!empty($result));
		$this->assertEquals($result['UsersAddon']['position'], 1);
		$userAddons = $this->UsersAddon->find('all', array('order' => 'position'));
		$userAddons = Set::combine($userAddons, '/UsersAddon/id', '/UsersAddon/position');
		$this->assertEquals(array_values($userAddons), range(1,4));
	}

}
