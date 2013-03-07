<?php
/**
 * Short description for class.
 *
 * @package	   cake
 * @subpackage	cake.tests.fixtures
 */
class ArticleFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'AnotherArticle'
 */
	public $name = 'Article';

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'title' => array('type' => 'string', 'null' => false),
		'slug' => array('type' => 'string', 'null' => true),
		'tiny_slug' => array('type' => 'string', 'null' => true),
		'position' => array('type' => 'integer', 'null' => false, 'default' => '0', 'length' => 10),
		'created' => 'datetime',
		'updated' => 'datetime');

/**
 * Records property
 *
 * @var array
 */
	public $records = array(
		array('id' => 1, 'title' => 'First Article', 'slug' => 'first_article', 'tiny_slug' => '0', 'position' => 1, 'created' => '2007-03-18 10:39:23', 'updated' => '2007-03-18 10:41:31'),
		array('id' => 2, 'title' => 'Second Article', 'slug' => 'second_article', 'tiny_slug' => '1', 'position' => 2, 'created' => '2007-03-18 10:41:23', 'updated' => '2007-03-18 10:43:31'),
		array('id' => 3, 'title' => 'Third Article', 'slug' => 'third_article', 'tiny_slug' => '2', 'position' => 3, 'created' => '2007-03-18 10:43:23', 'updated' => '2007-03-18 10:45:31')
	);

}