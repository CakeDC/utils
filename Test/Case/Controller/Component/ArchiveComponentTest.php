<?php
App::uses('Controller', 'Controller');
App::uses('ArchiveComponent', 'Utils.Controller/Component');

class RefererArticlesTestController extends Controller {

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

class RefererTestArticle extends Model {

/**
 * Name
 *
 * @var string
 */
	public $name = 'Article';
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
		$request = new CakeRequest(null, false);
		$this->Controller = new RefererArticlesTestController($request, $this->getMock('CakeResponse'));
		$this->Controller->Article = $this->getMockForModel('Article');
		$this->Controller->constructClasses();
		$this->Controller->params = array(
			'named' => array(),
			'pass' => array(),
			'url' => array());
		$this->Controller->modelClass = 'Article';
		$this->Controller->Archive = new ArchiveComponent;
		$this->Controller->Archive->startup($this->Controller);
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
 * testArchiveLinks
 *
 * @access public
 * @return void
 */
	public function testArchiveLinks() {
		$result = $this->Controller->Archive->archiveLinks();
		$this->assertEqual($result[0], array(
			'year' => 2007,
			'month' => 03,
			'count' => 3));
	}

}