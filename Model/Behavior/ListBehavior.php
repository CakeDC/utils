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
 * Utils List Behavior
 *
 * @package utils
 * @subpackage utils.models.behaviors
 */
class ListBehavior extends ModelBehavior {

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
		'positionColumn' => 'position',
		'scope' => '',
		'validate' => false,
		'addToTop' => false,
		'callbacks' => false
	);

/**
 * Setup
 *
 * @param Model $model
 * @param array $config
 */
	public function setup(Model $model, $config = array()) {
		$settings = array_merge($this->_defaults, $config);
		$this->settings[$model->alias] = $settings;
	}

/**
 * Before save method. Called before all saves
 *
 * Overriden to transparently manage setting the item position to the end of the list
 *
 * @param Model $model
 * @param array $options
 * @return boolean True to continue, false to abort the save
 */
	public function beforeSave(Model $model, $options = array()) {
		extract($this->settings[$model->alias]);
		if (empty($model->data[$model->alias][$model->primaryKey])) {
			if ($addToTop) {
				$this->__addToListTop($model);
			} else {
				$this->__addToListBottom($model);
			}
		}
		return true;
	}

/**
 * Before delete method. Called before all deletes
 *
 * Will delete the current item from list and update position of all items after one
 *
 * @param Model $model
 * @param boolean $cascade
 * @return boolean True to continue, false to abort the delete
 */
	public function beforeDelete(Model $model, $cascade = true) {
		$dataStore = $model->data;
		$model->recursive = 0;
		$model->read(null,$model->id);
		extract($this->settings[$model->alias]);
		$result = $this->removeFromList($model);
		$model->data = $dataStore;
		return $result;
	}

/**
 *  Set new position of selected item for model
 *
 * @param Model $model
 * @param int $position new position of item in list
 * @param string $id  - value of model primary key to read
 * @return bool
 */
	public function insertAt(Model $model, $position = 1, $id = null) {
		if (!$this->__setById($model, $id, false)) {
			return false;
		}
		return $this->__insertAtPosition($model, $position);
	}

/**
 * Swap positions with the next lower item, if one exists.
 *
 * @param Model $model
 * @param ID $id  - value of model primary key to read
 * @return bool
 */
	public function moveLower(Model $model, $id = null) {
		if (!$this->__setById($model, $id)) {
	 		return false;
		}
		$lowerItem = $this->lowerItem($model);
		if ($lowerItem == null) {
			return true;
		}

		/* @todo: add transaction */
		$currData = $model->data;
		$model->set($lowerItem);
		$this->_decrementPosition($model);
		$model->set($currData);
		return $this->_incrementPosition($model);
		/* @todo: add transaction */
	}
	public function moveDown(Model $model, $id = null) {
		return $this->moveLower($model, $id);
	}

/**
 * Swap positions with the next higher item, if one exists.
 *
 * @param Model $model
 * @param string $id UUID value of model primary key
 * @return bool
 */
	public function moveHigher(Model $model, $id = null) {
		if (!$this->__setById($model, $id)) {
			return false;
		}
		$higherItem = $this->higherItem($model);
		if ($higherItem == null) {
			return true;
		}

		/* @todo: add transaction */
		$currData = $model->data;
		$model->set($higherItem);
		$this->_incrementPosition($model);
		$model->set($currData);
		return $this->_decrementPosition($model);
		/* @todo: add transaction */
	}

/**
 * Move Up
 *
 * @param string $model 
 * @param string $id 
 * @return bool
 */
	public function moveUp(Model $model, $id = null) {
		return $this->moveHigher($model, $id);
	}

/**
 * Move to the bottom of the list. If the item is already in the list, the items below it have their
 * position adjusted accordingly.
 *
 * @param Model $model
 * @param string $id UUID value of model primary key
 * @return bool
 */
	public function moveToBottom( Model$model, $id = null) {
		if (!$this->__setById($model, $id)) {
			return false;
		}
		if (!$this->isInList($model)) {
			return;
		}
		/* @todo: add transaction */
		$this->__decrementPositionsOnLowerItems($model);
		return $this->__assumeBottomPosition($model);
		/* @todo: add transaction */
	}

/**
 * Move to the top of the list. If the item is already in the list, the items above it have their
 * position adjusted accordingly.
 *
 * @param Model $model
 * @param string $id  - value of model primary key to read
 * @return bool
 */
	 public function moveToTop(Model $model, $id = null) {
		if (!$this->__setById($model, $id)) {
			return false;
		}
		if (!$this->isInList($model)) {
			return;
		}
		/* @todo: add transaction */
		$this->__incrementPositionsOnHigherItems($model);
		return $this->__assumeTopPosition($model);
		/* @todo: add transaction */
	}

/**
 * Removes an item from the list
 * @param Model $model
 * @param string $id UUID
 * @return mixed
 */
	public function removeFromList(Model $model, $id = null) {
		if (!$this->__setById($model, $id)) {
			return false;
		}
		if ($this->isInList($model)) return $this->__decrementPositionsOnLowerItems($model);
	}

/**
 * Return true if this object is the first in the list.
 *
 * @param Model $model
 * @param string $id
 * @return bool
 */
	public function isFirst(Model $model, $id = null) {
		if (!$this->__setById($model, $id)) {
			return false;
		}
		extract($this->settings[$model->alias]);
		if (!$this->isInList($model)) {
			return false;
		}
		return $model->data[$model->alias][$positionColumn] == 1;
	}

/**
 * Check if the item is the last on in the list
 *
 * @param Model $model
 * @param string $id UUID
 * @return boolean return true if this object is the last in the list.
 */
	public function isLast(Model $model, $id = null) {
		if (!$this->__setById($model, $id)) {
			return false;
		}
		extract($this->settings[$model->alias]);
		if (!$this->isInList($model)) {
			return false;
		}
		return $model->data[$model->alias][$positionColumn] == $this->__bottomPositionInList($model);
	}

/**
 * Return the next higher item in the list
 *
 * @param Model $model
 * @param string $id UUID
 * @return array
 */
	public function higherItem(Model $model, $id = null) {
		if (!$this->__setById($model, $id)) {
			return false;
		}
		extract($this->settings[$model->alias]);
		if (!$this->isInList($model)) {
			return null;
		}
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		return $model->find('first', array('conditions' => array($this->__scopeCondition($model), $model->alias . '.' . $positionColumn => $model->data[$model->alias][$positionColumn] - 1), 'recursive' => 0));
	}

/**
 * Return the next lower item in the list.
 *
 * @param Model $model
 * @param string $id UUID
 * @return mixed
 */
	public function lowerItem(Model $model, $id = null) {
		if (!$this->__setById($model, $id)) {
			return false;
		}
		extract($this->settings[$model->alias]);
		if (!$this->isInList($model)) {
			return null;
		}
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		return $model->find('first', array('conditions' => array($this->__scopeCondition($model), $model->alias . '.' . $positionColumn => $model->data[$model->alias][$positionColumn] + 1), 'recursive' => 0));
	}

/**
 * Return true if item in the list.
 *
 * @param Model $model
 * @return bool
 */
	public function isInList($model) {
		extract($this->settings[$model->alias]);
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		if (empty($model->data[$model->alias][$positionColumn])) {
			return false;
		}
		return !($model->data[$model->alias][$positionColumn] == null);
	}

/**
 * Repair list method
 *
 * @param object AppModel
 * @return boolean
 */
	public function fixListOrder($model) {
		extract($this->settings[$model->alias]);
		$data = $model->find('all', array(
			'conditions' => $this->__scopeCondition($model),
			'order' => array($model->alias . '.' . $positionColumn => 'asc'),
			'recursive' => -1));
		$position = 1;
		foreach ($data as $row) {
			$model->id = $row[$model->alias][$model->primaryKey];
			$model->saveField($positionColumn, $position, array(
			'validate' => $validate,
			'callbacks' => $callbacks));
			$position += 1;
		}
	}

/**
 * Increase the position of this item without adjusting the rest of the list.
 *
 * @param Model $model
 * @return mixed
 */
	protected function _incrementPosition($model) {
		if (!$this->isInList($model)) {
			return;
		}
		extract($this->settings[$model->alias]);
		$model->data[$model->alias][$positionColumn]++;
		return $model->save(null, array(
			'validate' => $validate,
			'callbacks' => $callbacks));
	}

/**
 * Decrease the position of this item without adjusting the rest of the list.
 *
 * @param Model $model
 * @return mixed
 */
	protected function _decrementPosition($model) {
		if (!$this->isInList($model)) {
			return;
		}
		extract($this->settings[$model->alias]);
		$model->data[$model->alias][$positionColumn]--;
		return $model->save(null, array(
			'validate' => $validate,
			'callbacks' => $callbacks));
	}

/**
 * Add aditional conditions to make scope of list.
 *
 * @param Model $model
 * @return array
 */
   private function __scopeCondition($model) {
		extract($this->settings[$model->alias]);
		$scopes = array();
		if (is_string($scope)) {
			if ($scope=='') {
				return $scopes;
			}
			$scopes[$model->alias . '.' . $scope] = $model->data[$model->alias][$scope];
		} elseif (is_array($scope)) {
			foreach ($scope as $k => $v) {
				if (is_numeric($k)) {
					$scopeEl = $v;
					$v = $model->data[$model->alias][$scopeEl];
				} else {
					$scopeEl = $k;
				}
				$scopes[$model->alias . '.' . $scopeEl] = $v;
			}
		}
		return $scopes;
	}

/**
 * Add to list top
 *
 * @param Model $model 
 * @return mixed
 */
	private function __addToListTop($model) {
		return $this->__incrementPositionsOnAllItems($model);
	}

/**
 * Add to list bottom
 *
 * @param Model $model 
 * @return mixed
 */
	private function __addToListBottom($model) {
		extract($this->settings[$model->alias]);
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		$model->data[$model->alias][$positionColumn] = $this->__bottomPositionInList($model) + 1;
	}

/**
 * Bottom position in list
 *
 * @param Model $model 
 * @param string $except 
 * @return void
 */
	private function __bottomPositionInList(Model $model, $except = null) {
		extract($this->settings[$model->alias]);
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		$item = $this->__bottomItem($model, $except);
		if (!empty($item) && isset($item[$model->alias][$positionColumn])) {
			return $item[$model->alias][$positionColumn];
		} else {
			return 0;
		}
	}

/**
 * Bottom Item
 *
 * @param Model $model 
 * @param string $except 
 * @return mixed
 */
	private function __bottomItem(Model $model, $except = null) {
		extract($this->settings[$model->alias]);
		$conditions = $this->__scopeCondition($model);
		if (is_string($conditions)) {
			$conditions = array($conditions);
		}
		if ($except != null) {
			$conditions = array_merge($conditions, array($model->alias . '.' . $model->primaryKey . ' != ' => $except[$model->alias][$model->primaryKey]));
		}
		$model->recursive = 0;
		$options = array(
			'conditions' => $conditions,
			'order' => array($model->alias . '.' . $positionColumn => 'DESC'));
		return $model->find('first', $options);
	}

/**
 * Assume Bottom position
 *
 * @param Model $model 
 * @return boolean
 */
	private function __assumeBottomPosition($model) {
		extract($this->settings[$model->alias]);
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		$model->data[$model->alias][$positionColumn] = $this->__bottomPositionInList($model, $model->data)+1;
		return $model->save(null, array(
			'validate' => $validate,
			'callbacks' => $callbacks));
	}

/**
 * Pre-check for first list record
 *
 * @param Model $model 
 * @return boolean
 */
	private function __assumeTopPosition($model) {
		extract($this->settings[$model->alias]);
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		$model->data[$model->alias][$positionColumn] = 1;
		return $model->save(null, array(
			'validate' => $validate,
			'callbacks' => $callbacks));
	}

/**
 * This has the effect of moving all the higher items up one.
 *
 * @param Model $model
 * @param integer $position
 * @return boolean
 */
	private function __decrementPositionsOnHigherItems(Model $model, $position) {
		extract($this->settings[$model->alias]);
		return $model->updateAll(
			array($model->alias . '.' . $positionColumn => $model->alias . '.' . $positionColumn . '-1'),
			array($this->__scopeCondition($model), $model->alias . '.' . $positionColumn . ' <= ' => $position)
		);
    }

/**
 * This has the effect of moving all the lower items up one
 *
 * @param object AppModel
 * @return boolean
 */
	private function __decrementPositionsOnLowerItems($model) {
		if (!$this->isInList($model)) return;
		extract($this->settings[$model->alias]);
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		return $model->updateAll(
			array($model->alias . '.' . $positionColumn => $model->alias . '.' . $positionColumn . ' - 1'),
			array($this->__scopeCondition($model), $model->alias . '.' . $positionColumn . ' > ' =>  $model->data[$model->alias][$positionColumn])
		);
    }

/**
 * This has the effect of moving all the higher items down one.
 *
 * @param object AppModel
 * @return boolean
 */
	private function __incrementPositionsOnHigherItems($model) {
		if (!$this->isInList($model)) return;
		extract($this->settings[$model->alias]);
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		return $model->updateAll(
			array($model->alias . '.' . $positionColumn => $model->alias . '.' . $positionColumn . '+1'),
			array($this->__scopeCondition($model), $model->alias . '.' . $positionColumn . ' < ' => $model->data[$model->alias][$positionColumn])
		);
    }

/**
 * Moves all lower items one position down
 *
 * @param object AppModel
 * @param integer
 * @return boolean
 */
	private function __incrementPositionsOnLowerItems(Model $model, $position) {
		extract($this->settings[$model->alias]);
		$positionColumn = $this->settings[$model->alias]['positionColumn'];
		return $model->updateAll(
			array($model->alias . '.' . $positionColumn => $model->alias . '.' . $positionColumn . '+1'),
			array($this->__scopeCondition($model), $model->alias . '.' . $positionColumn . ' >= ' => $position)
		);
	}

/**
 * Increments the position on all items
 *
 * @param object AppModel
 * @return boolean
 */
	private function __incrementPositionsOnAllItems($model) {
		extract($this->settings[$model->alias]);
		return $model->updateAll(
			array($model->alias . '.' . $positionColumn => $model->alias . '.' . $positionColumn . '+1'),
			array($this->__scopeCondition($model))
		);

    }

/**
 * Inserts an item on a certain position
 *
 * @param Model $model
 * @param $position
 * @return boolean
 */
	private function __insertAtPosition(Model $model, $position) {
		extract($this->settings[$model->alias]);

		$data = $model->data;
		$model->data[$model->alias][$positionColumn] = 0;
		$model->save(null, array(
			'validate' => $validate,
			'callbacks' => $callbacks));
		$model->create($data);

		$model->recursive = 0;
		$model->findById($model->id);
		$this->removeFromList($model);
		$result = $this->__incrementPositionsOnLowerItems($model, $position);
		if ($position <= $this->__bottomPositionInList($model) + 1) {
			$model->data[$model->alias][$positionColumn] = $position;
			$result = $model->save(null, array(
				'validate' => $validate,
				'callbacks' => $callbacks));
		}
		return $result;
	}

/**
 *  SetById method. Check is model innitialized.
 *
 *  If $id is defined read record from model with this primary key value
 *
 * @param Model $model
 * @param string $id  - value of model primary key to read
 * @param bool $checkId
 * @return boolean True if model initialized, false if no info in $model->data exists.
 */
	private function __setById(Model $model, $id = null, $checkId = true) {
		if (!isset($id)) {
			if ($checkId) {
				return isset($model->data[$model->alias][$model->primaryKey]);
			} else {
				return isset($model->data[$model->alias]);
			}
		} else {
			return $model->read(null, $id);
		}
	}

}
