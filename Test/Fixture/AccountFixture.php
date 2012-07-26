 <?php  

 class AccountFixture extends CakeTestFixture { 
    var $name = 'Account'; 
    var $fields = array( 
			'id' => array('type'=>'integer', 'null' => false, 'default' => null, 'length' => 10, 'key' => 'primary', 'extra' => 'auto_increment'),
			'name' => array('type'=>'string', 'null' => true, 'default' => '', 'length' => 100),
			'data' => array('type'=>'string', 'null' => true, 'default' => '', 'length' => 100),
			'state' => array('type'=>'string', 'null' => true, 'default' => '', 'length' => 32),
			'created' => array('type'=>'datetime', 'null' => true, 'default' => ''),
			'indexes' => array('PRIMARY' => array('column' => 'id', 'unique' => 1), )
		); 	
	
    var $records = array( 
		array ('id' => 1, 'name' => 'Account1', 'data' => '', 'state' => 'in'), 
    ); 
} 
