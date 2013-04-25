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

App::uses('AppHelper', 'View/Helper');
App::uses('HttpSocket', 'Network/Http');

/**
 * Helper for image and text placeholders
 *
 * @property HtmlHelper $Html
 * @package Utils
 * @subpackage Utils.View.Helper
 */
class PlaceHelper extends AppHelper {

/**
 * Used helpers
 *
 * @var array
 */
	public $helpers = array('Html');

/**
 * Settings, configurable in controller or from view/layout/element
 *
 * cache - name of cache engine used for caching results of requests to lipsum.com, or false to disable caching
 * imageDimensions - array with default width and height for image placeholders
 *
 * @var array
 */
	public $settings = array(
		'cache' => 'default',
		'imageDimensions' => array(300, 200),
	);

/**
 * Array map with URLs of services used for getting image and text placeholders
 *
 * @var array
 */
	protected $_urls = array(
		'image' => 'http://placekitten.com/%s/%s',
		'text' => 'http://www.lipsum.com/feed/json?amount=%s&what=%s&start=%s',
	);

/**
 * Array map with allowed keywords used by lipsum.com service
 *
 * @var array
 */
	protected $_lipsumTypes = array(
		'w' => 'words',
		'p' => 'paras',
		'l' => 'lists',
		'b' => 'bytes',
	);

/**
 * Instance of HttpSocket for accessing lipsum.com
 *
 * @var HttpSocket
 */
	protected $_Socket;

/**
 * Returns <img> tag with placeholder image from placekitten.com.
 *
 * Default width and height is configurable in settings['imageDimensions'] array.
 *
 * @param integer $width image width
 * @param integer $height image height
 * @param array $options options for HtmlHelper::image()
 * @return string
 */
	public function image($width = null, $height = null, $options = array()) {
		$url = $this->_imageUrl($width, $height);
		$options = array_merge($options, compact('width', 'height'));
		$options['alt'] = isset($options['alt']) ? $options['alt'] : "$width x $height";
		return $this->Html->image($url, $options);
	}

/**
 * Returns 'Lorem Ipsum' text placeholder
 *
 * ### Options
 *
 * - `start` If set to false, the generated string will not start with words
 * 'Lorem ipsum dolor sit amet...'. Defaults to true.
 *
 * @param integer $amount amount of units (see $what) to return
 * @param string $what allowed identifier - must start with (case insensitive) letter
 *   used as key in PlaceHelper::$_lipsumTypes map (except for <ol>). Examples:
 *   p, para, paragraph, paragraphs - returns requested $amount of <p> tags with text
 *   w, word, words - returns requested $amount of words (not wrapped in any html tag)
 *   b, byte, bytes - returns text with $amount of bytes (not wrapped in any html tag)
 *   l, list - returns unordered list with $amount of items
 *   ol - returns ordered list with $amount of items
 *   Unknown identifiers (first characters of identifier) defaults to 'l' (unordered list).
 * @param array $options options for HtmlHelper::para() or HtmlHelper::nestedList()
 * @param array $itemOptions itemOptions for HtmlHelper::nestedList()
 * @return string
 */
	public function text($amount = 1, $what = 'p', $options = array(), $itemOptions = array()) {
		$type = $this->_getType($what);
		$start = true;
		if (isset($options['start'])) {
			$start = (bool)$options['start'];
			unset($options['start']);
		}
		$content = $this->_getData($amount, $type, $start);

		switch ($type) {
			case 'l':
				return $this->Html->nestedList($content, $options, $itemOptions, ($what == 'ol') ? 'ol' : 'ul');
			case 'p':
				foreach ($content as $key => $line) {
					$content[$key] = $this->Html->para(null, $line, $options);
				}
				return implode(PHP_EOL, $content);
		}

		return array_shift($content);
	}

/**
 * Returns unique key for placeholder text caching
 *
 * @param integer $amount amount of units to return
 * @param string $type single character, key of PlaceHelper::$_lipsumTypes
 * @param boolean $start decides if the generated string should start with words
 *   'Lorem ipsum dolor sit amet...'
 * @return string
 */
	protected function _cacheKey($amount, $type, $start) {
		$name = __CLASS__;
		$start = (string)$start;
		return implode('_', compact('name', 'type', 'amount', 'start'));
	}

/**
 * Decode json response from lipsum.com
 *
 * @param HttpResponse $response response from lipsum.com
 * @param boolean $noCache disables caching in runtime
 * @return array
 */
	protected function _decodeResponse($response, &$noCache) {
		if (!$response->isOk()) {
			$noCache = true;
			return array(__d('Utils', 'Server lipsum.com returns error code %s.', $response->code));
		}

		if (!empty($response->body)) {
			$eol = '__EOL__';
			$result = json_decode(str_replace(PHP_EOL, $eol, $response->body));
			if (!empty($result->feed->lipsum)) {
				return explode($eol, $result->feed->lipsum);
			}
		}

		$noCache = true;
		return array(__d('Utils', 'Response from lipsum.com is empty or could not be decoded.'));
	}

/**
 * Fetches data from lipsum.com and returns them as an array
 *
 * @param integer $amount amount of units to return
 * @param string $type single character, key of PlaceHelper::$_lipsumTypes
 * @param boolean $start decides if the generated string should start with words
 *   'Lorem ipsum dolor sit amet...'
 * @param boolean $noCache disables caching in runtime
 * @return array
 */
	protected function _fetchData($amount, $type, $start, &$noCache) {
		if (empty($this->_Socket)) {
			$this->_Socket = new HttpSocket();
		}

		$noCache = false;
		$url = $this->_textUrl($amount, $type, $start);

		try {
			$response = $this->_Socket->get($url);
			return $this->_decodeResponse($response, $noCache);
		} catch (SocketException $e) {
			$noCache = true;
			return array(__d('Utils', 'Connection to server lipsum.com failed. %s.', $e->getMessage()));
		}
	}

/**
 * Returns cached or fresh data for text placeholder
 *
 * @param integer $amount amount of units to return
 * @param string $type single character, key of PlaceHelper::$_lipsumTypes
 * @param boolean $start decides if the generated string should start with words
 *   'Lorem ipsum dolor sit amet...'
 * @return array
 */
	protected function _getData($amount, $type, $start) {
		$cache = $this->settings['cache'];
		if (empty($cache)) {
			return $this->_fetchData($amount, $type, $start, $noCache);
		}

		$cacheKey = $this->_cacheKey($amount, $type, $start);
		$result = Cache::read($cacheKey, $cache);

		if (empty($result)) {
			$result = $this->_fetchData($amount, $type, $start, $noCache);
			if (!$noCache) {
				Cache::write($cacheKey, $result, $cache);
			}
		}

		return $result;
	}

/**
 * Maps text placeholder identifiers to single character.
 *
 * @param string $what allowed identifier
 * @return string
 */
	protected function _getType($what) {
		$key = strtolower(substr($what, 0, 1));
		return isset($this->_lipsumTypes[$key]) ? $key : 'l';
	}

/**
 * Returns url for image placeholder service
 *
 * @param integer $width image width
 * @param integer $height image height
 * @return string
 */
	protected function _imageUrl(&$width, &$height) {
		foreach (array('width', 'height') as $key => $var) {
			${$var} = empty(${$var}) ? $this->settings['imageDimensions'][$key] : ${$var};
		}
		return sprintf($this->_urls['image'], $width, $height);
	}

/**
 * Returns url for text placeholder service
 *
 * @param integer $amount amount of units to return
 * @param string $type single character, key of PlaceHelper::$_lipsumTypes
 * @param boolean $start decides if the generated string should start with words
 *   'Lorem ipsum dolor sit amet...'
 * @return string
 */
	protected function _textUrl($amount, $type, $start) {
		return sprintf($this->_urls['text'], $amount, $this->_lipsumTypes[$type], ($start ? 'yes' : 'no'));
	}

}
