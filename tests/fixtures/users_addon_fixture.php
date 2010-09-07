<?php
class UsersAddonFixture extends CakeTestFixture {
/**
 * Name
 *
 * @var string $name
 * @access public
 */
	public $name = 'UsersAddon';
/**
 * Table
 *
 * @var string $table
 * @access public
 */
	public $table = 'users_addons';
/**
 * Fields
 *
 * @var array $fields
 * @access public
 */
	public $fields = array(
		'id' => array('type'=>'string', 'null' => false, 'length' => 36, 'key' => 'primary'),
		'addon_id' => array('type'=>'string', 'null' => false, 'length' => 36, 'key' => 'index'),
		'user_id' => array('type'=>'string', 'null' => false, 'length' => 36),
		'position' => array('type'=>'float', 'null' => false, 'default' => '1', 'length' => 4),
		'active' => array('type'=>'boolean', 'null' => false, 'default' => '0'),
		'created' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
		'modified' => array('type'=>'datetime', 'null' => true, 'default' => NULL),
		'indexes' => array(
			'PRIMARY' => array('column' => 'id', 'unique' => 1), 
			'UNIQUE_ADDON_USER' => array('column' => array('addon_id', 'user_id'), 'unique' => 1))
	);
/**
 * Records
 *
 * @var array $records
 * @access public
 */
	public $records = array(
		array(
			'id' => '0fab7f82-a9ab-11dd-8943-00e018bfb339',
			'addon_id' => '706976ea-a752-11dd-bbc2-00e018bfb339',
			'user_id' => '47ea303a-3b2c-4251-b313-4816c0a800fa',
			'position' => 1,
			'active' => 1,
			'created' => '2008-03-25 01:35:35',
			'modified' => '2008-03-25 01:35:35'),
		array(
			'id' => '149e7472-a9ab-11dd-be1d-00e018bfb339',
			'addon_id' => 'f839b724-a752-11dd-94ca-00e018bfb339',
			'user_id' => '47ea303a-3b2c-4251-b313-4816c0a800fa',
			'position' => 2,
			'active' => 0,
			'created' => '2008-03-25 01:35:35',
			'modified' => '2008-03-25 01:35:35'),
		array(
			'id' => '1857670e-a9ab-11dd-b579-00e018bfb339',
			'addon_id' => '2f21bbf6-a753-11dd-81b6-00e018bfb339',
			'user_id' => '47ea303a-3b2c-4251-b313-4816c0a800fa',
			'position' => 3,
			'active' => 1,
			'created' => '2008-03-25 01:35:35',
			'modified' => '2008-03-25 01:35:35'));
}
?>