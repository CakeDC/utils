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
 * Utils Publishable Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
class PublishableBehavior extends ModelBehavior {
/**
 * Contain settings indexed by model name.
 *
 * @var array
 */
	var $__settings = array();

/**
 * Initiate behaviour for the model using settings.
 *
 * @param object $Model Model using the behaviour
 * @param array $settings Settings to override for model.
 */
	function setup(&$Model, $settings = array()) {
		$default = array('field' => 'published', 'field_date' => 'published_date', 'find' => true);

		if (!isset($this->__settings[$Model->alias])) {
			$this->__settings[$Model->alias] = $default;
		}

		$this->__settings[$Model->alias] = array_merge($this->__settings[$Model->alias], is_array($settings) ? $settings : array());
	}


/**
 * Marks a record as published and optionally change other fields.
 *
 * @param object $Model Model from where the method is being executed.
 * @param mixed $id ID of the publishable record.
 * @param $attributes Other fields to change (in the form of field => value)
 * @return boolean Result of the operation.
 */
	function publish(&$Model, $id = null, $attributes = array()) {
		if ($Model->hasField($this->__settings[$Model->alias]['field'])) {
			if (empty($id)) {
				$id = $Model->id;
			}

			$onFind = $this->__settings[$Model->alias]['find'];
			$this->enablePublishable($Model, false);

			$data = array($Model->alias => array(
				$Model->primaryKey => $id,
				$this->__settings[$Model->alias]['field'] => true
			));

			$Model->id = $id;
			if (isset($this->__settings[$Model->alias]['field_date']) && $Model->hasField($this->__settings[$Model->alias]['field_date'])) {
				if (!$Model->field($this->__settings[$Model->alias]['field_date'])) {
					$data[$Model->alias][$this->__settings[$Model->alias]['field_date']] = date('Y-m-d H:i:s');
				}
			}

			if (!empty($attributes)) {
				$data[$Model->alias] = array_merge($data[$Model->alias], $attributes);
			}

			$result = $Model->save($data, false, array_keys($data[$Model->alias]));
			$this->enablePublishable($Model, 'find', $onFind);

			return ($result !== false);
		}

		return false;
	}

/**
 * Marks a record as unpublished and optionally change other fields.
 *
 * @param object $Model Model from where the method is being executed.
 * @param mixed $id ID of the publishable record.
 * @param $attributes Other fields to change (in the form of field => value)
 * @return boolean Result of the operation.
 */
	function unPublish(&$Model, $id = null, $attributes = array()) {
		if ($Model->hasField($this->__settings[$Model->alias]['field'])) {
			if (empty($id)) {
				$id = $Model->id;
			}

			$data = array($Model->alias => array(
				$Model->primaryKey => $id,
				$this->__settings[$Model->alias]['field'] => false
			));

			if (!empty($attributes)) {
				$data[$Model->alias] = array_merge($data[$Model->alias], $attributes);
			}

			$onFind = $this->__settings[$Model->alias]['find'];
			$this->enablePublishable($Model, false);

			$Model->id = $id;
			$result = $Model->save($data, false, array_keys($data[$Model->alias]));

			$this->enablePublishable($Model, 'find', $onFind);

			return ($result !== false);
		}

		return false;
	}

/**
 * Set if the beforeFind() should be overriden for specific model.
 *
 * @param object $Model Model to be published
 * @param mixed $methods If string, method to enable on, if array array of method names, if boolean, enable it for find method
 * @param boolean $enable If specified method should be overriden.
 */
	function enablePublishable(&$Model, $methods, $enable = true) {
		if (is_bool($methods)) {
			$enable = $methods;
			$methods = array('find');
		}

		if (!is_array($methods)) {
			$methods = array($methods);
		}

		foreach($methods as $method) {
			$this->__settings[$Model->alias][$method] = $enable;
		}
	}

/**
 * Run before a model is about to be find, used only fetch for published records.
 *
 * @param object $Model Model
 * @param array $queryData Data used to execute this query, i.e. conditions, order, etc.
 * @return mixed Set to false to abort find operation, or return an array with data used to execute query
 */
	function beforeFind(&$Model, $queryData, $recursive = null) {

		if (Configure::read('Publishable.disable') === true) {
			return $queryData;
		}

		if ($this->__settings[$Model->alias]['find'] && $Model->hasField($this->__settings[$Model->alias]['field'])) {
			$Db =& ConnectionManager::getDataSource($Model->useDbConfig);
			$include = false;

			if (!empty($queryData['conditions']) && is_string($queryData['conditions'])) {
				$include = true;

				$fields = array(
					$Db->name($Model->alias) . '.' . $Db->name($this->__settings[$Model->alias]['field']),
					$Db->name($this->__settings[$Model->alias]['field']),
					$Model->alias . '.' . $this->__settings[$Model->alias]['field'],
					$this->__settings[$Model->alias]['field']
				);

				foreach($fields as $field) {
					if (preg_match('/^' . preg_quote($field) . '[\s=!]+/i', $queryData['conditions']) ||
						preg_match('/\\x20+' . preg_quote($field) . '[\s=!]+/i', $queryData['conditions'])) {
							
						$include = false;
						break;
					}
				}
			}
			else if (empty($queryData['conditions']) ||
				(!in_array($this->__settings[$Model->alias]['field'], array_keys($queryData['conditions'])) && 
				!in_array($Model->alias . '.' . $this->__settings[$Model->alias]['field'], array_keys($queryData['conditions'])))) {
					
				$include = true;
			}

			if ($include) {
				if (isset($this->__settings[$Model->alias]['field_date']) && $Model->hasField($this->__settings[$Model->alias]['field_date'])) {
					$includeDateCondition = true;
				}
				if (empty($queryData['conditions'])) {
					$queryData['conditions'] = array();
				}

				if (is_string($queryData['conditions'])) {
					$queryData['conditions'] = $Db->name($Model->alias) . '.' . $Db->name($this->__settings[$Model->alias]['field']) . '= 1 AND ' . $queryData['conditions'];
				}
				else {
					$queryData['conditions'][$Model->alias . '.' . $this->__settings[$Model->alias]['field']] = true;
					if (!empty($includeDateCondition)) {
						$queryData['conditions'][$Model->alias . '.' . $this->__settings[$Model->alias]['field_date'] . ' <='] = date('Y-m-d H:i');
					}
				}
			}
			
			if (is_null($recursive) && !empty($queryData['recursive'])) {
				$recursive = $queryData['recursive'];
			} elseif (is_null($recursive)) {
				$recursive = $Model->recursive;
			}

			if ($recursive < 0) {
				return $queryData;
			}

			$associated = $Model->getAssociated('belongsTo');

			foreach ($associated as $m) {
				if ($Model->{$m}->Behaviors->enabled('Publishable')) {
					$queryData = $Model->{$m}->Behaviors->Publishable->beforeFind($Model->{$m}, $queryData, --$recursive);
				}
			}

		}

		return $queryData;
	}

/**
 * Run before a model is saved, used to disable beforeFind() override.
 *
 * @param object $Model Model about to be saved.
 * @return boolean True if the operation should continue, false if it should abort
 */
	function beforeSave(&$Model) {
		if ($this->__settings[$Model->alias]['find']) {
			if (!isset($this->__backAttributes)) {
				$this->__backAttributes = array($Model->alias => array());
			}
			else if (!isset($this->__backAttributes[$Model->alias])) {
				$this->__backAttributes[$Model->alias] = array();
			}

			$this->__backAttributes[$Model->alias]['find'] = $this->__settings[$Model->alias]['find'];
			$this->enablePublishable($Model, false);
		}

		return true;
	}

/**
 * Run after a model has been saved, used to enable beforeFind() override.
 *
 * @param object $Model Model just saved.
 * @param boolean $created True if this save created a new record
 */
	function afterSave(&$Model, $created) {
		if (isset($this->__backAttributes[$Model->alias]['find'])) {
			$this->enablePublishable($Model, 'find', $this->__backAttributes[$Model->alias]['find']);
			unset($this->__backAttributes[$Model->alias]['find']);
		}
	}
}