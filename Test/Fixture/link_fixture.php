<?php
// For the inheritable behavior
class LinkFixture extends CakeTestFixture {
    var $name = 'Link';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'url' => array('type' => 'string'),
    );
    
    var $records = array(
    
        array('id' => 11, 'url'=> 'http://cakephp.org'),
        array('id' => 12, 'url'=> 'http://google.com'),
        
    );
    
}
?>