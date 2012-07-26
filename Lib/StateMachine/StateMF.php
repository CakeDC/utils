<?php 
class StateMF {

/**
 * State name
 *
 * @var string
 */
    protected $_name;

/**
 * State options
 *
 * @var array
 */
    protected $_options;

/**
 * State constructor
 *
 * @param $name
 * @param $options
 */
	public function __construct($name, $options) {
		$default = array (
		'enter' => false,
		'after' => false,
		'exit' => false);
		$this->_name = $name;
		$this->_options = Set::merge($default, $options);
	}

/**
 * Event triggered before object entered into new state
 *
 * @param $record Model
 */
	public function entering($record) {
		$enterAction = $this->_options['enter'];
		if (isset($this->_options['enter'])) {
			$record->runTransitionAction($enterAction, $this);
		}
	}

/**
 * Event triggered after object entered into new state
 *
 * @param $record Model
 * @return mixed
 */
	public function entered($record) {
		$afterActions = $this->_options['after'];
		if (!$afterActions) {
			return;
		}
		if (!is_array($afterActions)) {
			$afterActions = array($afterActions);
		}
		foreach ($afterActions as $afterAction) {
			$record->runTransitionAction($afterAction, $this);
		}
	}

/**
 * Event triggered after object leave state
 *
 * @param $record Model
 */
	public function exited($record) {
		$exitAction = $this->_options['exit'];
		if (isset($this->_options['exit'])) {
			$record->runTransitionAction($exitAction, $this);
		}
	}

/**
 * State Name getter
 *
 * @return mixed
 */
    public function getName() {
        return $this->_name;
    }
}
