<?php
App::uses('Controller', 'Controller');
App::uses('AuthComponent', 'Controller/Component');
App::uses('SessionComponent', 'Controller/Component');
App::uses('FormPreserverComponent', 'Utils.Controller/Component');
App::uses('ComponentCollection', 'Controller');

class FormPreserverArticle extends CakeTestModel {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Article';
}

class FormPreserverArticlesTestController extends Controller {

/**
 * @var string
 */
	public $name = 'ArticlesTest';

/**
 * @var array
 */
	public $uses = array(
		'FormPreserverArticle'
	);

/**
 * @var array
 */
	public $components = array(
		'Utils.FormPreserver',
		'Session',
		'Auth'
	);

/**
 * Redirect
 *
 * @param string $url
 * @param integer $status
 * @param boolean $exit
 * @return void
 */
	public function redirect($url, $status = null, $exit = true) {
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
		'plugin.utils.article'
	);

/**
 * setUp method
 *
 * @access public
 * @return void
 */
	public function setUp() {
		$this->Session = $this->getMockBuilder('SessionComponent')
			->disableOriginalConstructor()
			->getMock();

		$this->Auth = $this->getMockBuilder('AuthComponent')
			->disableOriginalConstructor()
			->getMock();

		$this->Collection = $this->getMockBuilder('ComponentCollection')
			->disableOriginalConstructor()
			->getMock();

		$this->Collection->Auth = $this->Auth;
		$this->Collection->Session = $this->Session;

		$request = new CakeRequest(null, false);
		$this->Controller = new FormPreserverArticlesTestController($request, $this->getMock('CakeResponse'));
		$this->Controller->action = 'edit';
		$this->Controller->request->params = array(
			'named' => array(),
			'pass' => array(),
			'url' => array());
		$this->Controller->name = 'Articles';
		$this->Controller->modelClass = 'Article';
		$this->Controller->Session = $this->Session;
		$this->Controller->FormPreserver = new FormPreserverComponent($this->Collection);
		$this->Controller->FormPreserver->initialize($this->Controller);
		$this->Controller->FormPreserver->Session = $this->Session;
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	public function tearDown() {
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
				'title' => 'Foo'
			)
		);

		$this->Controller->action = 'edit';
		$this->Controller->request->data = $data;
		$this->Controller->FormPreserver->Controller = $this->Controller;
		$this->Controller->FormPreserver->Session = $this->Controller->Session;
		$this->Controller->FormPreserver->actions = array('edit');
		$this->Controller->FormPreserver->startup($this->Controller);

		$this->assertEquals($this->Controller->redirectUrl, array(
			'controller' => 'users',
			'action' => 'login',
			'plugin' => null
		));
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
				'title' => 'Foo'
			)
		);
		$this->Controller->action = 'edit';
		$this->Controller->FormPreserver->initialize($this->Controller);
		$this->Controller->Session->write('PreservedForms.ArticlesTest.edit', $data);
		$this->Controller->request->data = array();
		$this->Controller->FormPreserver->restore();

		$this->Session->expects($this->once())
			->method('check')
			->with('PreservedForms.ArticlesTest.edit')
			->will($this->returnValue(true));

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
				'title' => 'Foo'
			)
		);

		$this->Controller->action = 'edit';
		$this->Controller->FormPreserver->initialize($this->Controller);
		$expectedDataForSession = Hash::remove($data, '_Token');
		$this->Session->expects($this->once())
			->method('write')
			->with('PreservedForms.Articles.edit', $expectedDataForSession)
			->will($this->returnValue(true));

		$this->assertTrue($this->Controller->FormPreserver->preserve($data));
	}
}
