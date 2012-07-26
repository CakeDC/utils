<?php

/*
 * StateMachine behavior for CakePHP
 * @author Yevgeny Tomenko aka SkieDr
 * @version 1.0.0.2

 configuration is

 1)
class Person extends AppModel {

	var $name='Person';
	var $actsAs = array(
		'StateMachine' => array (
			'initial' => 'in',
			'column' => 'state',
			'states' => array(
				'in' => array('exit' => 'exitTest'),
				'ready' => array('after'=>'afterTest', 'enter'=>'enterTest'),
				'out' => array(),
			),

			'events' => array(
				'prepare' => array(
					'transitions' => array (
						'to'=>'ready',
						'from'=>array('in', 'out'),
					),
				),
				'close' => array(
					'transitions' => array (
  						'to'=>'out',
						'from'=>array('ready', 'in'),
					),
				),
			)
		),

	);

	function enterTest() {
		debug('enterTest');
		return true;
	}

	function afterTest() {
		debug('afterTest');
		return true;
	}

	function exitTest() {
		debug('exitTest');
		return true;
	}

 */

App::uses('StateTransitionMF', 'Utils.Lib/StateMachine');
App::uses('EventMF', 'Utils.Lib/StateMachine');
App::uses('StateMF', 'Utils.Lib/StateMachine');

class StateMachineBehavior extends ModelBehavior {

/**
 * Behavior settings
 *
 * @var array
 */
	public $settings = null;

/**
 * Map method configuration for this behavior
 *
 * @var array
 */
	public $mapMethods = array();

/**
 * Default configuration for behavior
 *
 * @var array
 */
	protected $_default = array (
		'states' => array(),
//		'transition_table' => array(),
		'events' => array(),
//		'event_table' => array(),
		'initial_state' => 'initial',
		'state_column' => 'state'
	);

/**
 * Perform initial configuration of behavior
 *
 * @param Model $model
 * @param array $config
 * @throws CakeException
 */
	public function setup($model, $config = array()) {
		$settings = $this->_default;

		if (isset($config['column'])) {
			$settings['state_column'] = $config['column'];
		}

		if (isset($config['states'])) {
			if (is_array($config['states'])) {
				$settings['states'] = $config['states'];
			} elseif (is_string($config['states'])) {
				$settings['states'] = $config['states'];
			}
		}
		foreach ($settings['states'] as $state => $opts) {
			$this->state($model, $state, $opts);
			$camelState=Inflector::camelize($state);
			$this->mapMethods["/isState$camelState/"] = 'isState';
			$settings['mapStates']["isState$camelState"] = $state;
		}

		if (!isset($config['initial']) || !in_array($config['initial'],array_keys($settings['states']))) {
            throw new CakeException('(StateMachine::setup) Wrong initial state.');
		} else {
			$settings['initial_state'] = $config['initial'];
		}

		unset($settings['states']);


		if (isset($config['events'])) {
			foreach ($config['events'] as $event => $transitions) {
				$camelEvent = Inflector::camelize($event);
				$this->mapMethods["/setState$camelEvent/"] = 'fire';
				$settings['events'][$event] = $transitions;
				$settings['mapEvents']["setState$camelEvent"] = $event;
				$this->event($model, $event, array(), $transitions);
			}
		}
		$this->settings[$model->alias] = Set::merge($this->settings[$model->alias], $settings);
	}

/**
 * Before save callback
 *
 * @param Model $model
 * @return bool
 */
	public function beforeSave($model) {
		if (!$model->exists() && count(array_keys($model->data[$model->alias]))>0) {
			$this->setInitialState($model);
		}
		return true;
	}

/**
 * After save callback
 *
 * @param Model $model
 * @param bool $created
 * @return bool
 */
	public function afterSave($model, $created) {
		if ($created) {
			$this->runInitialStateActions($model);
		}
		return true;
	}

/**
 * Read model state machine attribute from internal structure
 *
 * @param Model $model
 * @param string $attrName
 * @return mixed
 */
    public function readStateMachineAttribute($model, $attrName) {
		if (isset($this->settings[$model->alias][$attrName])) {
			return $this->settings[$model->alias][$attrName];
		}
	}

/**
 * Store model state machine attribute in internal structure
 * 
 * @param Model $model
 * @param string $attrName
 * @param mixed $value
 */
	protected function _writeStateMachineAttribute($model, $attrName, $value) {
		$this->settings[$model->alias][$attrName] = $value;
	}

/**
 * Configure initial state
 * 
 * @param Model $model
 */    
	public function setInitialState($model) {
		$stateColumn = $this->readStateMachineAttribute($model, 'state_column');
		$initialState = $this->readStateMachineAttribute($model, 'initial_state');
		$model->set($stateColumn, $initialState);
	}

/**
 * Initialize initial state
 * 
 * @param Model $model
 */    
	public function runInitialStateActions($model) {
		$states = $this->readStateMachineAttribute($model, 'states');
		$initialStateName = $this->readStateMachineAttribute($model, 'initial_state');
		$initialState = $states[$initialStateName];
		$initialState->entering($model);
		$initialState->entered($model);
	}

/**
 * Returns the current state title
 *
 * @param Model $model
 * @return mixed
 */
	public function currentState($model) {
		$stateColumn = $this->readStateMachineAttribute($model, 'state_column');
		return $model->data[$model->alias][$stateColumn];
	}

/**
 * Returns what the next state for a given event would be.
 * If there possible several states, first would returned
 *
 * @param Model $model
 * @param $event
 * @return
 */
	public function nextStateForEvent($model, $event) {
		$stateColumn = $this->readStateMachineAttribute($model, 'state_column');
		$nextStates = $this->nextStatesForEvent($model, $event);
		if (!empty($nextStates)) {
			return $nextStates[0]->to;
		}
	}

/**
 * List of allowed states for transition
 * 
 * @param Model $model
 * @param string $event
 * @return array
 */    
	public function nextStatesForEvent($model, $event) {
		$transitionTable = $this->readStateMachineAttribute($model, 'transition_table');
		$result = array();
		foreach ($transitionTable[$event] as $state) {
			if ($state->from == $this->currentState($model)) {
				$result[] = $state;
			}
		}
		return $result;
	}

/**
 * Perform transition if method declared in the model
 *
 * @param $model
 * @param $action
 * @param $state
 * @return bool
 */
	public function runTransitionAction($model, $action, $state) {
		if (method_exists($model, $action)) {
			return $model->{$action}($state);
		}
		return false;
	}


/**
 * Return list of declared states
 *
 * @param $model
 * @return array
 */
	public function states($model) {
		return array_keys($this->readStateMachineAttribute($model, 'states'));
	}

/**
 * Define an event.  This takes a block which describes all valid transitions
 * for this event.
 *
 * @param $model
 * @param $event
 * @param array $options
 * @param $transitions
 */
	public function event($model, $event, $options, $transitions) {
		$transitionTable = $this->readStateMachineAttribute($model, 'transition_table');
		$eventTable = $this->readStateMachineAttribute($model, 'event_table');
		$e = new EventMF($event, $options, $transitionTable, $transitions);
		$eventTable[$event] = $e;
		$this->_writeStateMachineAttribute($model, 'event_table', $eventTable);
		$this->_writeStateMachineAttribute($model, 'transition_table', $transitionTable);
	}

/**
 * Perform transmission to new state
 *
 * @param $model
 * @param $method
 * @return bool|null
 */
	public function fire($model, $method) {
		if (!isset($this->settings[$model->alias]['mapEvents'][$method])){
			return null;
		}
		$eventName = $this->settings[$model->alias]['mapEvents'][$method];
		$eventTable = $this->readStateMachineAttribute($model, 'event_table');
        /** @var $event EventMF */
		$event = $eventTable[$eventName];
		return $event->fire($model);
	}

/**
 * Define a state of the system.
 *
 * @param $model
 * @param $name
 * @param array $options
 */
	public function state($model, $name, $options = array()) {
			$state = new StateMF($name, $options);
			$states = $this->readStateMachineAttribute($model, 'states');
			$states[$name] = $state;
			$this->_writeStateMachineAttribute($model, 'states', $states);
	}

/**
 * Compare current state with specific state
 *
 * @param $model
 * @param $method
 * @return bool
 */
	public function isState($model, $method) {
		if (!isset($this->settings[$model->alias]['mapStates'][$method])) {
			return false;
		}
		$state = $this->settings[$model->alias]['mapStates'][$method];
		return $state == $this->currentState($model);
	}

}
