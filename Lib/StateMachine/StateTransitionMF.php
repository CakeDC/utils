<?php 
class StateTransitionMF {

/**
 * Source state name
 *
 * @var string
 */
    protected $_from;

/**
 * Destination state name
 *
 * @var string
 */
    protected $_to;

/**
 * Transition options
 *
 * @var array
 */
    protected $_options;

/**
 * @param $options
 */
	public function __construct($options) {
		$this->_from = $options['from'];
		$this->_to = $options['to'];
		if (isset($options['guard'])) {
			$this->guard = $options['guard'];
		}
		$this->_options = $options;
	}

/**
 * Transition constructor
 *
 * @param $record
 * @return bool
 */
	public function guard($record) {
		if (isset($this->guard)) {
			return $record->runTransitionAction($this->guard, $this);
		} else {
			return true;
		}
	}

/**
 * Perform Transition
 *
 * @param $record
 * @return bool
 */
	public function perform($record) {
		if (!$this->guard($record)) {
			return false;
		}
		$loopBack = $record->currentState() == $this->_to;
		$states = $record->readStateMachineAttribute('states');
        /** @var $nextState StateMF */
		$nextState = $states[$this->_to];
        /** @var $oldState StateMF */
        $oldState = $states[$record->currentState()];

		if (!$loopBack) {
			$nextState->entering($record);
		}

		$record->set($record->readStateMachineAttribute('state_column'), $this->_to);
		$record->save();

		if (!$loopBack) {
			$nextState->entered($record);
			$oldState->exited($record);
		}
		return true;
	}

/**
 * Compare method
 *
 * @param $obj StateTransitionMF
 * @return bool
 */
	public function cmp($obj) {
		return ($this->_from == $obj->_from) && ($this->_to == $obj->_to);
	}

/**
 *
 * @return mixed
 */
    public function getFrom() {
        return $this->_from;
    }

/**
 * Destination state getter
 *
 * @return mixed
 */
    public function getTo() {
        return $this->_to;
    }

}
