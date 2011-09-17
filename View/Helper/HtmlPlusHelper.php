<?php
/**
 * Copyright 2011, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2011, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

/**
 * Useful extensions to the Html Helper
 *
 * @package utils
 * @subpackage utils.views.helpers
 */
class HtmlPlusHelper extends AppHelper {

/**
 * Takes the script_for_layout variable and returns the meta tags form it omitting the rest
 *
 * @param string $scripts 
 * @return string
 */
	public function metaForLayout($scripts = null) {
		return $this->_getScriptType($scripts, 'meta');
	}

/**
 * Takes the script_for_layout variable and returns the script tags form it omitting the rest
 *
 * @param string $scripts 
 * @return string
 */
	public function scriptsForLayout($scripts) {
		return $this->_getScriptType($scripts, 'script');
	}

/**
 * Process input scripts and return only the specified type
 *
 * @param string $scripts Scripts from $scripts_for_layout, if null, will be taken form the View object
 * @param string $type Script type to return
 * @return string
 */
	protected function _getScriptType($scripts = null, $type) {
		$scripts = explode("\n\t", $scripts);
		$result = array();
		foreach ($scripts as $s) {
			if (strpos($s, '<' . $type) === 0) {
				$result[] = $s;
			}
		}
		return implode("\n\t", $result);
	}
	
}
