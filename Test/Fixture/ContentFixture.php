<?php
// For the inheritable behavior
class ContentFixture extends CakeTestFixture {
	public $name = 'Content';

	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'user_id' => array('type' => 'integer', 'null' => true, 'default' => null),
		'title' => array('type' => 'string', 'null' => false),
		'body' => 'text',
		'published' => array('type' => 'string', 'length' => 1, 'default' => 'N'),
		'type' => array('type' => 'string', 'null' => false),
		'permalink' => array('type' => 'string'),
		'parent_id' => array('type' => 'integer'),
		'created' => 'datetime',
		'updated' => 'datetime'
	);

	public $records = array(

		/* Articles */
		array('id' => 1, 'parent_id' => 0, 'type'=>'Article', 'title'=> 'Unearthed rare monster in london', 'body'=> 'very strange discovery...', 'permalink'=> 'unearthed-rare-monster-in-london'),
		array('id' => 2, 'parent_id' => 0, 'type'=>'Article', 'title'=> 'about us', 'body'=> 'history of our company', 'permalink'=> 'about-us'),


		/*  Pages */
		array('id' => 100, 'parent_id' => 0, 'type' => 'Page', 'title' => 'Home page', 'body'=>'welcome to my site', 'permalink'=>''),
		array('id' => 101, 'parent_id' => 100, 'type'=>'Page', 'title'=> 'Frequent Asked Questions', 'body'=> 'questions and more...', 'permalink'=> 'faq'),
		array('id' => 102, 'parent_id' => 101, 'type'=>'Page', 'title'=> 'about us', 'body'=> 'CakePHP is a MVC PHP framework that aids development of... ', 'permalink'=> 'about-us'),

	);

}
?>