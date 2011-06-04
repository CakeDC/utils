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
 * Utils Cleaner Helper
 *
 * @package utils
 * @subpackage utils.views.cleaner
 */
class CleanerHelper extends AppHelper {

/**
 * Other helpers
 *
 * @var array
 */
	public $helpers = array('Javascript');

/**
 * Replace image thumb
 *
 * @var boolean $replaceImgThumb
 */
	public $replaceImgThumb = false;

/**
 * Tags
 *
 * @var array $tagsArray
 */
	public $tagsArray = array();

/**
 * Attributes
 *
 * @var array $attributesArray
 */
	public $attributesArray = array();

/**
 * Holds different configurations
 *
 * @var array $config
 */
	public $config = array(
		'full' => array(
			'replaceImgThumb' => false,
			'tagsArray' => array('pre', 'br', 'p', 'strong', 'em', 'ul', 'ol', 'li', 'dl', 'dd', 'dt', 'a', 'img', 'i', 'u', 'b'),
			'attributesArray' => array('lang', 'src', 'href', 'title', 'alt', 'width', 'height')),
		'mini' => array(
			'replaceImgThumb' => true,
			'tagsArray' => array('br', 'p', 'strong', 'em', 'ul', 'ol', 'li', 'dl', 'dd', 'dt', 'a', 'img', 'i', 'u', 'b'),
			'attributesArray' => array('src', 'href', 'title', 'alt')),
		'small'	 => array(
			'replaceImgThumb' => false,
			'tagsArray' => array('img'),
			'attributesArray' => array('src', 'href', 'title'))
		);

/**
 * Constructor
 *
 */
	public function __construct() {
		foreach ($this->config['full'] as $key => $value) {
			$this->{$key} = $value;
		}
		return parent::__construct();
	}

/**
 * Configuration of cleaner. possible to call separately or from clean method
 *
 * @param string $options 
 * @return void
 */
	public function configure($options) {
		if (is_null($options)) {
			return;
			//$options = 'full';
		}
		if (is_string($options) && isset($this->config[$options])) {
			foreach ($this->config[$options] as $key => $value) {
				$this->{$key} = $value;
			}
		} else {
			if (isset($options['tagsArray']) && is_array($options['tagsArray'])) {
				$this->tagsArray = array_map('strtolower', $options['tagsArray']);
			}
			if (isset($options['attributesArray']) && is_array($options['attributesArray'])) {
				$this->attributesArray = array_map('strtolower', $options['attributesArray']);
			}
			if (isset($options['replaceImgThumb']) && is_bool($options['replaceImgThumb'])) {
				$this->replaceImgThumb = $options['replaceImgThumb'];
			}
		}
	}

/**
 * Main clean method
 *
 * @param string $data
 * @param mixed $options String for config or array to set custom options
 * @return string
 */
	public function clean($data, $options = null) {
		$this->configure($options);
		$cleaned = $data;
		// disable call to Helper::clean because it lead to the broken texts
		// $cleaned = parent::clean($data);

		if (is_array($cleaned)) {
			foreach($cleaned as $key => $value) {
				if (is_string($value)) {
					$cleaned[$key] = $this->__remove($value);
				}
			}
			return $cleaned;
		} else if (is_string($cleaned)) {
			return $this->__remove($cleaned);
		} else {
			return $cleaned;
		}
	}

/**
 * Remove all unwanted tags and attributes.
 *
 * @param string $cleaned 
 * @return void
 */
	function __remove($cleaned) {
		do {
			$oldstring = $cleaned;
			$cleaned = $this->__tagsFilter($cleaned);
		} while ($oldstring != $cleaned);
		return $cleaned;
	}

/**
 * Strip a string of certain tags
 *
 * @param string $cleaned 
 * @return void
 */
	function __tagsFilter($cleaned) {
		$beforeTag = NULL;
		$afterTag = $cleaned;
		$tagOpenStart = strpos($cleaned, '<');
		while($tagOpenStart !== false) {
			$beforeTag .= substr($afterTag, 0, $tagOpenStart);
			$afterTag = substr($afterTag, $tagOpenStart);
			$fromTagOpen = substr($afterTag, 1);
			$tagOpenEnd = strpos($fromTagOpen, '>');
			if ($tagOpenEnd === false) {
				break;
			}
			$tagOpenNested = strpos($fromTagOpen, '<');
			if (($tagOpenNested !== false) && ($tagOpenNested < $tagOpenEnd)) {
				$beforeTag .= substr($afterTag, 0, ($tagOpenNested+1));
				$afterTag = substr($afterTag, ($tagOpenNested+1));
				$tagOpenStart = strpos($afterTag, '<');
				continue;
			}
			$tagOpenNested = (strpos($fromTagOpen, '<') + $tagOpenStart + 1);
			$currentTag = substr($fromTagOpen, 0, $tagOpenEnd);
			$tagLength = strlen($currentTag);
			if (!$tagOpenEnd) {
				$beforeTag .= $afterTag;
				$tagOpenStart = strpos($afterTag, '<');
			}
			$tagLeft = $currentTag;
			$attributeSet = array();
			$currentSpace = strpos($tagLeft, ' ');
			if (substr($currentTag, 0, 1) == "/") {
				$isCloseTag = true;
				list($tagName) = explode(' ', $currentTag);
				$tagName = substr($tagName, 1);
			} else {
				$isCloseTag = false;
				list($tagName) = explode(' ', $currentTag);
			}
			if ((!preg_match("/^[a-z][a-z0-9]*$/i",$tagName)) || (!$tagName)) {
				$afterTag = substr($afterTag, ($tagLength + 2));
				$tagOpenStart = strpos($afterTag, '<');
				continue;
			}
			while ($currentSpace !== false) {
				$fromSpace = substr($tagLeft, ($currentSpace + 1));
				$nextSpace = strpos($fromSpace, ' ');
				$openQuotes = strpos($fromSpace, '"');
				$closeQuotes = strpos(substr($fromSpace, ($openQuotes + 1)), '"') + $openQuotes + 1;
				if (strpos($fromSpace, '=') !== false) {
					if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes+1)), '"') !== false)) {
						$attribute = substr($fromSpace, 0, ($closeQuotes + 1));
					}
					else {
						$attribute = substr($fromSpace, 0, $nextSpace);
					}
				} else {
					$attribute = substr($fromSpace, 0, $nextSpace);
				}
				if (!$attribute) {
					$attribute = $fromSpace;
				}
				$attributeSet[] = $attribute;
				$tagLeft = substr($fromSpace, strlen($attribute));
				$currentSpace = strpos($tagLeft, ' ');
			}
			$tagFound = in_array(strtolower($tagName), $this->tagsArray);
			if ($tagFound) {
				if (!$isCloseTag) {
					if ($this->__filterAttr($attributeSet, strtolower($tagName))) {
						$beforeTag .= '<' . $tagName;
						for ($i = 0; $i < count($attributeSet); $i++) {
							$beforeTag .= ' ' . $attributeSet[$i];
						}
						if (strpos($fromTagOpen, "</" . $tagName)) {
							$beforeTag .= '>';
						} else {
							$beforeTag .= ' />';
						}
					}
				} else {
					$beforeTag .= '</' . $tagName . '>';
				}
			}
			$afterTag = substr($afterTag, ($tagLength + 2));
			$tagOpenStart = strpos($afterTag, '<');
		}
		$beforeTag .= $afterTag;
		return $beforeTag;
	}

/**
 * Strip a tag of certain attributes
 *
 * @param string $attributeSet 
 * @param string $tag 
 * @return void
 */
	function __filterAttr(&$attributeSet, $tag) {
		$newAttrSet = array();
		for ($i = 0; $i <count($attributeSet); $i++) {
			if (!$attributeSet[$i]) {
				continue;
			}
			$attributeSubSet = explode('=', trim($attributeSet[$i]));
			if (count($attributeSubSet)>2) {
				$attributeSubSetTmp = $attributeSubSet;
				$attributeSubSetTmp = array_reverse($attributeSubSetTmp);
				array_pop($attributeSubSetTmp);
				$attributeSubSetTmp = array_reverse($attributeSubSetTmp);
				$attributeSubSet[1] = join('=', $attributeSubSetTmp);
			}
			list($attributeSubSet[0]) = explode(' ', $attributeSubSet[0]);
			if (!eregi("^[a-z]*$",$attributeSubSet[0]) || substr($attributeSubSet[0], 0, 2) == 'on') {
				continue;
			}
			if ($attributeSubSet[1]) {
				$attributeSubSet[1] = str_replace('&#', '', $attributeSubSet[1]);
				$attributeSubSet[1] = preg_replace('/\s+/', '', $attributeSubSet[1]);
				$attributeSubSet[1] = str_replace('"', '', $attributeSubSet[1]);
				if ((substr($attributeSubSet[1], 0, 1) == "'") && (substr($attributeSubSet[1], (strlen($attributeSubSet[1]) - 1), 1) == "'")) {
					$attributeSubSet[1] = substr($attributeSubSet[1], 1, (strlen($attributeSubSet[1]) - 2));
				}
				$attributeSubSet[1] = stripslashes($attributeSubSet[1]);
			}
			if (((strpos(strtolower($attributeSubSet[1]), 'expression') !== false) && (strtolower($attributeSubSet[0]) == 'style')) || $this->__checkPos($attributeSubSet[1])) {
				continue;
			}
			$attributeFound = in_array(strtolower($attributeSubSet[0]), $this->attributesArray);

			if (!$this->__postFilter($tag, strtolower($attributeSubSet[0]), $attributeSubSet[1])) {
				return false;
			}

			if ($attributeFound) {
				if ($attributeSubSet[1]) {
					$newAttrSet[] = $attributeSubSet[0] . '="' . $attributeSubSet[1] . '"';
				} elseif ($attributeSubSet[1] == "0") {
					$newAttrSet[] = $attributeSubSet[0] . '="0"';
				} else {
					$newAttrSet[] = $attributeSubSet[0] . '="' . $attributeSubSet[0] . '"';
				}
			}
		}
		$attributeSet = $newAttrSet;
		return true;
	}

/**
 * Check pos
 *
 * @param string $attrval 
 * @return void
 */
	function __checkPos($attrval) {
		$checkList = array('javascript:', 'behaviour:', 'vbscript:', 'mocha:', 'livescript:');
		$result = false;
		foreach ($checkList as $check) {
			$result = $result || (strpos(strtolower($attrval), $check) !== false);
		}
		return $result;
	}

/**
 * Filter external image links
 *
 * @param string $tag 
 * @param string $attribute 
 * @param string $attributeValue 
 * @return void
 */
	function __postFilter($tag, $attribute, &$attributeValue) {
		if ($tag == 'img' && $attribute == 'src') {
			if (substr($attributeValue, 0, 1) != '/' && strpos($attributeValue, FULL_BASE_URL) === false) {
				return false;
			} else {
				if ($this->replaceImgThumb && preg_match('/(?<path>\/media\/display\/)(?<uuid>[0-9a-z-]{36})/', $attributeValue, $matches)) {
					$attributeValue = $matches['path'] . 'thumb/' . $matches['uuid'];
				}
			}
		}
		return true;
	}

/**
 * Replave all image tags
 *
 * @param string $text 
 * @param boolean $showVideo 
 * @return void
 */
	function replaceAllImageTags($text, $showVideo = true) {
		$text = $this->bbcode2js($text, $showVideo);
		// while (preg_match('/src="(\/media\/display\/)([0-9a-z-]{36})"/', $text, $matches)) {
		// 		$name = 'src="' . $matches[1] . $matches[2] . '"';
		// 		$newName = 'src="' . $matches[1] . 'thumb/' . $matches[2] . '"';
		// 		$text = str_replace($name, $newName, $text);
		// 	}
		return $text;
	}

/**
 * Convert BBCode to Javascript for video embedding
 *
 * @param string $text 
 * @param boolean $show 
 * @return void
 */
	function bbcode2js($text, $show = true) {
		do {
			$oldstring = $text;
			$text = $this->__bb2js($text, $show);
		} while ($oldstring != $text);
		return $text;
	}

/**
 * BB 2 JS
 *
 * @param string $text 
 * @param boolean $show 
 * @return void
 */
	function __bb2js($text, $show = true) {
		if(preg_match('/\[googlevideo\]/', $text)) {
			$vid = null;
			if (preg_match('/(?:docid=)([-a-z0-9]+)/i', $text, $found)) {
				if (isset($found[1])) {
					$vid = $found[1];
				}
			}
			if ($vid) {
				$this->Javascript->link('vipers-video-quicktags', false);
				$this->Javascript->codeBlock('vvq_googlevideo("vvq_' . $vid . '", "325", "265", "' . $vid . '");', array('inline' => false), true);

				$content = "<p id=\"vvq_$vid\">";
				$content .= '<a href="http://video.google.com/videoplay?docid=' . $vid .'">';
				$content .= 'http://video.google.com/videoplay?docid=' . $vid . '</a></p><br />';
				if (!$show) {
					$content = '';
				}
				$text = str_replace('[googlevideo]http://video.google.com/videoplay?docid=' . $vid . '[/googlevideo]', $content, $text);
			} else {
				$start = strpos($text, '[googlevideo]');
				$endStr = '[/googlevideo]';
				$end = strpos($text, $endStr, $start) + strlen($endStr);
				$text = substr($text, 0, $start) . substr($text, $end);
			}
		} elseif (preg_match('/\[youtubevideo\]/', $text)) {
			$vid = null;
			if (preg_match('/(?:v=)([-_a-z0-9]+)/i', $text, $found)) {
				if (isset($found[1])) {
					$vid = $found[1];
				}
			}
			if ($vid) {
				$this->Javascript->link('vipers-video-quicktags', false);
				$this->Javascript->codeBlock('vvq_youtube("vvq_' . $vid . '", "325", "271", "' . $vid . '");', array('inline' => false), true);

				$content = "<p id=\"vvq_$vid\">";
				$content .= '<a href="http://www.youtube.com/watch?v=' . $vid . '">';
				$content .= 'http://www.youtube.com/watch?v=' . $vid . '</a></p><br />';
				if (!$show) {
					$content = '';
				}
				$text = str_replace('[youtubevideo]http://www.youtube.com/watch?v=' . $vid . '[/youtubevideo]', $content, $text);
			} else {
				$start = strpos($text, '[youtubevideo]');
				$endStr = '[/youtubevideo]';
				$end = strpos($text, $endStr, $start) + strlen($endStr);
				$text = substr($text, 0, $start) . substr($text, $end);
			}
		} elseif (preg_match('/\[breakvideo\]/', $text)) {
			$vid = null;
			if (preg_match('/\/([a-zA-Z0-9]+)(\[)/', $text, $found)) {
				if (isset($found[1])) {
					$vid = $found[1];
				}
			}
			if ($vid) {
				$content = '<object width="464" height="392"><param name="movie" value="http://embed.break.com/' . $vid . '"></param><param name="allowScriptAccess" value="always"></param><embed src="http://embed.break.com/' . $vid . '" type="application/x-shockwave-flash" allowScriptAccess=always width="464" height="392"></embed></object>';
				if (!$show) {
					$content = '';
				}
				$text = str_replace('[breakvideo]http://embed.break.com/' . $vid . '[/breakvideo]', $content, $text);
			} else {
				$start = strpos($text, '[breakvideo]');
				$endStr = '[/breakvideo]';
				$end = strpos($text, $endStr, $start) + strlen($endStr);
				$text = substr($text, 0, $start) . substr($text, $end);
			}
		}
		return $text;
	}
}
