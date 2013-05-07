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
		'engine' => 'serialize',
		'fields' => array(),
		'field' => null
	);

/**
 * Setup
 *
 * @param object AppModel
 * @param array $config
 */
	public function setup(Model $Model, $config = array()) {
		$settings = array_merge($this->_defaults, $config);
		if(!empty($settings['field']) && empty($settings['fields'])) {
			$settings['fields'] = $settings['field'];
		}
		if (!is_array($settings['fields'])) {
			$settings['fields'] = array($settings['fields']);
		}
		$this->settings[$Model->alias] = $settings;
	}

/**
 * After find callback
 *
 * @param Model $Model
 * @param mixed $results The results of the find operation
 * @param boolean $primary Whether this model is being queried directly (vs. being queried as an association)
 * @return mixed Result of the find operation
 */
	public function afterFind(Model $Model, $results, $primary = false) {
		if (!empty($results)) {
			foreach ($results as $key => $result) {
				$results[$key] = $this->deserialize($Model, $result);
			}
		}
		return $results;
	}

/**
 * Called before each save operation
 *
 * @param Model $Model
 * @param array $options
 * @return boolean True if the operation should continue, false if it should abort
 */
	public function beforeSave(Model $Model, $options = array()) {
		$Model->data = $this->serialize($Model, $Model->data);
		return true;
	}

/**
 * Called after each save operation
 *
 * @param Model $Model
 * @param bool $created
 * @param array $options
 * @return void
 */
	public function afterSave(Model$Model, $created, $options = array()) {
		if(!empty($options['deserialize'])) $Model->data = $Model->deserialize($Model->data);
	}

/**
 * Serializes data
 *
 * @param Model $Model
 * @param array $data
 * @internal param string $matchId
 * @return boolean
 */
	public function serialize(Model $Model, &$data) {
		if (empty($data[$Model->alias])) {
			return $data;
		}
		$fields = $this->settings[$Model->alias]['fields'];
		$engine = $this->settings[$Model->alias]['engine'];
		if (!empty($data[$Model->alias][0]) && array_intersect_key($fields, array_keys($data[$Model->alias][0]))) {
			foreach ($data[$Model->alias] as $key => $model) {
				$model = $Model->serialize(array($Model->alias => $model));
				$data[$Model->alias][$key] = $model[$Model->alias];
			}
		} else {
			foreach ($fields as $field) {
				if (isset($data[$Model->alias][$field]) && is_array($data[$Model->alias][$field])) {
					if ($engine == 'json') {
						$data[$Model->alias][$field] = @json_encode($data[$Model->alias][$field]);
					} else {
						$data[$Model->alias][$field] = @serialize($data[$Model->alias][$field]);
					}
				}
			}
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
		if (empty($data[$Model->alias])) {
			return $data;
		}
		$fields = $this->settings[$Model->alias]['fields'];

		$engine = $this->settings[$Model->alias]['engine'];
		foreach ($fields as $field) {
			if (!empty($data[$Model->alias][$field])) {
				if (is_string($data[$Model->alias][$field])) {
					if ($engine == 'json') {
						$data[$Model->alias][$field] = @json_decode($data[$Model->alias][$field], true);
					} else {
						$data[$Model->alias][$field] = @unserialize($data[$Model->alias][$field]);
					}
					if ($data[$Model->alias][$field] === false) {
						$data[$Model->alias][$field] = array();
					}
				}

			} elseif (!empty($data[$Model->alias]) && array_key_exists($field, $data[$Model->alias])) {
				$data[$Model->alias][$field] = array();
			}
		}
		return $data;
	}
}
