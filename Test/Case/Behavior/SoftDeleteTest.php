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
// App::uses('SoftDelete', 'Utils.Model/Behavior/SoftDelete');

/**
 * SoftDeleteTestBehavior
 *
 * @package default
 * @author Predominant
 */
// class SoftDeleteTestBehavior extends SoftDeleteBehavior {
// }

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
	public $actsAs = array('Utils.SoftDelete');

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
	public function setUp() {
		$this->Post = new SoftDeletedPost();
	}

/**
 * Destroy the model instance
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Post);
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
		$this->Post->Behaviors->unload('SoftDelete');
		$data = $this->Post->read(null, 1);
		$this->assertEqual($data['Post']['deleted'], true);
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
		$this->Post->Behaviors->unload('SoftDelete');
		$data = $this->Post->read(null, 1);
		$this->assertEqual($data['Post']['deleted'], false);
	}

/**
 * testSoftDeletePurge
 *
 * @return void
 */
	public function testSoftDeletePurge() {
		$this->Post->Behaviors->disable('SoftDelete');
		$data = $this->Post->read(null, 3);
		$this->assertTrue(!empty($data));
		$this->Post->Behaviors->enable('SoftDelete');
		$data = $this->Post->read(null, 3);
		$this->assertFalse($data);
		$count = $this->Post->purgeDeletedCount();
		$this->assertEqual($count, 1);
		$this->Post->purgeDeleted();
		
		$data = $this->Post->read(null, 3);
		$this->assertFalse($data);
		$this->Post->Behaviors->disable('SoftDelete');
		$data = $this->Post->read(null, 3);
		$this->assertFalse($data);
	}

}
