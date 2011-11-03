<?php
/**
 * Short description for class.
 *
 * @package       cake
 * @subpackage    cake.tests.fixtures
 */
class BArticleFixture extends CakeTestFixture {

/**
 * Name
 *
 * @var string 
 * @access public
 */
	public $name = 'BArticle';

/**
 * Fields
 *
 * @var array
 * @access public
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'null' => false, 'length' => 11, 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false),
		'parent_id' => array('type' => 'string', 'null' => true, 'default' => NULL, 'length' => 36),
		'lft' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'rght' => array('type' => 'integer', 'null' => false, 'default' => NULL),
		'created' => 'datetime',
		'modified' => 'datetime');

	public $records = array(
		array(
			'id' => 1,
			'title' => 'First article',
			'parent_id' => NULL,
			'lft' => 65537,
			'rght' => 65542,
			'created' => '2010-02-03 16:44:34',
			'modified' => '2010-02-03 16:44:34',
		),
			array(
				'id' => 2,
				'title' => 'First article - child 1',
				'parent_id' => 1,
				'lft' => 65538,
				'rght' => 65541,
				'created' => '2010-02-03 17:07:06',
				'modified' => '2010-02-03 17:07:06',
			),
				array(
					'id' => 3,
					'title' => 'First article - child 1 - subchild 1',
					'parent_id' => 2,
					'lft' => 65539,
					'rght' => 65540,
					'created' => '2010-02-03 17:42:27', 
					'modified' => '2010-02-03 17:42:27'),
		array(
			'id' => 4, 'title' => 'Second article',
			'parent_id' => NULL,
			'lft' => 131073,
			'rght' => 131074,
			'created' => '2010-02-03 17:46:47',
			'modified' => '2010-02-03 17:46:47')
		);

}
?>