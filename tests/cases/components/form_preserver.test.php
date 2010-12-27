<?php
App::import('Controller', 'Controller', false);
App::import('Component', array('Utils.FormPreserver', 'Session', 'Auth'));

class Article extends CakeTestModel {
/**
 * 
 */
	public $name = 'Article';
}

class ArticlesTestController extends Controller {

/**
 * @var string
 * @access public
 */
	public $name = 'ArticlesTest';

/**
 * @var array
 * @access public
 */
	public $uses = array('Article');

/**
 * @var array
 * @access public
 */
	public $components = array('Utils.FormPreserver', 'Session', 'Auth');

/**
 * 
 */
	public function redirect($url, $status = NULL, $exit = true) {
		$this->redirectUrl = $url;
	}

}

class FormPreserverComponentTest extends CakeTestCase {
/**
 * Fixtures
 *
 * @var array
 * @access public
 */
	public $fixtures = array(
		'plugin.utils.article');
/**
 * setUp method
 *
 * @access public
 * @return void
 */
	function startTest() {
		$this->Controller = new ArticlesTestController();
		$this->Controller->constructClasses();
		$this->Controller->action = 'edit';
		$this->Controller->params = array(
			'named' => array(),
			'pass' => array(),
			'url' => array());
		$this->Controller->modelClass = 'Article';
		$this->Controller->Component->init($this->Controller);
		$this->Controller->Component->initialize($this->Controller);
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function endTest() {
		unset($this->Controller);
		ClassRegistry::flush();
	}

/**
 * testStartup
 *
 * @access public
 * @return void
 */
	public function testStartup() {
		$data = array(
			'_Token' => 'token',
			'ArticleTest' => array(
				'title' => 'Foo'));

		$this->Controller->data = $data;
		$this->Controller->FormPreserver->actions = array('edit');
		$this->Controller->FormPreserver->startup($this->Controller);

		$this->assertEqual($this->Controller->redirectUrl, array(
			'controller' => 'users',
			'action' => 'login',
			'plugin' => null));
	}

/**
 * testRestore
 *
 * @access public
 * @return void
 */
	public function testRestore() {
		$data = array(
			'_Token' => 'token',
			'ArticleTest' => array(
				'title' => 'Foo'));
		$this->Controller->Session->write('PreservedForms.ArticlesTest.edit', $data);
		$this->Controller->data = null;
		$this->Controller->FormPreserver->restore();
		$this->assertTrue($this->Controller->Session->check('PreservedForms'));
		$this->assertEqual($this->Controller->data, $data);
		session_destroy();
	}

/**
 * testPreserve
 *
 * @access public
 * @return void
 */
	public function testPreserve() {
		$data = array(
			'_Token' => 'token',
			'ArticleTest' => array(
				'title' => 'Foo'));
		$result = $this->Controller->FormPreserver->preserve($data);
		$this->assertTrue($this->Controller->Session->check('PreservedForms'));
		$this->assertTrue($result);
		session_destroy();
	}

}
