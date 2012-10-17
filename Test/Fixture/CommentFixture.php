<?php
// For the inheritable behavior
class CommentFixture extends CakeTestFixture {
    var $name = 'Comment';
    
    var $fields = array(
        'id' => array('type' => 'integer', 'key' => 'primary'),
        'content_id' => array('type' => 'integer', 'null' => false),
        'body' => 'text',
        'published' => array('type' => 'string', 'length' => 1, 'default' => 'N'),
        'permalink' => array('type' => 'string'),
        'parent_id' => array('type' => 'integer'),
        'created' => 'datetime',
        'updated' => 'datetime'
    );
    
}
?>