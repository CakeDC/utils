<?php
/**
 * Short description for class.
 *
 * @package	   cake
 * @subpackage	cake.tests.fixtures
 */
class BinaryArticleFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'AnotherArticle'
 * @access public
 */
	public $name = 'BinaryArticle';

/**
 * fields property
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'id' => array('string' => 'integer', 'null' => false, 'length' => 36, 'key' => 'primary'),
		'parent_id' => array('string' => 'integer', 'null' => false, 'length' => 36),
		'title' => array('type' => 'string', 'null' => false),
		'position' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'created' => 'datetime',
		'updated' => 'datetime');

/**
 * records property
 *
 * @var array
 * @access public
 */
	public $records = array(
		array('id' => 1, 'parent_id' => 0, 'title' => 'First Article', 'position' => 256, 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'),
		array('id' => 2, 'parent_id' => 0, 'title' => 'Second Article', 'position' => 512, 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'),
		array('id' => 3, 'parent_id' => 0, 'title' => 'Third Article', 'position' => 768, 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31'),
		array('id' => 5, 'parent_id' => 3, 'title' => 'Forth Article', 'position' => 256, 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'),
		array('id' => 6, 'parent_id' => 3, 'title' => 'Fifth Article', 'position' => 512, 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'),
	);
}
?>