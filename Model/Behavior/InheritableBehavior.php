<?php
/**
 * This code is an improved version of the Inheritable Behavior published
 * at http://bakery.cakephp.org/articles/view/inheritable-behavior-missing-link-of-cake-model
 * 
 * It was itself based on top of the SubclassBehavior by Eldon Bite <eldonbite@gmail.com>
 * and the ExtendableBehavior class by Matthew Harris which can be found at
 * http://bakery.cakephp.org/articles/view/extendablebehavior 
 * 
 * @author Cake Development Corporation (http://cakedc.com)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License 
 */

/**
 * Utils Plugin
 *
 * Utils Inheritable Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
class InheritableBehavior extends ModelBehavior {

/**
 * Settings of the behavior.
 * Each settings are keyed by Model alias.
 *
 * The available settings keys are:
 * 	- inheritanceField
 * 	- method: STI or CTI
 * 	- fieldAlias
 *
 * @var array
 */
	public $settings = array();

/**
 * Set up the behavior.
 * Finds parent model and determines type field settings
 *
 * @param Model $Model
 * @param array $config Behavior configuration
 * @internal param \Model $model
 * @return void
 */
	public function setup(Model $Model, $config = array()) {
		$_defaults = array(
			'inheritanceField' => 'type',
			'method' => 'STI',
			'fieldAlias' => $Model->alias);
		$this->settings[$Model->alias] = array_merge($_defaults, $config);

		$Model->parent = ClassRegistry::init(get_parent_class($Model));
		$Model->inheritanceField = $this->settings[$Model->alias]['inheritanceField'];
		$Model->fieldAlias = $this->settings[$Model->alias]['fieldAlias'];

		if ($this->settings[$Model->alias]['method'] == 'CTI') {
			$this->classTableBindParent($Model);
			if (!empty($Model->parent->validate)) {
				$Model->validate = Hash::merge($Model->parent->validate, $Model->validate);
			}
		}
	}

/**
 * Before find callback
 * Filter query conditions with the correct `type' field condition.
 *
 * @param Model $model
 * @param array $query Find query
 * @return array Updated query
 */
	public function beforeFind(Model $Model, $query) {
		if ($this->settings[$Model->alias]['method'] == 'STI') {
			$query = $this->_singleTableBeforeFind($Model, $query);
		} else {
			if (!empty($query['recursive']) && $query['recursive'] < 0) {
				unset($query['recursive']);
				$query['contain'] = array($Model->parent->alias);
			}
			if (!empty($query['contain'])) {
				$this->_classTableBindContains($Model, $query['contain']);
				if (empty($query['contain'][$Model->parent->alias]) && !in_array($Model->parent->alias, $query['contain'])) {
					array_unshift($query['contain'], $Model->parent->alias);
				}
			}
		}
		return $query;
	}

/**
 * After find callback
 * In case of CTI inheritance, data contained in the 'ParentAlias' key are merged with Model data
 *
 * @param Model $Model
 * @param array $results
 * @param boolean $primary
 * @return array Results
 */
	public function afterFind(Model $Model, $results = array(), $primary = false) {
		if ($this->settings[$Model->alias] !== 'STI' && !empty($results)) {
			foreach ($results as $i => $res) {
				if (is_int($i)) {
					if (!empty($res[$Model->parent->alias]) && !empty($res[$Model->alias])) {
						$results[$i][$Model->alias] = array_merge($res[$Model->parent->alias], $res[$Model->alias]);
						unset($results[$i][$Model->parent->alias]);
	
					} elseif (!empty($res[$Model->alias][$Model->parent->alias])) {
						$results[$i][$Model->alias] = array_merge($res[$Model->alias][$Model->parent->alias], $res[$Model->alias]);
						unset($results[$i][$Model->alias][$Model->parent->alias]);
	
					} elseif (!empty($res[$Model->alias][0])) {
						foreach($res[$Model->alias] as $j => $subRes) {
							if (isset($subRes[$Model->parent->alias])) {
								$results[$i][$Model->alias][$j] = array_merge($subRes[$Model->parent->alias], $subRes);
								unset($results[$i][$Model->alias][$j][$Model->parent->alias]);
							}
						}
					}
				} elseif ($i == $Model->parent->alias) {
					$results = array_merge($results, $res);
					unset($results[$i]);
				} elseif ($i == $Model->alias && array_key_exists(0, $res)) {
					foreach($res as $j => $payload) {
						if (array_key_exists($Model->parent->alias, $payload)) {
							$results[$i][$j] = array_merge($payload, $payload[$Model->parent->alias]);
							unset($results[$i][$j][$Model->parent->alias]);
						}
					}
				}
			}
		}
		return $results;
	}

/**
 * Before save callback
 * Set the `type' field before saving the record in case of STI model
 *
 * @param Model $Model
 * @return true
 */
	public function beforeSave(Model $Model) {
		if ($this->settings[$Model->alias]['method'] == 'STI') {
			$this->_singleTableBeforeSave($Model);
		} elseif ($this->settings[$Model->alias]['method'] == 'CTI') {
			$this->_saveParentModel($Model);
			$Model->id = $Model->parent->id;
		}
		return true;
	}

/**
 * After delete callback
 * Deletes the parent model in case of CTI model
 *
 * @param Model $Model
 * @return true
 */
	public function afterDelete(Model $Model) {
		if ($this->settings[$Model->alias]['method'] == 'CTI') {
			$Model->parent->delete($Model->id);
		}
		return true;
	}

/**
 * Beforefind callback for STI models
 *
 * @param Model $Model
 * @param array $query Find query
 * @return Updated query
 */
	protected function _singleTableBeforeFind(Model $Model, $query) {
		extract($this->settings[$Model->alias]);
		$_schema = $Model->schema();
		if (isset($_schema[$inheritanceField]) && $Model->alias != $Model->parent->alias) {
			$field = $Model->alias. '.' . $inheritanceField;

			if (!isset($query['conditions'])) {
				$query['conditions'] = array();
			} elseif (is_string($query['conditions'])) {
				$query['conditions'] = array($query['conditions']);
			}

			if (is_array($query['conditions'])) {
				if (!isset($query['conditions'][$field])) {
					$query['conditions'][$field] = array();
				}
				$query['conditions'][$field] = $fieldAlias;
			}
		}

		return $query;
	}

/**
 * BeforeSave callback for STI models
 * Sets the inheritance field to the correct Model alias
 *
 * @param Model $Model
 * @return true
 */
	protected function _singleTableBeforeSave(Model $Model) {
		if (isset($Model->_schema[$Model->inheritanceField]) && $Model->alias != $Model->parent->alias) {
			// May be there is an edge case for this
			if (!isset($Model->data[$Model->alias])) {
				$Model->data[$Model->alias] = array();
			}
			$Model->data[$Model->alias][$Model->inheritanceField] = $Model->alias;
		}
		return true;
	}

/**
 * Binds the parent model for a CTI model
 *
 * @param Model $Model
 * @param array $query
 * @return boolean Success of the binding
 */
	public function classTableBindParent(Model $Model) {
		$bind = array('belongsTo' => array(
			$Model->parent->alias => array(
				'type' => 'INNER',
				'className' => $Model->parent->alias,
				'foreignKey' => $Model->primaryKey)));
		$success = $Model->bindModel($bind, false);
		//Putting the parent association as the first one, so any dependent join on the parent model will
		// be in the right order
		$assoc = $Model->belongsTo[$Model->parent->alias];
		unset($Model->belongsTo[$Model->parent->alias]);
		$Model->belongsTo = array_merge(array($Model->parent->alias => $assoc), $Model->belongsTo);
		return $success;
	}

/**
 * Binds additional belongsTo association from the parent for a CTI model
 *
 * @param Model $Model
 * @param array $binds, additional models to bind. They will be filtered to left only belongsTo associations
 */
	protected function _classTableBindContains(Model $Model, $binds) {
		$assocs = array_flip($Model->parent->getAssociated('belongsTo'));
		foreach ($binds as $k => $alias) {
			if (is_array($alias)) {
				$alias = $k;
			}
			if (isset($assocs[$alias])) {
				$foreignKey = Inflector::underscore($alias) . '_id';
				$bind = array('belongsTo' => array(
					$alias => array(
						'conditions' => "{$Model->parent->alias}.{$foreignKey} = {$alias}.id",
						'foreignKey' => false)));
				$Model->bindModel($bind, true);
			}
		}
	}

/**
 * After save callback for CTI models
 * Saves data for the parent model
 *
 * @param Model $Model
 * @return true
 */
	protected function _saveParentModel(Model $Model) {
		$fields = array_keys($Model->parent->schema());
		$parentData = array($Model->parent->primaryKey => $Model->id);

		foreach ($Model->data[$Model->alias] as $key => $value) {
			if (in_array($key, $fields)) {
				$parentData[$key] = $value;
			}
		}

		$result = $Model->parent->save($parentData, false);
		if ($result !== false) {
			$Model->data[$Model->alias] = Set::merge($Model->data[$Model->alias], $result[$Model->parent->alias]);
		}
		return true;
	}

}
