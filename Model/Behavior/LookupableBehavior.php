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
 * Utils Lookupable Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
 App::uses('ModelBehavior', 'Model');

class LookupableBehavior extends ModelBehavior {

/**
 * Lookupable behavior settings
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
		'types' => array());

/**
 * Setup
 *
 * @param AppModel $Model
 * @param array $settings
 * @return void
 */
	function setup(Model $Model, $settings = array()) {
		$settings = array_merge($this->_defaults, $settings);
		$this->settings[$Model->alias] = $settings;
	}

/**
 * This method will used by lookup method to find already exist record.
 *
 * @param Model
 * @param string $modelClass, 
 * @param string $name, 
 * @return array, record or false
 */
	public function findExistingRecord(Model $Model, $modelClass, $name) {
		return $Model->{$modelClass}->find('first', array(
			'contain' => array(),
			'conditions' => array(
				$modelClass . '.' . $Model->{$modelClass}->displayField => $name)));
	}
	
/**
 * This method will used by lookup method to save new record.
 *
 * @param Model
 * @param string $modelClass, 
 * @param array $data
 * @return boolean
 */
	public function saveLookupRecord(Model $Model, $modelClass, $data) {
		$Model->{$modelClass}->create();
		return $Model->{$modelClass}->save(array($modelClass => $data), array(
				'validate' => false,
				'callbacks' => true));
	}
	
/**
 * This method will lookup the publisher, narrator and author based on the name
 *
 * If they don't exist the record will be created. The result will be always 
 * merged into $this->data if lookup return true.
 *
 * @param Model
 * @param string
 * @param string
 * @return boolean True on success
 */
	public function lookup(Model $Model, $type = null, $name = null) {
		extract($this->settings[$Model->alias]);

		$type = strtolower($type);
		$modelClass = Inflector::classify($type);
		if (!in_array($modelClass, $types) || empty($name)) {
			return false;
		}

		$result = $Model->findExistingRecord($modelClass, $name);

		if (empty($result)) {
			$fieldName = $Model->{$modelClass}->displayField;
			$data = array($fieldName => $name);

			// if (isset($Model->data[$modelClass])) {
				// if (isset($Model->data[$modelClass][$fieldName]) && $Model->data[$modelClass][$fieldName] != $name) {
					// unset($Model->data[$modelClass]['id']);
				// }
			if (isset($Model->data[$modelClass])) {
				$data = Set::merge($Model->data[$modelClass], $data);
			}
			
			$result = $Model->saveLookupRecord($modelClass, $data);
			
			$Model->data[$modelClass]['id'] = $Model->{$modelClass}->id;
			$Model->data[$modelClass]['name'] = $name;
			$Model->data[$Model->alias][$type . '_id'] = $Model->{$modelClass}->id;
		} else {
			$Model->data[$modelClass] = $result[$modelClass];
			$Model->data[$Model->alias][$type . '_id'] = $result[$modelClass]['id'];
		}

		return true;
	}

/**
 * afterFind callback
 *
 * @param $Model
 * @param array
 * @param boolean
 * @return array
 */
	public function afterFind(Model $Model, $results, $primary) {
		extract($this->settings[$Model->alias]);
		foreach ($results as $key => $record) {
			foreach ($types as $submodel) {
				$submodel = Inflector::camelize($submodel);
				$viewField = Inflector::underscore($submodel . '_' . $Model->{$submodel}->displayField);
				if (isset($record[$submodel][$Model->{$submodel}->displayField])) {
					$results[$key][$Model->alias][$viewField] = $record[$submodel][$Model->{$submodel}->displayField];
				}
			}
		}
		return $results;
	}

/**
 * afterSave callback
 *
 * Checks for authors, publishers and narrators and creates them on the fly if 
 * they dont exist, if they exist by the entered name the id will be looked up
 * and stored in the record.
 *
 * @return boolean True on success
 */
	public function afterSave(Model $Model, $created) {
		extract($this->settings[$Model->alias]);
		$resave = false;
		foreach ($types as $submodel) {
			$viewField = Inflector::underscore($submodel . '_' . $Model->{Inflector::classify($submodel)}->displayField);
			if (isset($Model->data[$Model->alias][$viewField]) && !empty($Model->data[$Model->alias][$viewField])) {
				if ($Model->lookup($submodel, $Model->data[$Model->alias][$viewField])) {
					$resave = true;
				}
			}
		}

		if ($resave) {
			$result = $Model->save($Model->data, array(
				'validate' => false,
				'callbacks' => false));
			$Model->data = $result;
		}
	}
}
