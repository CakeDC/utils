<?php
/**
 * Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('View', 'View');
App::uses('PlaceHelper', 'Utils.View/Helper');
App::uses('HttpSocketResponse', 'Network/Http');

/**
 * Test class for PlaceHelper
 *
 * Allows access to protected methods and set instance of custom class to protected $_Socket property.
 */
class TestPlaceHelper extends PlaceHelper {

	public function cacheKey($amount, $type, $start) {
		return parent::_cacheKey($amount, $type, $start);
	}

	public function decodeResponse($response, &$noCache) {
		return parent::_decodeResponse($response, $noCache);
	}

	public function fetchData($amount, $type, $start, &$noCache) {
		return parent::_fetchData($amount, $type, $start, $noCache);
	}

	public function getData($amount, $type, $start) {
		return parent::_getData($amount, $type, $start);
	}

	public function getType($what) {
		return parent::_getType($what);
	}

	public function imageUrl(&$width, &$height) {
		return parent::_imageUrl($width, $height);
	}

	public function textUrl($amount, $type, $start) {
		return parent::_textUrl($amount, $type, $start);
	}

	public function setSocket(TestPlaceSocket $Socket) {
		$this->_Socket = $Socket;
	}

}

/**
 * Testing socket class
 *
 * Can just throw an exception or return HttpSocketResponse with predefined code and body.
 */
class TestPlaceSocket {

	public $throwException;

	public function __construct($throwException = false) {
		$this->throwException = $throwException;
	}

	public function get($url = null) {
		if ($this->throwException) {
			throw new SocketException('TestPlaceSocket Error');
		}

		$response = new HttpSocketResponse();
		$response->code = 200;
		$response->body = '{"feed":{"lipsum":"ok"}}';
		return $response;
	}

}

/**
 * PlaceHelper Test Case
 *
 * @property TestPlaceHelper $Place
 */
class PlaceHelperTest extends CakeTestCase {

/**
 * Start Test
 *
 * @param string $method
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$View = new View();
		$this->Place = new TestPlaceHelper($View);
		$this->Place->settings['cache'] = false;
	}

/**
 * End Test
 *
 * @param string $method
 * @return void
 */
	public function tearDown() {
		unset($this->Place);
		parent::tearDown();
	}

/**
 * testImage
 *
 * @return void
 */
	public function testImage() {
		$result = $this->Place->image();
		$this->assertTag(array(
			'tag' => 'img',
			'attributes' => array(
				'width' => 300,
				'height' => 200,
				'src' => 'http://placekitten.com/300/200',
				'alt' => '300 x 200',
			)
		), $result);

		$result = $this->Place->image(600, 150, array('class' => 'border', 'alt' => 'Alternative Text'));
		$this->assertTag(array(
			'tag' => 'img',
			'attributes' => array(
				'width' => 600,
				'height' => 150,
				'src' => 'http://placekitten.com/600/150',
				'class' => 'border',
				'alt' => 'Alternative Text',
			)
		), $result);
	}

/**
 * testTextWords
 *
 * @return void
 */
	public function testTextWords() {
		$result = $this->Place->text(11, 'w');
		$this->assertEquals(str_word_count($result), 11);
		$this->assertEquals(strpos($result, 'Lorem ipsum'), 0);

		$result = $this->Place->text(5, 'w', array('start' => false));
		$this->assertEquals(str_word_count($result), 5);
		$this->assertEquals(strpos($result, 'Lorem ipsum'), false);
	}

/**
 * testTextParagraphs
 *
 * @return void
 */
	public function testTextParagraphs() {
		$result = $this->Place->text();
		$this->assertTag(array(
			'tag' => 'p',
		), $result);

		$result = $this->Place->text(2, 'paragraphs', array('class' => 'well'));
		$exploded = explode(PHP_EOL, $result);
		$this->assertEquals(count($exploded), 2);
		$match = array('tag' => 'p', 'attributes' => array('class' => 'well'));
		$this->assertTag($match, $exploded[0]);
		$this->assertTag($match, $exploded[1]);
	}

/**
 * testTextBytes
 *
 * @return void
 */
	public function testTextBytes() {
		$result = $this->Place->text(77, 'b');
		$this->assertEquals(strlen($result), 77);
	}

/**
 * testTextLists
 *
 * @return void
 */
	public function testTextLists() {
		$result = $this->Place->text(3, 'list');
		$this->assertTag(array(
			'tag' => 'ul',
			'children' => array('count' => 3, 'only' => array('tag' => 'li')),
		), $result);

		$result = $this->Place->text(5, 'ol', array('class' => 'list'), array('class' => 'item'));
		$this->assertTag(array(
			'tag' => 'ol',
			'attributes' => array('class' => 'list'),
			'children' => array(
				'count' => 5,
				'only' => array('tag' => 'li', 'attributes' => array('class' => 'item')),
			),
		), $result);
	}

/**
 * testCacheKey
 *
 * @return void
 */
	public function testCacheKey() {
		$result = $this->Place->cacheKey(11, 'x', true);
		$this->assertEquals($result, 'PlaceHelper_x_11_1');

		$result = $this->Place->cacheKey(99, 'y', false);
		$this->assertEquals($result, 'PlaceHelper_y_99_');
	}

/**
 * testDecodeBadResponse
 *
 * @return void
 */
	public function testDecodeBadResponse() {
		$response = new HttpSocketResponse();

		$response->code = 404;
		$result = $this->Place->decodeResponse($response, $noCache);
		$this->assertTrue($noCache);
		$this->assertEquals($result, array(__d('Utils', 'Server lipsum.com returns error code %s.', 404)));
	}

/**
 * testDecodeEmptyOrInvalidResponse
 *
 * @return void
 */
	public function testDecodeEmptyOrInvalidResponse() {
		$response = new HttpSocketResponse();

		$response->code = 200;
		$result = $this->Place->decodeResponse($response, $noCache);
		$this->assertTrue($noCache);
		$this->assertEquals($result, array(__d('Utils', 'Response from lipsum.com is empty or could not be decoded.')));

		$noCache = false;
		$response->body = 'not a json';
		$result = $this->Place->decodeResponse($response, $noCache);
		$this->assertTrue($noCache);
		$this->assertEquals($result, array(__d('Utils', 'Response from lipsum.com is empty or could not be decoded.')));
	}

/**
 * testDecodeValidResponse
 *
 * @return void
 */
	public function testDecodeValidResponse() {
		$response = new HttpSocketResponse();
		$response->code = 200;

		$string = 'single line';
		$response->body = '{"feed":{"lipsum":"' . $string . '"}}';
		$result = $this->Place->decodeResponse($response, $noCache);
		$this->assertNull($noCache);
		$this->assertEquals($result, array($string));

		$expected = array('first line', 'second line', 'third line');
		$string = implode(PHP_EOL, $expected);
		$response->body = '{"feed":{"lipsum":"' . $string . '"}}';
		$result = $this->Place->decodeResponse($response, $noCache);
		$this->assertNull($noCache);
		$this->assertEquals($result, $expected);
	}

/**
 * testFetchDataConnectionFailed
 *
 * @return void
 */
	public function testFetchDataConnectionFailed() {
		$this->Place->setSocket(new TestPlaceSocket(true));
		$result = $this->Place->fetchData(1, 'p', true, $noCache);
		$this->assertTrue($noCache);
		$this->assertEquals($result, array(__d('Utils', 'Connection to server lipsum.com failed. %s.', 'TestPlaceSocket Error')));
	}

/**
 * testFetchData
 *
 * @return void
 */
	public function testFetchData() {
		$this->Place->setSocket(new TestPlaceSocket);
		$result = $this->Place->fetchData(1, 'p', true, $noCache);
		$this->assertFalse($noCache);
		$this->assertEquals($result, array('ok'));
	}

/**
 * testCachedData
 *
 * @return void
 */
	public function testCachedData() {
		$cache = 'test_place_helper';
		$config = Cache::config('default');
		$config['prefix'] = $cache;
		Cache::config($cache, $config);
		$this->Place->settings['cache'] = $cache;

		Cache::clear(false, $cache);
		$expected = array('two words');
		Cache::write('PlaceHelper_w_2_', $expected, $cache);
		$result = $this->Place->getData(2, 'w', false);
		$this->assertEquals($result, $expected);

		Cache::clear(false, $cache);
		$result = $this->Place->getData(8, 'w', true);
		$expected = Cache::read('PlaceHelper_w_8_1', $cache);
		$this->assertEquals($result, $expected);
	}

/**
 * testGetType
 *
 * @param string $what
 * @param string $expected
 * @dataProvider typeProvider
 * @return void
 */
	public function testGetType($what, $expected) {
		$actual = $this->Place->getType($what);
		$this->assertEquals($expected, $actual);
	}

/**
 * testImageUrl
 *
 * @return void
 */
	public function testImageUrl() {
		$width = $height = 1;
		$result = $this->Place->imageUrl($width, $height);
		$this->assertEquals($result, 'http://placekitten.com/1/1');

		$width = $height = null;
		$result = $this->Place->imageUrl($width, $height);
		$this->assertEquals($result, 'http://placekitten.com/300/200');
	}

/**
 * testTextUrl
 *
 * @return void
 */
	public function testTextUrl() {
		$result = $this->Place->textUrl(99, 'w', true);
		$this->assertEquals($result, 'http://www.lipsum.com/feed/json?amount=99&what=words&start=yes');

		$result = $this->Place->textUrl(5, 'l', false);
		$this->assertEquals($result, 'http://www.lipsum.com/feed/json?amount=5&what=lists&start=no');
	}

/**
 * Data provider for testGetType()
 *
 * @return array
 */
	public function typeProvider() {
		return array(
			array('w', 'w'),
			array('word', 'w'),
			array('words', 'w'),
			array('p', 'p'),
			array('para', 'p'),
			array('paras', 'p'),
			array('paragraph', 'p'),
			array('paragraphs', 'p'),
			array('b', 'b'),
			array('byte', 'b'),
			array('bytes', 'b'),
			array('l', 'l'),
			array('list', 'l'),
			array('lists', 'l'),
			array('ul', 'l'),
			array('ol', 'l'),
			array('default', 'l'),
		);
	}

}
