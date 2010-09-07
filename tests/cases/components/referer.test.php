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


class PrgComponentTest extends CakeTestCase {
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
 * testSetReferer
 *
 * @access public
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

		$this->Controller->data['Data']['referer'] = '/post';
		$this->Controller->Referer->setReferer('/foo/bar2');
		$this->assertEqual($this->Controller->viewVars['referer'], '/post');
	}

/**
 * testRedirect
 *
 * @access public
 * @return void
 */
	public function testRedirect() {
		$this->Controller->data['Data']['referer'] = '/foo/bar';
		$result = $this->Controller->Referer->redirect('/home');
		$this->assertEqual($this->Controller->redirectUrl, '/foo/bar');

		$_SERVER['HTTP_REFERER'] = '/';
		$this->Controller->data = null;
		$result = $this->Controller->Referer->redirect('/home');
		$this->assertEqual($this->Controller->redirectUrl, '/home');
	}

}
?>