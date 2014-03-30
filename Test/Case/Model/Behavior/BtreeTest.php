<?php
App::uses('BtreeBehavior', 'Utils.Model/Behavior');
class BArticle extends CakeTestModel {
	public $useTable = 'b_articles';
	public $actsAs = array('Utils.Btree');
}

/**
 * BTree test case
 *
 */
class BtreeTest extends CakeTestCase {
	
/**
 * Model acting like the tested behavior
 * 
 * @var BArticle
 * @access public
 */
	public $Model = null;
	
/**
 * Behavior being tested
 * 
 * @var BtreeBehavior
 * @access public
 */
	public $Behavior = null;
	
/**
 * Fixtures
 *
 * @var array
 * @access public
 */
	public $fixtures = array('plugin.utils.b_article');

/**
 * Start test method
 *
 * @return void
 * @access public
 */
	public function setUp() {
		parent::setUp();
		$this->Model = new BArticle();
		$this->Behavior = $this->Model->Behaviors->Btree;
	}

/**
 * Destroy the model instance
 *
 * @return void
 * @access public
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Model, $this->Behavior);
		ClassRegistry::flush();
	}

/**
 * Test beforeSave method
 * 
 * @TODO Assert lft / rght values
 * @return void
 * @access public
 */
	public function testBeforeSave() {
		$data = array(
			$this->Model->alias => array(
				'title' => 'Third article',
				'parent_id' => 3));
		$this->Model->data = $data;
		$this->assertTrue($this->Behavior->beforeSave($this->Model));
	}

/**
 * Test children method
 * 
 * @return void
 * @access public
 */
	public function testChildren() {
		$result = $this->Model->children(false, true);
		$this->assertEquals(Set::extract('/BArticle/id', $result), array(1, 4));

		$this->Model->id = 1;
		$result = $this->Model->children();
		$this->assertEquals(Set::extract('/BArticle/id', $result), array(2, 3));
	}

/**
 * Test generatetreelist method
 *
 * @return void
 * @access public
 */
	public function testGeneratetreelist() {
		$result = $this->Model->generatetreelist();
		$expected = array(
			1 => 'First article',
			2 => '_First article - child 1',
			3 => '__First article - child 1 - subchild 1',
			4 => 'Second article');
		$this->assertEquals($result, $expected);
	}

/**
 * Test getparentnode method
 *
 * @return void
 * @access public
 */
	public function testGetParentNode() {
		$result = $this->Model->getparentnode(2);
		$this->assertEquals($result['BArticle']['id'], 1);

		$result = $this->Model->getparentnode(3);
		$this->assertEquals($result['BArticle']['id'], 2);
	}

/**
 * Test getparentnode method
 *
 * @return void
 * @access public
 */
	public function testGetPath() {
		$result = $this->Model->getpath(3);
		$this->assertEquals(Set::extract('/BArticle/id', $result), array(1, 2, 3));
	}

/**
 * Test getparentnode method
 *
 * @return void
 * @access public
 */
	public function testChildcount() {
		$this->assertEquals($this->Model->childcount(1, true), 1);
		$this->assertEquals($this->Model->childcount(null, false), 4);
	}

}
?>