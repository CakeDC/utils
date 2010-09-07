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
 * Utils Serializeable Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
class SerializableBehavior extends ModelBehavior {

/**
 * Settings
 * 
 * @var mixed
 */
	public $settings = array();

/**
 * Default settings
 * 
 * @var array
 */
	protected $_defaults = array(
		'field' => 'data');

/**
 * Setup
 *
 * @param object AppModel
 * @param array $config
 */
	public function setup(&$model, $config = array()) {
		$settings = array_merge($this->_defaults, $config);
		$this->settings[$model->alias] = $settings;
	}
	
/**
 * After find callback
 *
 * @param mixed $results The results of the find operation
 * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
 * @return mixed Result of the find operation
 */	
	function afterFind($Model, $results, $primary = false) {
		$config = $this->settings[$Model->alias];
		if (!empty($results)) {
			foreach($results as $key => $result) {
				$results[$key] = $Model->deserialize($result);
			}
		}
		return $results;
	}

/**
 * Called before each save operation
 *
 * @return boolean True if the operation should continue, false if it should abort
 */
	function beforeSave(&$Model, $options = array()) {
		$Model->data = $Model->serialize($Model->data);
		return true;
	}
	
/**
 * 
 *
 * @param string $matchId
 * @param array $data
 * @return boolean
 */	
	public function serialize($Model, &$data) {
		$config = $this->settings[$Model->alias];
		if (isset($data[$Model->alias][$config['field']]) && is_array($data[$Model->alias][$config['field']])) {
			$data[$Model->alias][$config['field']] = @serialize($data[$Model->alias][$config['field']]);
		}
		return $data;
	}

/**
 * 
 *
 * @param string $matchId
 * @param array $data
 * @return boolean
 */	
	public function deserialize($Model, &$data) {
		$config = $this->settings[$Model->alias];
		if (!empty($data[$Model->alias][$config['field']])) {
			if (is_string($data[$Model->alias][$config['field']])) {
				$data[$Model->alias][$config['field']] = @unserialize($data[$Model->alias][$config['field']]);
				if ($data[$Model->alias][$config['field']] === false) {
					$data[$Model->alias][$config['field']] = array();
				}
			}
		} else {
			$data[$Model->alias][$config['field']] = array();
		}
		return $data;
	}
}
