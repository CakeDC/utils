<?php
/**
 * Copyright 2007-2010, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Utils Plugin
 *
 * Utils Pingbacks Component
 *
 * @package utils
 * @subpackage utils.controllers.components
 */
App::import('Core', 'HttpSocket');
App::import('Lib', 'Xmlrpc.Xmlrpc');

class PingbacksComponent extends Object {
/**
 * Components that are required
 *
 * @var array $components
 */
	public $components = array('Session');

/**
 * Socket to access webservice
 *
 * @var Socket $Socket
 */
	private $Socket;

/**
 * Controller
 *
 * @var mixed $controller
 */
	public $controller = null;

/**
 * Callback
 *
 * @param object Controller object
 */
	public function initialize(&$controller) {
		$this->Socket = new HttpSocket();
		$this->controller = $controller;
	}

/**
 * Scans the input text, pings every linked website for auto-discovery-capable pingback
 * servers, and does an appropriate pingback.
 *
 * @link http://www.hixie.ch/specs/pingback/pingback
 */
	public function pingback($sourceUri, $text) {
		$links = $this->extractLinks($text, false);
		foreach ($links as $link) {
			$this->pingbackUrl($sourceUri, $link);
		}
	}

/**
 * Attempts to notify the server of targetUri that sourceUri refers to it.
 *
 * @param object Controller object
 */
	public function pingbackUrl($sourceUri, $targetUri) {
		$urlData = $this->Socket->get($targetUri);
		$pingbackServerUri = $this->_determinePingbackUri($this->Socket->response);
		if(!empty($pingbackServerUri)) {
			$XmlRpcClient = new XmlRpcClient($pingbackServerUri);
			try {
				$Request = new XmlRpcRequest('pingback.ping', array(new XmlRpcValue($sourceUri), new XmlRpcValue($targetUri)));
				$result = $XmlRpcClient->call($Request);
			} catch (Exception $e) {
				// pingback server return error. we will ignore it
			}
		}
	}

/**
 * Scans the input text, pings every linked website for auto-discovery-capable pingback
 * servers, and does an appropriate trackback.
 *
 */
	public function trackback($sourceUri, $text) {
		$links = $this->extractLinks($text, false);
		foreach ($links as $link) {
			$this->trackbackUrl($sourceUri, $link);
		}
	}

/**
 * Attempts to notify the server of targetUri that sourceUri refers to it.
 *
 * @param object Controller object
 */
	public function trackbackUrl($sourceUri, $targetUri) {
		$urlData = $this->Socket->get($targetUri);
		$trackbackServerUri = $this->_determineTrackbackUri($this->Socket->response);
		if(!empty($trackbackServerUri)) {
			$response = $this->Socket->post($targetUri, $data);
		}
	}

/**
 * Extracts all the hyperlinks from the entry text.
 *
 * @param string $text
 * @param boolean $allowLocalLinks
 * @return array of hyperlink in data
 */
	public function extractLinks($text, $allowLocalLinks = false) {
		$matches = array();
		$result = preg_match_all('/href="([^"]+)"/i', $text, $matches);
		if (isset($matches[1])) {
			$urls = $matches[1];
		} else {
			$urls = array();
		}
		$result = array();
		foreach ($urls as $url) {
			if ($allowLocalLinks) {
				$result[] = $url;
			} else {
				$urlInfo = HttpSocket::parseUri($url);
				$serverHost = '';
				if (isset($urlInfo['host']) && isset($urlInfo['scheme'])) {
					$schemeLen = strlen($urlInfo['scheme']) + 3;
					$serverHost = $urlInfo['scheme'] . '://' . $urlInfo['host'];
					if (strpos(substr(FULL_BASE_URL, $schemeLen), ':') !== false) {
						$serverHost .= ':' . $urlInfo['port'];
					}
				}
				if ($serverHost != FULL_BASE_URL) {
					$result[] = $url;
				}
			}
		}
		return $result;
	}

/**
 * Process html response to find trackback link
 *
 * @param Xml $xml
 */
	public function _determineTrackbackUri($text) {
		$result = false;
		preg_match_all('|(\<rdf\:RDF.+?\<\/rdf\:RDF\>)|is', $text, $matches);
		if (isset($matches[1])) {
			foreach ($matches[1] as $match) {
				$Xml = new Xml($match);
				$result = $this->_scanRdf($Xml);
				if (!empty($result)) {
					return $result;
				}
			}
		}
		if (empty($result)) {
			$result = $this->_testRelLink($text, 'trackback');
		}
		if (empty($result)) {
			$result = $this->_testTagClass($text, 'span', 'trackbacks-link');
		}
		return $result;
	}

/**
 * Scan Xml document to fetch trackback link
 *
 * @param Xml $xml
 */
	protected function _scanRdf(Xml $xml) {
		if (count($xml->children) > 0) {
			$rootNode = $xml->children[0];
			if ($rootNode->namespace . ':' . $rootNode->name != 'rdf:RDF' || !isset($rootNode->namespaces['trackback'])) {
				return false;
			}
			foreach ($rootNode->children as $child) {
				if ($child->namespace . ':' . $child->name == 'rdf:Description' && isset($child->attributes['trackback:ping'])) {
					return $child->attributes['trackback:ping'];
				}
			}
		}
		return false;
	}

/**
 * Attempt to determine the pingback Uri of the given url.
 * we try to find the X-Pingback server header
 * and then we looking for rel link
 *
 */
	protected function _determinePingbackUri($response) {
		foreach ($response['header'] as $header => $value) {
			if (strtolower($header) == 'x-pingback') {
				return $value;
			}
		}
		return $this-> _testRelLink($response['body'], 'pingback');
	}

/**
 * Search rel link in html page
 *
 * @param string $text
 * @param string $relType pingback, trackback or some other
 */
	protected function _testRelLink($text, $relType) {
		if (preg_match('|<link rel="' . $relType . '" href="([^"]+)" ?/?>|', $text, $matches)) {
			return str_replace(array('&amp;', '&lt;', '&gt;', '&quot;'), array('&', '<', '>', '"'), $matches[1]);
		} else {
			return false;
		}
	}

/**
 * Search tag with class in html page
 *
 * @param string $text
 * @param string $tag
 * @param string $class
 */
	protected function _testTagClass($text, $tag, $class) {
		if (preg_match('|<' . $tag . '.*? class="' . $class . '".*?>([^<]+?)</' . $tag . '>|', $text, $matches)) {
			return str_replace(array('&amp;', '&lt;', '&gt;', '&quot;'), array('&', '<', '>', '"'), $matches[1]);
		} else {
			return false;
		}
	}
}
