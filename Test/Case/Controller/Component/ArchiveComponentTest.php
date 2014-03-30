<?php
App::uses('Controller', 'Controller');
App::uses('ArchiveComponent', 'Utils.Controller/Component');


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


class ArchiveComponentTest extends CakeTestCase {

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
		$this->Controller = new ArticlesTestController();
		$this->Controller->constructClasses();
		$this->Controller->params = array(
			'named' => array(),
			'pass' => array(),
			'url' => array());
		$this->Controller->modelClass = 'Article';
		$this->Controller->Archive = new ArchiveComponent($this->Controller->Components);
		$this->Controller->Archive->startup($this->Controller);
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
 * testArchiveLinks
 *
 * @access public
 * @return void
 */
	public function testArchiveLinks() {
		$result = $this->Controller->Archive->archiveLinks();
		$this->assertEquals($result[0], array(
			'year' => 2007,
			'month' => 03,
			'count' => 3));
	}

}
