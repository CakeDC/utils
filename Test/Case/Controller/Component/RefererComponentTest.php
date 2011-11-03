<?php
App::import('Controller', 'Controller', false);
App::import('Component', 'Utils.Referer');

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
		$this->Controller = new ArticlesTestController($request, $response);
		$this->Controller->modelClass = 'Article';
		$this->Controller->Components->init($this->Controller);
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
		$this->assertEqual($this->Controller->viewVars['referer'], '/bar');

		$_SERVER['HTTP_REFERER'] = '/';
		$this->Controller->Referer->setReferer('/foo/bar2');
		$this->assertEqual($this->Controller->viewVars['referer'], '/foo/bar2');

		$_SERVER['HTTP_REFERER'] = '/';
		$this->Controller->Referer->setReferer(array('controller' => 'foo', 'action' => 'bar'));
		$this->assertEqual($this->Controller->viewVars['referer'], '/foo/bar');

		$this->Controller->request->data['Data']['referer'] = '/post';
		$this->Controller->Referer->setReferer('/foo/bar2');
		$this->assertEqual($this->Controller->viewVars['referer'], '/post');
	}

/**
 * testRedirect
 *
 * @return void
 */
	public function testRedirect() {
		$this->Controller->request->data['Data']['referer'] = '/foo/bar';
		$result = $this->Controller->Referer->redirect('/home');
		$this->assertEqual($this->Controller->redirectUrl, '/foo/bar');

		$_SERVER['HTTP_REFERER'] = '/';
		$this->Controller->request->data = null;
		$result = $this->Controller->Referer->redirect('/home');
		$this->assertEqual($this->Controller->redirectUrl, '/home');
	}

}
