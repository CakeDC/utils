<?php
/**
 * CakePHP Gravatar Helper Test
 *
 * @copyright Copyright 2010, Graham Weldon
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @package goodies
 * @subpackage goodies.tests.cases.helpers
 *
 */
App::import('Helper', array('Html', 'Utils.Gravatar'));
App::import('Core', 'View', false);

/**
 * GravatarHelper Test
 *
 * @package goodies
 * @subpackage goodies.test.cases.views.helpers
 */
class GravatarHelperTest extends CakeTestCase {

/**
 * Gravatar helper
 *
 * @var GravatarHelper
 * @access public
 */
	public $Gravatar = null;

/**
 * Start Test
 *
 * @return void
 * @access public
 */
	public function setUp() {
		$null = null;
		$this->View = new View($null);
		$this->Gravatar = new GravatarHelper($this->View);
		$this->Gravatar->Html = new HtmlHelper($this->View);
	}

/**
 * End Test
 *
 * @return void
 * @access public
 */
	public function tearDown() {
		unset($this->Gravatar);
	}

/**
 * testBaseUrlGeneration
 *
 * @return void
 * @access public
 */
	public function testBaseUrlGeneration() {
		$expected = 'http://www.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5');
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false, 'default' => 'wavatar'));
		list($url, $params) = explode('?', $result);
		$this->assertEqual($expected, $url);
	}

/**
 * testExtensions
 *
 * @return void
 * @access public
 */
	public function testExtensions() {
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => true, 'default' => 'wavatar'));
		$this->assertPattern('/\.jpg(?:$|\?)/', $result);
	}

/**
 * testRating
 *
 * @return void
 * @access public
 */
	public function testRating() {
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => true, 'default' => 'wavatar'));
		$this->assertPattern('/\.jpg(?:$|\?)/', $result);
	}

/**
 * testAlternateDefaultIcon
 *
 * @return void
 * @access public
 */
	public function testAlternateDefaultIcon() {
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false, 'default' => 'wavatar'));
		list($url, $params) = explode('?', $result);
		$this->assertPattern('/default=wavatar/', $params);
	}

/**
 * testAlternateDefaultIconCorrection
 *
 * @return void
 * @access public
 */
	public function testAlternateDefaultIconCorrection() {
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false, 'default' => '12345'));
		$this->assertPattern('/[^\?]+/', $result);
	}

/**
 * testSize
 *
 * @return void
 * @access public
 */
	public function testSize() {
		$result = $this->Gravatar->url('example@gravatar.com', array('size' => '120'));
		list($url, $params) = explode('?', $result);
		$this->assertPattern('/size=120/', $params);
	}

/**
 * testImageTag
 *
 * @return void
 * @access public
 */
	public function testImageTag() {
		$expected = '<img src="http://www.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5') . '" alt="" />';
		$result = $this->Gravatar->image('example@gravatar.com', array('ext' => false));
		$this->assertEqual($expected, $result);

		$expected = '<img src="http://www.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5') . '" alt="Gravatar" />';
		$result = $this->Gravatar->image('example@gravatar.com', array('ext' => false, 'alt' => 'Gravatar'));
		$this->assertEqual($expected, $result);
	}

/**
 * testDefaulting
 *
 * @return void
 * @access public
 */
	public function testDefaulting() {
		$result = $this->Gravatar->url('example@gravatar.com', array('default' => 'wavatar', 'size' => 'default'));
		list($url, $params) = explode('?', $result);
		$this->assertEqual($params, 'default=wavatar');
	}

/**
 * testNonSecureUrl
 *
 * @return void
 * @access public
 */
	public function testNonSecureUrl() {
		$_SERVER['HTTPS'] = false;
		
		$expected = 'http://www.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5');
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false));
		$this->assertEqual($expected, $result);

		$expected = 'http://www.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5');
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false, 'secure' => false));
		$this->assertEqual($expected, $result);

		$_SERVER['HTTPS'] = true;
		$expected = 'http://www.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5');
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false, 'secure' => false));
		$this->assertEqual($expected, $result);
	}

/**
 * testSecureUrl
 *
 * @return void
 * @access public
 */
	public function testSecureUrl() {
		$expected = 'https://secure.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5');
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false, 'secure' => true));
		$this->assertEqual($expected, $result);

		$_SERVER['HTTPS'] = true;
		
		$expected = 'https://secure.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5');
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false));
		$this->assertEqual($expected, $result);

		$expected = 'https://secure.gravatar.com/avatar/' . Security::hash('example@gravatar.com', 'md5');
		$result = $this->Gravatar->url('example@gravatar.com', array('ext' => false, 'secure' => true));
		$this->assertEqual($expected, $result);
	}
}