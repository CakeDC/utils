<?php
App::uses('Controller', 'Controller');
App::uses('AuthComponent', 'Controller/Component');
App::uses('SessionComponent', 'Controller/Component');
App::uses('FormPreserverComponent', 'Utils.Controller/Component');

class FormPreserverArticle extends CakeTestModel {
/**
 * 
 */
	public $name = 'Article';
}

class FormPreserverArticlesTestController extends Controller {

/**
 * @var string
 * @access public
 */
	public $name = 'ArticlesTest';

/**
 * @var array
 * @access public
 */
	public $uses = array('FormPreserverArticle');

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
	function setUp() {
		$request = new CakeRequest(null, false);
		$this->Controller = new FormPreserverArticlesTestController($request, $this->getMock('CakeResponse'));
		$this->Controller->constructClasses();
		$this->Controller->action = 'edit';
		$this->Controller->request->params = array(
			'named' => array(),
			'pass' => array(),
			'url' => array());
		$this->Controller->modelClass = 'Article';
		$this->Controller->FormPreserver = new FormPreserverComponent($this->Controller->Components);
		$this->Controller->FormPreserver->initialize($this->Controller);
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	function tearDown() {
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

		$this->Controller->action = 'edit';
		$this->Controller->request->data = $data;
		$this->Controller->FormPreserver->Controller = $this->Controller;
		$this->Controller->FormPreserver->Session = $this->Controller->Session;
		$this->Controller->FormPreserver->actions = array('edit');
		$this->Controller->FormPreserver->startup($this->Controller);

		$this->assertEquals($this->Controller->redirectUrl, array(
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
		$this->Controller->action = 'edit';
		$this->Controller->FormPreserver->initialize($this->Controller);
		$this->Controller->Session->write('PreservedForms.ArticlesTest.edit', $data);
		$this->Controller->request->data = array();
		$this->Controller->FormPreserver->restore();
		$this->assertFalse($this->Controller->Session->check('PreservedForms'));
		$this->assertEquals($this->Controller->request->data, $data);
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

		$this->Controller->action = 'edit';
		$this->Controller->FormPreserver->initialize($this->Controller);
		$this->assertTrue($this->Controller->FormPreserver->preserve($data));
		$this->assertTrue($this->Controller->Session->check('PreservedForms'));
		session_destroy();
	}

}
