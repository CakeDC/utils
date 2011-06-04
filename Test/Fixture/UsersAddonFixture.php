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
			'id' => 'useraddon-1',
			'addon_id' => 'addon-1',
			'user_id' => 'user-1',
			'position' => 1,
			'active' => 1,
			'created' => '2008-03-25 01:35:35',
			'modified' => '2008-03-25 01:35:35'),
		array(
			'id' => 'useraddon-2',
			'addon_id' => 'addon-2',
			'user_id' => 'user-1',
			'position' => 2,
			'active' => 0,
			'created' => '2008-03-25 01:35:35',
			'modified' => '2008-03-25 01:35:35'),
		array(
			'id' => 'useraddon-3',
			'addon_id' => 'addon-3',
			'user_id' => 'user-1',
			'position' => 3,
			'active' => 1,
			'created' => '2008-03-25 01:35:35',
			'modified' => '2008-03-25 01:35:35'));
}
