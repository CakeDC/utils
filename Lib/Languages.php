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

App::uses('L10n', 'I18n');

/**
 * Utils Plugin
 *
 * Utils Languages Library
 *
 * @package utils
 * @subpackage utils.libs
 */
class Languages extends L10n {

/**
 * Constructor
 *
 */
	public function __construct() {
	}

/**
 * List of languages that can be used in selects for example
 *
 * @param string
 * @return array
 */
	public function lists($order = 'language') {
		static $lists = array();

		if (empty($lists)) {
			$catalogs = $this->catalog();
			$match = null;
			foreach ($catalogs as $catalog) {
				if ($match != $catalog['localeFallback']) {
					$lists[$catalog['language']] = $catalog['localeFallback'];
				}
				$match = $catalog['localeFallback'];
			}
		}
		ksort($lists);

		if ($order === 'locale') {
			return array_flip($lists);
		}
		return $lists;
	}
}
