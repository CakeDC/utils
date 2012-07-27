<?php
App::uses('Controller', 'Controller');
App::uses('PrgComponent', 'Utils.Controller/Component');


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
	public $components = array('Utils.Archive');

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
	public function startTest() {
		$this->Controller = new ArticlesTestController();
		$this->Controller->constructClasses();
		$this->Controller->modelClass = 'Article';
	}

/**
 * tearDown method
 *
 * @access public
 * @return void
 */
	public function endTest() {
		unset($this->Controller);
		ClassRegistry::flush();
	}

	public function testPrg() {
	}
	
}

