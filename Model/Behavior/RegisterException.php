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

App::import('Core', 'HttpSocket');
App::import('Core', 'Xml');
App::import('Lib', 'Xmlrpc.Xmlrpc');

/**
 * Utils Plugin
 *
 * Utils Pingbackable Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
class RegisterException extends Exception {
	public $messageString;
	function __construct($message) {
		$this->messageString = $message;
	}
}

class PingbackableBehavior extends ModelBehavior {

/**
 * Settings array
 *
 * @var array
 */
	public $settings = array();

/**
 * Default settings
 *
 * @var array
 */
	public $defaults = array(
		'commentAlias' => 'Comment',
		'requireApproveModelField' => 'moderate',
		'requireApproveCommentField' => 'approved');

/**
 * Setup
 *
 * @param AppModel $model
 * @param array $settings
 */
	public function setup(&$model, $settings = array()) {
		if (!isset($this->settings[$model->alias])) {
			$this->settings[$model->alias] = $this->defaults;
		}
		$this->settings[$model->alias] = am($this->settings[$model->alias], !empty(is_array($settings)) ? $settings : array());
	}

/**
 * Register pingback comment. Used by pingback server
 *
 * @param AppModel $model
 * @param string $modelId
 * @param array $options
 * @param string $sourceUri
 * @param string $targetUri
 * @return boolean
 */
	public function pingbackRegisterComment(&$model, $modelId, $sourceUri, $targetUri) {
		extract($this->settings[$model->alias]);
		if ($model->{$commentAlias}->hasAny(array($commentAlias . '.foreign_key' => $modelId, 'author_url' => $sourceUri))) {
			throw new XmlRpcResponseException(0, 'Pingback already registries in system.');
		}
		$sourceBody = $this->loadPageContent($sourceUri);
		if (strpos($sourceBody, $targetUri) === false) {
			throw new XmlRpcResponseException(0, 'Source link is not detected in target blog.');
		}
		$sourceBody = $this->cleanupPage($sourceBody);
		$title = $this->fetchTitle($sourceBody);
		$cite = $this->fetchPingbackCite($sourceBody, $sourceUri, $targetUri);
		$isSpam = false;
		$data = array(
			'comment_type' => 'pingback',
			'author_name' => $title . 'blog',
			'author_url' => $sourceUri,
			'title' => $title,
			'foreign_key' => $modelId,
			'model' => $model->alias,
			'body' => $cite);
		if ($model->{$commentAlias}->Behaviors->enabled('Antispamable')) {
			$isSpam = $model->isSpam();
		}
		$data['is_spam'] = $isSpam ? 'spam' : 'clean';
		$modelData = $model->find('first', array('conditions' => array('id' => $modelId), 'recursive' => -1));
		if (!empty($modelData[$model->alias][$requireApproveModelField])) {
			$data[$requireApproveCommentField] = 0;
		}
		$model->{$commentAlias}->create($data);
		return $model->{$commentAlias}->save();
	}

/**
 * return content of required page
 *
 * @param string $uri
 * @return string
 */
	protected function loadPageContent($uri) {
		$Http = new HttpSocket();
		return $Http->get($uri);
	}

/**
 * Cleanup some markup for page
 *
 * @param string $text
 * @return string
 */
	protected function cleanupPage($text) {
		return preg_replace("/ <(h[1-6]|p|t[hd]]|li|d[td]|pre|caption|body)[^>]*>/", "\n\n", preg_replace( '/[\s\r\n\t]+/', ' ', str_replace('<!DOC', '<DOC', $text)));
	}

/**
 * Extract page title
 *
 * @param string $text
 * @return string
 */
	protected function fetchTitle($text) {
		preg_match('|<title>([^<]*?)</title>|is', $text, $matchedTitle);
		$title = $matchedTitle[1];
		if (empty($title)) {
			throw new XmlRpcResponseException(0x20, 'No title on the page.');
		}
		preg_replace('/<\/?[^>]*>/', '', $title);
		preg_replace('/\s{2,}/', ' ', $title);
		return $title;
	}

/**
 * Extract short cite from pingback requestor page
 *
 * @param string $text
 * @param string $sourceUri, source url permalink
 * @param string $targetUri, target ping url
 * @return string
 */
	protected function fetchPingbackCite($text, $sourceUri, $targetUri) {
	    if(!strstr($text, preg_replace('/&(amp;)?/i', '&amp;', $targetUri))) {
	        if(!strstr($text, str_replace('&amp;', '&', $targetUri))) {
				throw new XmlRpcResponseException(0x11, 'Source URI (' . $sourceUri . ') does not contain a link to the target URI (' . $targetUri . ')');
	        }
	    }
		$paragraphs = explode("\n\n", strip_tags($text, '<a>'));
		foreach ($paragraphs as $paragraph) {
			if (strpos($paragraph, $targetUri) !== false) {
				preg_match('|<a[^>]+?' . preg_quote($targetUri) . '[^>]*>([^>]+?)</a>|', $paragraph, $matchedLinks);
				if (!empty($matchedLinks)) {
					$linkText = $matchedLinks[1];
					$buffer = preg_replace('|\</?PINGBACKTEXT\>|', '', $paragraph);
					if (strlen($linkText) > 100) {
						$linkText = substr($linkText, 0, 100) . '...';
					}
					$stubLink = '<PINGBACKTEXT>' . $linkText . '</PINGBACKTEXT>';
					$buffer = trim(strip_tags(str_replace($matchedLinks[0], $stubLink, $buffer), '<PINGBACKTEXT>'));
					$buffer = strip_tags(preg_replace('|.*?\s(.{0,100}' . preg_quote($stubLink) . '.{0,100})\s.*|s', '$1', $buffer));
					break;
				}
			}
		}
		if (empty($buffer)) {
			throw new XmlRpcResponseException(0x11, 'Source URI (' . $sourceUri . ') does not contain a link to the target URI (' . $targetUri . ')');
		}
		return '... ' . $buffer . ' ...';
	}

/**
 * Scans the input text, pings every linked website for auto-discovery-capable pingback
 * servers, and does an appropriate pingback.
 *
 * @param AppModel $model
 * @param string $sourceUri, source url permalink
 * @param string $text, content of post
 * @link http://www.hixie.ch/specs/pingback/pingback
 */
	public function pingback(&$model, $sourceUri, $text) {
		$links = $this->extractLinks($text, false);
	    foreach ($links as $link) {
	        $this->pingbackUrl($sourceUri, $link);
	    }
	}

/**
 * Attempts to notify the server of targetUri that sourceUri refers to it.
 *
 * @param string $sourceUri, source url permalink
 * @param string $targetUri, target ping url
 */
	protected function pingbackUrl($sourceUri, $targetUri) {
		$Socket = new HttpSocket();
	    $urlData = $Socket->get($targetUri);
	    $pingbackServerUri = $this->determinePingbackUri($Socket->response);
	    if(!empty($pingbackServerUri)) {
			$XmlRpcClient = new XmlRpcClient($pingbackServerUri);
			try {
				$Request = new XmlRpcRequest('pingback.ping', array(new XmlRpcValue($sourceUri), new XmlRpcValue($targetUri)));
				$result = $XmlRpcClient->call($Request);
			} catch (Exception $e) {
				//pingback server return error. we will ignore it
			}
	    }
	}

/**
 * Attempt to determine the pingback Uri of the given url.
 * we try to find the X-Pingback server header
 * and then we looking for rel link
 *
 * @param array $response, HttpSocket response
 */
	public function determinePingbackUri($response) {
		if (!is_array($response) || !isset($response['header'])) {
			return false;
		}
		foreach ($response['header'] as $header => $value) {
			if (strtolower($header) == 'x-pingback') {
				return $value;
			}
		}
		return $this->testRelLink($response['body'], 'pingback');
	}

/**
 * Saves a trackback link
 *
 * @param string $entryId UUID
 * @param array $data
 * @return boolean
 */
	public function trackbackRegisterComment(&$model, $modelId, $data = array()) {
		extract($this->settings[$model->alias]);
		if ($model->{$commentAlias}->hasAny(array($commentAlias . '.foreign_key' => $modelId, 'author_url' => $data['author_url']))) {
			throw new RegisterException('Trackback already registried in system.');
		}
		$isSpam = 'clean';
		$_default = array(
			'comment_type' => 'trackback',
			'foreign_key' => $modelId,
			'model' => $model->alias);
		$data = Set::merge($_default, $data);
		if (isset($data['title'])) {
			$data['author_name'] = $data['title'] . '\'s blog';
		}
		if ($model->{$commentAlias}->Behaviors->enabled('Antispamable')) {
			$isSpam = $model->{$commentAlias}->isSpam(null, array('permalink' => $model->permalink($modelId, true)));
		} else {
			$isSpam = false;
		}
		$modelData = $model->find('first', array('conditions' => array('id' => $modelId), 'recursive' => -1));
		if (!empty($modelData[$model->alias][$requireApproveModelField])) {
			$data[$requireApproveCommentField] = 0;
		}
		$data['is_spam'] = ($isSpam ? 'spam' : 'clean');
		$model->{$commentAlias}->create($data);
		return $model->{$commentAlias}->save();
	}

/**
 * Scans the input text, pings every linked website for auto-discovery-capable pingback
 * servers, and does an appropriate trackback.
 *
 * @param AppModel $model
 * @param array $data that need to pass at trackback server
 *   required keys: title, excerpt,  url (permalink), blog_name,
 * @param string $text entry contents
 * @param mixed $links, optional list of links to make trackback pings
 *
 *
 * @link http://www.sixapart.com/pronet/docs/trackback_spec
 */
	public function trackback(&$model, $data, $text, $links = null) {
		$Socket = new HttpSocket();
		if (is_string($links)) {
			$links = explode(' ', $links);
		} elseif (is_null($links)) {
			$tmplinks = $this->extractLinks($text, false);
			$links = array();
			foreach ($tmplinks as $link) {
			    $urlData = $Socket->get($link);
				if (!empty($urlData)) {
					$trackbackServerUri = $this->determineTrackbackUri($urlData);
					if(!empty($trackbackServerUri)) {
						$links[$link] = $trackbackServerUri;
					}
				}
			}
		}
	    foreach ($links as $link => $trackback) {
	        $this->trackbackUrl($data, $text, $link, $trackback);
	    }
	}

/**
 * Attempts to notify the server of targetUri that sourceUri refers to it.
 *
 * @param $data array list of parameters passed to trackback server
 * @param $data array
 */
	public function trackbackUrl($data, $text, $targetUri, $targetTrackbackUri) {
		if (is_numeric($targetUri)) {
			if (empty($data['excerpt'])) {
				$data['excerpt'] = substr($data, 0, 100);
			}
		} elseif (empty($data['excerpt'])) {
			try {
				$data['excerpt'] = $this->fetchPingbackCite($text, $targetUri, $targetUri);
			} catch (Exception $e) {
				return false;
			}
		}
		$Socket = new HttpSocket();
		$response = $Socket->post($targetTrackbackUri, $data);
		try {
			$Xml = new Xml($response);
			$array = $Xml->toArray(null, array('ignoreCamelize' => true));
			if (!isset($array['response']['result'])) {
				return false;
			} else {
				return $array['response']['result'];
			}
		} catch (Exception $e) {
			return false;
		}
	}

/**
 * Search rel link in html page
 *
 * @param string $text
 * @param string $relType pingback, trackback or some other
 */
	public function testRelLink($text, $relType, $relTag = 'rel') {
		if (preg_match('|<' . $relTag . ' rel="' . $relType . '" href="([^"]+)" ?/?>|', $text, $matches)) {
			return str_replace(array('&amp;', '&lt;', '&gt;', '&quot;'), array('&', '<', '>', '"'), $matches[1]);
		}
		if (preg_match('|<' . $relTag . ' href="([^"]+)" rel="' . $relType . '" ?/?>|', $text, $matches)) {
			return str_replace(array('&amp;', '&lt;', '&gt;', '&quot;'), array('&', '<', '>', '"'), $matches[1]);
		}
		return false;
	}

/**
 * Search tag with class in html page
 *
 * @param string $text
 * @param string $tag
 * @param string $class
 */
	public function testTagClass($text, $tag, $class) {
		if (preg_match('|<' . $tag . '.*? class="' . $class . '".*?>([^<]+?)</' . $tag . '>|', $text, $matches)) {
			return str_replace(array('&amp;', '&lt;', '&gt;', '&quot;'), array('&', '<', '>', '"'), $matches[1]);
		} else {
			return false;
		}
	}

/**
 * Extracts all the hyperlinks from the entry text.
 *
 * @param string $text
 * @param boolean $allowLocalLinks
 * @return array of hyperlink in data
 */
	 protected function extractLinks($text, $allowLocalLinks = false) {
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
				$Socket = new HttpSocket();
				$urlInfo = $Socket->parseUri($url);

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
 * Process html response to find trackback link in RDF spec
 *
 * @param string $text
 */
	public function determineTrackbackUri($text) {
		$result = false;
		preg_match_all('|(\<rdf\:RDF.+?\<\/rdf\:RDF\>)|is', $text, $matches);
		if (isset($matches[1])) {
			foreach ($matches[1] as $match) {
				$Xml = new Xml($match);
				$result = $this->scanRdf($Xml);
				if (!empty($result)) {
					return $result;
				}
			}
		}
		if (empty($result)) {
			$result = $this->testRelLink($text, 'trackback');
		}
		if (empty($result)) {
			$result = $this->testRelLink($text, 'trackback', 'a');
		}
		if (empty($result)) {
			$result = $this->testTagClass($text, 'span', 'trackbacks-link');
		}
		return $result;
	}

/**
 * Scan Xml document to fetch trackback link
 *
 * @param Xml $xml
 * @return mixed
 */
	protected function scanRdf(Xml $xml) {
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
 * Send message to weblog system
 */
	public function blogPing(&$model, $type, $serverUrl, $blogName, $blogUrl, $siteUrl = '', $rssUrl = '', $tags = array()) {
		$XmlRpcClient = new XmlRpcClient($serverUrl);
		if ($type == 'extendedPing') {
			try {
				if (is_array($tags)) {
					$tags = join('|', $tags);
				}
				$Request = new XmlRpcRequest('weblogUpdates.extendedPing', array(new XmlRpcValue($blogName), new XmlRpcValue($siteUrl), new XmlRpcValue($blogUrl), new XmlRpcValue($rssUrl), $tags));
				$result = $XmlRpcClient->call($Request);
			} catch (XmlRpcResponseException $e) {
				// Nothing.
			}
		} else {
			try {
				$Request = new XmlRpcRequest('weblogUpdates.ping', array(new XmlRpcValue($blogName), new XmlRpcValue($blogUrl)));
				$result = $XmlRpcClient->call($Request);
			} catch (XmlRpcResponseException $e) {
				// Nothing.
			}
		}
	}
}
