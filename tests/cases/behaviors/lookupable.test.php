<?php
App::import('Behavior', 'Utils.Lookupable');

/**
 * Post Test Model
 */
class Post extends CakeTestModel {
	public $useTable = 'posts';
	//public $actsAs = array('Utils.Lookupable');
	public $alias = 'Post';
	public $belongsTo = array(
		'Article');
}

/**
 * Lookupable Test case
 */
class LookupableTest extends CakeTestCase {
/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	public $fixtures = array(
		'plugin.utils.post',
		'plugin.utils.article');

/**
 * Creates the model instance
 *
 * @return void
 * @access public
 */
	public function startTest() {
		$this->Post = new Post();
	}

/**
 * Destroy the model instance
 *
 * @return void
 * @access public
 */
	public function endTest() {
		unset($this->Post);
		ClassRegistry::flush();
	}

/**
 * testAddRecordAndLookup
 *
 * @return void
 * @access public
 */
	public function testAddRecordAndLookup() {
		$this->Post->Behaviors->attach('Utils.Lookupable', array(
			'types' => array(
				'Article')));
		$this->Post->create();
		$firstResult = $this->Post->save(array(
			'Post' => array(
				'title' => 'foobar',
				'article_title' => 'Im looked up!'),
			'Article' => array(
				'slug' => 'TEST',
				'tiny_slug' => '1')));

		$result = $this->Post->Article->find('first', array(
			'conditions' => array(
				'Article.title' => 'Im looked up!')));
		$this->assertTrue(is_array($result));
		$this->assertEqual($result['Article']['title'], 'Im looked up!');

		// another post with the same before created article
		$this->Post->create();
		$secondResult = $this->Post->save(array(
			'Post' => array(
				'title' => 'foobar123',
				'article_title' => 'Im looked up!')));
		$this->assertEqual($firstResult['Post']['article_id'], $secondResult['Post']['article_id']);
	}

}
?>