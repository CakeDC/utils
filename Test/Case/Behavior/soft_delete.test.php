<?php
/**
 * Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::import('Behavior', 'Utils.SoftDelete');

/**
 * SoftDeleteTestBehavior
 *
 * @package default
 * @author Predominant
 */
class SoftDeleteTestBehavior extends SoftDeleteBehavior {
}

/**
 * SoftDeletedPost
 *
 * @package utils
 * @subpackage utils.tests.cases.behaviors
 */
class SoftDeletedPost extends CakeTestModel {

/**
 * Use Table
 *
 * @var string
 */
	public $useTable = 'posts';

/**
 * Behaviors
 *
 * @var array
 */
	public $actsAs = array('SoftDeleteTest');

/**
 * Alias
 *
 * @var string
 */
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
		$this->assertEqual($data[$this->Post->alias][$this->Post->primaryKey], 1);
		$result = $this->Post->delete(1);
		$this->assertFalse($result);
		$data = $this->Post->read(null, 1);
		$this->assertFalse($data);
		$this->Post->Behaviors->detach('SoftDeleteTest');
		$data = $this->Post->read(null, 1);
		$this->assertEqual($data['Post']['deleted'], 1);
		$this->assertEqual($data['Post']['updated'], $data['Post']['deleted_date']);
	}

/**
 * testUnDelete
 *
 * @return void
 */
	public function testUnDelete() {
		$data = $this->Post->read(null, 1);
		$result = $this->Post->delete(1);
		$result = $this->Post->undelete(1);
		$data = $this->Post->read(null, 1);
		$this->assertEqual($data['Post']['deleted'], 0);
	}

/**
 * testSoftDeletePurge
 *
 * @return void
 */
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
