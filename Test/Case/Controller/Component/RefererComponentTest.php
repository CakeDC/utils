<?php
App::uses('Controller', 'Controller');
App::uses('RefererComponent', 'Utils.Controller/Component');

class RefererComponentArticle extends CakeTestModel {
/**
 * 
 */
	public $name = 'Article';
}

class RefererComponentArticlesTestController extends Controller {

/**
 * @var string
 * @access public
 */
	public $name = 'ArticlesTest';

/**
 * @var array
 * @access public
 */
	public $uses = array('RefererComponentArticle');

/**
 * @var array
 * @access public
 */
	public $components = array('Utils.Referer');

/**
 * 
 */
	public function redirect($url, $status = NULL, $exit = true) {
		$this->redirectUrl = $url;
	}

}


class RefererComponentTest extends CakeTestCase {
/**
 * Controller object instance
 *
 * @var Controller
 */
	public $Controller;

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
		'plugin.utils.article');

/**
 * setUp method
 *
 * @return void
 */
	function setUp() {
		$request = new CakeRequest('controller_posts/index');
		$response = new CakeResponse(); 
		$this->Controller = new RefererComponentArticlesTestController($request, $response);
		$this->Controller->modelClass = 'Article';
		$this->Controller->Referer = new RefererComponent($this->Controller->Components);
		$this->Controller->Referer->initialize($this->Controller, array());


		ClassRegistry::addObject('view', new View($this->Controller));  		
	}

/**
 * tearDown method
 *
 * @return void
 */
	function tearDown() {
		unset($this->Controller);
		ClassRegistry::flush();
	}

/**
 * testSetReferer
 *
 * @return void
 */
	public function testSetReferer() {
		$_SERVER['HTTP_REFERER'] = '/bar';
		$this->Controller->Referer->setReferer('/foo/bar');
		$this->assertEquals($this->Controller->viewVars['referer'], '/bar');

		$_SERVER['HTTP_REFERER'] = '/';
		$this->Controller->Referer->setReferer('/foo/bar2');
		$this->assertEquals($this->Controller->viewVars['referer'], '/foo/bar2');

		$_SERVER['HTTP_REFERER'] = '/';
		$this->Controller->Referer->setReferer(array('controller' => 'foo', 'action' => 'bar'));
		$this->assertEquals($this->Controller->viewVars['referer'], '/foo/bar');

		$this->Controller->request->data['Data']['referer'] = '/post';
		$this->Controller->Referer->setReferer('/foo/bar2');
		$this->assertEquals($this->Controller->viewVars['referer'], '/post');
	}

/**
 * testRedirect
 *
 * @return void
 */
	public function testRedirect() {
		$this->Controller->request->data['Data']['referer'] = '/foo/bar';
		$result = $this->Controller->Referer->redirect('/home');
		$this->assertEquals($this->Controller->redirectUrl, '/foo/bar');

		$_SERVER['HTTP_REFERER'] = '/';
		$this->Controller->request->data = null;
		$result = $this->Controller->Referer->redirect('/home');
		$this->assertEquals($this->Controller->redirectUrl, '/home');
	}

}
