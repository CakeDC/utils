<?php
/**
 * Short description for class.
 *
 * @package	   cake
 * @subpackage	cake.tests.fixtures
 */
class ProductFixture extends CakeTestFixture {

/**
 * name property
 *
 * @var string 'AnotherPost'
 * @access public
 */
	public $name = 'Product';

	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'name' => array('type' => 'string'),
		'description' => array('type' => 'string'),
		'published' => array('type' => 'boolean', 'null' => false, 'default' => '0'),
		'publish_date' => 'datetime',
	);

	public $records = array(
		array('id' => 1, 'name'=> 'Foot Ball DVD', 'description' =>'The best footie matches ever', 'published' => 0, 'publish_date' => null),
		array('id' => 2, 'name'=> 'Cook like Jamie DVD', 'description' =>'Learn to cook like Jamie Oliver', 'published' => 1, 'publish_date' => null),
		array('id' => 3, 'name'=> 'Utimte Fishing', 'description' =>'Where to Fish in the UK', 'published' => 0, 'publish_date' => '2004-06-19 21:05:31'),
		array('id' => 4, 'name'=> 'Nigella from the Heart', 'description' =>'Nigella Eat your heart out', 'published' => 1, 'publish_date' => '2005-12-29 08:00:43'),
	);
}
?>