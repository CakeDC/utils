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

/**
 * Utils Plugin
 *
 * Utils Tiny Sluggable Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
class TinySluggableBehavior extends ModelBehavior {

/**
 * Settings to configure the behavior
 *
 * @var array
 */
	public $settings = array();

/**
 * Default settings
 *
 * @var array
 */
	protected $_defaults = array(
		'tinySlug' => 'tiny_slug',
		'codeset' => '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
		'orderField' => 'created'
	);

/**
 * Initiate behavior - The Model must have a field for the tiny_slug along with a "created" field
 *
 * @param Model $Model
 * @param array $settings Settings for the behavior. Keys: 
 * 	- tinySlug: name of the tiny slug field in the table [default: tiny_slug]
 *  - codeset: valid characters for tiny slug [default: 0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ]
 * @return void
 */
	public function setup(Model $Model, $settings = array()) {
		$this->settings[$Model->alias] = array_merge($this->_defaults, $settings);
		$Model->tinySlug = $this->settings[$Model->alias]['tinySlug'];
		$this->settings[$Model->alias]['base'] = strlen($this->settings[$Model->alias]['codeset']);
	}

/**
 * beforeSave callback
 *
 * @param Model $Model
 * @param array $options
 * @return boolean
 */
	public function beforeSave(Model $Model, $options = array()) {
		if (empty($Model->data[$Model->alias])) {
			return;
		}

		if (empty($Model->data[$Model->alias][$Model->tinySlug])) {
			$Model->data[$Model->alias][$Model->tinySlug] = $this->__getNextSlug($Model);
		}
		return true;
	}

/**
 * Calculates the next available slug and returns it
 *
 * @param Model $Model
 * @return string next avalible tiny slug
 */
	private function __getNextSlug(Model $Model) {
		$new = '';
		$prev = $Model->find('first', array(
			'contain' => array(),
			'fields' => array("{$Model->alias}.{$Model->tinySlug}", "{$Model->alias}.created"),
			'order' => "{$Model->alias}.{$this->settings[$Model->alias]['orderField']} DESC"));

		if (empty($prev)) {
			$new = $this->settings[$Model->alias]['codeset'][0];
		} else {
			$new = $this->__toShort($Model, (string) $this->__toDecimal($Model, $prev[$Model->alias][$Model->tinySlug]) + 1);
			$attempts = 0;
			$maxAttempts = 5; // Overriden after the first attempt
			$new = $prev[$Model->alias][$Model->tinySlug];

			// Check if this slug does not already exists
			do {
				if ($attempts == 1) {
					$maxAttempts = $Model->find('count', array(
						'conditions' => array(
							$Model->alias . '.created' => $prev[$Model->alias]['created'])));
				}
				$new = $this->__toShort($Model, $this->__toDecimal($Model, $new) + 1);
				$existing = $Model->find('count', array(
					'conditions' => array(
						$Model->alias . '.' . $Model->tinySlug => $new)));
				$attempts++;
			} while (!empty($existing) && $attempts < $maxAttempts);
		}
		return $new;
	}

/**
 * Calculates the
 *
 * @param Model $Model
 * @param int $decimal the decimal to convert
 * @return string
 */
	private function __toShort(Model $Model, $decimal) {
		$codeSet = $this->settings[$Model->alias]['codeset'];
		$base = $this->settings[$Model->alias]['base'];
		$short = '';
		while ($decimal > 0) {
			$short = substr($codeSet, ($decimal % $base), 1) . $short;
			$decimal = floor($decimal / $base);
		}
		return $short;
	}

/**
 * Converts a tiny slug into an integer
 *
 * @param Model
 * @param string $short
 * @return integer
 */
	private function __toDecimal(Model $Model, $short) {
		$codeSet = $this->settings[$Model->alias]['codeset'];
		$base = $this->settings[$Model->alias]['base'];
		$decimal = 0;
		for ($i = strlen($short); $i; $i--) {
			$decimal += strpos($codeSet, substr($short, (-1 * ( $i - strlen($short) )), 1)) * pow($base, $i - 1);
		}
		return $decimal;
	}

}
