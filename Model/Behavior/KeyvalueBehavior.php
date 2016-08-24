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
 * Utils Keyvalue Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
class KeyvalueBehavior extends ModelBehavior {

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
		'foreignKey' => 'user_id');

/**
 * Setup
 *
 * @param object AppModel
 * @param array $config
 */
	public function setup(Model $model, $config = array()) {
		$settings = array_merge($this->_defaults, $config);
		$this->settings[$model->alias] = $settings;
	}

/**
 * Returns details for named section
 * 
 * @var string
 * @var string
 * @return array
 */
	public function getSection($Model, $foreignKey = null, $section = null) {
		$Model->recursive = -1;
		$results = $Model->find('all',
 			array('conditions' => array($this->settings[$Model->alias]['foreignKey'] => $foreignKey)),
			array('fields' => array('field', 'value')));
		
		if ($results) {
		foreach($results as $result) {
				$details[] = array('field' => $result[$Model->alias]['field'], 'value' => $result[$Model->alias]['value']);
			}
	
			$detailArray = array();
			foreach ($details as $value) {
				$key = preg_split('/\./', $value['field'], 2);
				$detailArray[$key[0]][$key[1]] = $value['value'];
			}
	
			return ($detailArray[$section]);
		}
	}

/**
 * Save details for named section
 * 
 * @var string
 * @var array
 * @var string
 */
	public function saveSection($Model, $foreignKey = null, $data = null, $section = null) {
		foreach($data as $model => $details) {
			foreach($details as $key => $value) {
				$newDetail = array();
				$Model->recursive = -1;
				$tmp = $Model->find('first', array(
					'conditions' => array(
						$this->settings[$Model->alias]['foreignKey'] => $foreignKey,
						'field' => $section . '.' . $key),
					'fields' => array('id')));
				$newDetail[$Model->alias]['id'] = (isset($tmp[$Model->alias]['id']) ? $tmp[$Model->alias]['id'] : false);
				$newDetail[$Model->alias]['user_id'] = $Model->User->id;
				$newDetail[$Model->alias]['field'] = $section . '.' . $key;
				$newDetail[$Model->alias]['value'] = $value;
				return ($Model->save($newDetail) ? true : false);
			}
		}
	}
}

