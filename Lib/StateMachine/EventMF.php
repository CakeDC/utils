<?php 

class EventMF extends Object {
/**
 * Event name
 *
 * @var string
 */
    protected $_name;

/**
 * Transition list for this event
 *
 * @var array
 */
    protected $_transitions;

/**
 * Event options
 *
 * @var array
 */
    protected $options;

/**
 * Event constructor
 *
 * @param $name
 * @param $options
 * @param $transitionTable
 * @param $transitions
 */
    public function __construct($name, $options, $transitionTable, $transitions) {
		$this->_name = $name;
		$transitionTable[$this->_name] = array();
		$this->_transitions = $transitionTable[$this->_name];
		foreach ($transitions as $transition => $transitionOptions) {
			$this->transitions($transitionOptions);
		}
		$transitionTable[$this->_name] = $this->_transitions;
		$this->options = $options;
	}

/**
 * Return list of possible next states
 *
 * @param $record
 * @return array of StateTransitionMF
 */
	public function nextStates($record) {
		$result = array();
		foreach ($this->_transitions as $transition) {
			if ($transition->getFrom() == $record->currentState()) {
				$result[] = $transition;
			}
		}
		return $result;
	}

/**
 * Perform transition for model
 *
 * @param Model $record
 * @return bool
 */
	public function fire($record) {
		$nextStates = $this->nextStates($record);
		foreach ($nextStates as $transition) {
            /** @var $transition StateTransitionMF */
			if ($transition->perform($record)) {
				return true;
			}
		}
		return false;
	}

	public function transitions($options) {
		$transition = $options['from'];
		if (!is_array($transition) && is_string($transition)) {
            $transition = array($transition);
		}
		foreach ($transition as $state) {
			$this->_transitions[] = new StateTransitionMF(array_merge($options, array('from' => $state)));
		}
	}

}
