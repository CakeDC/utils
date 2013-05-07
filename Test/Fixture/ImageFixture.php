<?php
/**
 * Short description for class.
 *
 * @package	   cake
 * @subpackage	cake.tests.fixtures
 */
class ImageFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'AnotherArticle'
 */
	public $name = 'Image';

/**
 * fields property
 *
 * @var array
 */
	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'file_name' => array('type' => 'string'),
		'file_size' => array('type' => 'string'),
		'content_type' => array('type' => 'string'),
	);

/**
 * Records property
 *
 * @var array
 */
	public $records = array(
		array('id' => 1, 'file_name'=> 'soccer_worldcup.jpg', 'file_size' =>' 53422', 'content_type' => 'image/jpeg'),
		array('id' => 2, 'file_name'=> 'dog.png', 'file_size'=>'431234', 'content_type'=>'image/png')
	);

}