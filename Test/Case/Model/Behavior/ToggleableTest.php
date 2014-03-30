<?php
App::import('Behavior', 'Utils.Toggleable');

/**
 * Post Test Model
 */
class Post extends CakeTestModel {
	public $useTable = 'posts';
	//public $actsAs = array('Toggleable');
	public $alias = 'Post';
}

/**
 * Toggleable Test case
 */
class ToggleableTest extends CakeTestCase {
/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	public $fixtures = array('plugin.utils.post');

/**
 * Creates the model instance
 *
 * @return void
 */
	public function setUp() {
		$this->Post = new Post();
	}

/**
 * Destroy the model instance
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Post);
		unset($this->Behavior);
		ClassRegistry::flush();
	}

/**
 * testToggle
 *
 * @access public
 * @return void
 */
	public function testToggle() {
		$this->Post->Behaviors->load('Utils.Toggleable', array(
			'fields' => array(
				'deleted' => array(1, 0))));

		$this->assertEquals($this->Post->toggle(1, 'deleted'), 1);
		$this->assertEquals($this->Post->field('deleted'), true);
		$this->assertEquals($this->Post->toggle(1, 'deleted'), 0);
		$this->assertEquals($this->Post->field('deleted'), false);
	}

/**
 * testToggle
 *
 * @access public
 * @return void
 */
	public function testInvalidFieldException() {
		$this->Post->Behaviors->load('Utils.Toggleable', array(
			'fields' => array(
				'other_field' => array(1, 0))));

		$this->expectException('InvalidArgumentException');
		$this->Post->toggle(1, 'deleted');
	}

/**
 * testInvalidFieldStates
 *
 * @access public
 * @return void
 */
	public function testInvalidFieldStates() {
		$this->Post->Behaviors->load('Utils.Toggleable', array(
			'fields' => array(
				'deleted' => array(1))));

		$this->expectException('InvalidArgumentException');
		$this->Post->toggle(1, 'deleted');
	}

/**
 * testToggleInvalidRecord
 *
 * @access public
 * @return void
 */
	public function testToggleInvalidRecord() {
		$this->Post->Behaviors->load('Utils.Toggleable', array(
			'fields' => array(
				'deleted' => array(1))));

		try {
			$this->Post->toggle('invalid-record-id', 'deleted');
			$this->fail();
		} catch (Exception $ex) {
		}
	}

}
?>