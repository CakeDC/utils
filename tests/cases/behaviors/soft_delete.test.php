<?php
App::import('Behavior', 'Utils.SoftDelete');

/**
 * SoftDelete Test Behavior
 **/
class SoftDeleteTestBehavior extends SoftDeleteBehavior {
}

/**
 * SoftDeleted Post
 */
class SoftDeletedPost extends CakeTestModel {
	public $useTable = 'posts';
	public $actsAs = array('SoftDeleteTest');
	public $alias = 'Post';
}

/**
 * SoftDelete Test case
 */
class SoftDeleteTest extends CakeTestCase {
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
	public function startTest() {
		$this->Post = new SoftDeletedPost();
		$this->Behavior = new SoftDeleteTestBehavior();
	}

/**
 * Destroy the model instance
 *
 * @return void
 */
	public function endTest() {
		unset($this->Post);
		unset($this->Behavior);
		ClassRegistry::flush();
	}

/**
 * Test saving a item
 *
 * @return void
 */
	public function testSoftDelete() {
		$data = $this->Post->read(null, 1);
		$result = $this->Post->delete(1);
		$data = $this->Post->read(null, 1);
		$this->assertFalse($data);
		$this->Post->Behaviors->detach('SoftDeleteTest');
		$data = $this->Post->read(null, 1);
		$this->assertEqual($data['Post']['deleted'], 1);
		$this->assertEqual($data['Post']['updated'], $data['Post']['deleted_date']);
	}
		
	public function testUnDelete() {
		$data = $this->Post->read(null, 1);
		$result = $this->Post->delete(1);
		$result = $this->Post->undelete(1);
		$data = $this->Post->read(null, 1);
		$this->assertEqual($data['Post']['deleted'], 0);
	}
		
	public function testSoftDeletePurge() {
		$this->Post->Behaviors->disable('SoftDeleteTest');
		$data = $this->Post->read(null, 3);
		$this->assertTrue(!empty($data));
		$this->Post->Behaviors->enable('SoftDeleteTest');
		$data = $this->Post->read(null, 3);
		$this->assertFalse($data);
		$count = $this->Post->purgeDeletedCount();
		$this->assertEqual($count, 1);
		$this->Post->purgeDeleted();
		
		$data = $this->Post->read(null, 3);
		$this->assertFalse($data);
		$this->Post->Behaviors->disable('SoftDeleteTest');
		$data = $this->Post->read(null, 3);
		$this->assertFalse($data);
	}
		
		// $result = $this->Model->read();
		// $this->assertEqual($result['SoftDeletedPost']['slug'], 'fourth_Post');

		///Should not update
		// $this->Model->saveField('title', 'Fourth Post (Part 1)');
		// $result = $this->Model->read();
		// $this->assertEqual($result['SoftDeletedPost']['slug'], 'fourth_Post');

		////Should update
		// $this->Model->Behaviors->SluggableTest->settings['SoftDeletedPost']['update'] = true;
		// $this->Model->saveField('title', 'Fourth Post (Part 2)');
		// $result = $this->Model->read();
		// $this->assertEqual($result['SoftDeletedPost']['slug'], 'fourth_Post_part_2');

		////Updating the item should not update the slug
		// $this->Model->saveField('body', 'Here goes the content.');
		// $result = $this->Model->read();
		// $this->assertEqual($result['SoftDeletedPost']['slug'], 'fourth_Post_part_2');

}
?>