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
	public function setup(&$model, $config = array()) {
		$settings = array_merge($this->_defaults, $config);
		$this->settings[$model->alias] = $settings;
	}

/**
 * Returns details for named section
 *
 * @var integer $foreignKey
 * @var string $section
 * @return array
 */
	public function getSection($Model, $foreignKey = null, $section = null) {
		$Model->recursive = -1;
		$results = $Model->find('all',
			array('conditions' => array($this->settings[$Model->alias]['foreignKey'] => $foreignKey)),
			array('fields' => array('field', 'value')));

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

/**
 * Save details for named section
 *
 * @var integer $foreignKey
 * @var array $data
 * @var string $section
 * @return boolean True on success, or false on failure
 */
	public function saveSection($Model, $foreignKey = null, $data = null, $section = null) {
		$saveAll = array();
		foreach($data as $details) {
			foreach($details as $key => $value) {
				$conditions = array(
					$this->settings[$Model->alias]['foreignKey'] => $foreignKey,
					'field' => $section . '.' . $key
				);
				$Model->recursive = -1;
				$primaryKey = $Model->field($Model->primaryKey, $conditions);
				$newDetail = array(
					$Model->primaryKey => $primaryKey,
					$this->settings[$Model->alias]['foreignKey'] => $foreignKey,
					'field' => $section . '.' . $key,
					'value' => $value
				);
				$saveAll[] = $newDetail;
			}
		}

		return $Model->saveAll($saveAll);
	}

}
