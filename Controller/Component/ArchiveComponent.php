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
 * Utils Archive Component
 *
 * @package utils
 * @subpackage utils.controllers.components
 */
class ArchiveComponent extends Object {

/**
 * Date parameters to find
 *
 * @var array
 */
	protected $_parameters = array(
		'year', 'month', 'day');

/**
 * Controller reference
 *
 * @var object
 */
	public $controller = null;

/**
 * Name of model
 *
 * Customizable in beforeFilter(), or default controller's model name is used
 *
 * @var string
 */
	public $modelName = null;

/**
 * DateField
 *
 * @var string
 */
	public $dateField = 'created';

/**
 * Startup method, checks request for required parameters
 *
 * Builds the pagination conditions
 *
 * @param object
 */
	public function startup(&$controller) {
		$this->controller =& $controller;
		if (empty($this->modelName)) {
			$this->modelName = $controller->modelClass;
		}
		$parsedParams = array();

		foreach ($this->_parameters as $param) {
			if (isset($controller->params[$param]) && is_numeric($controller->params[$param])) {
				$parsedParams[$param] = $controller->params[$param];
			}
		}
		if (empty($parsedParams)) {
			return false;
		}

		if (method_exists($controller->{$this->modelName}, 'buildArchiveConditions')) {
			$archiveConditions = $controller->{$this->modelName}->buildArchiveConditions($parsedParams);
		} else {
			$archiveConditions = $this->_buildArchiveConditions($parsedParams);
		}

		$paginate = array();
		if (!empty($controller->paginate[$this->modelName])) {
			$paginate = $controller->paginate[$this->modelName];
		}
		if (isset($paginate['conditions'])) {
			$paginate['conditions'] = array_merge($paginate['conditions'], $archiveConditions);
		} else {
			$paginate['conditions'] = $archiveConditions;
		}
		$controller->paginate[$this->modelName] = $paginate;
		return true;
	}

/**
 * Create an array that indicates which year/month combinations have elements in them
 *
 * @param array $conditions Array of conditions to use on $this->modelName when doing the find
 * @return mixed either false on missing modelName or array of year/month combos
 */
	public function archiveLinks($conditions = array()) {
		$modelName = $this->modelName;
		$defaults = array(
			'order' => array("{$modelName}.$this->dateField" => 'DESC'),
			'fields' => array("{$modelName}.$this->dateField", "COUNT(*) AS month_count"),
			'conditions' => array(),
			'group' => array(
				"MONTH({$modelName}.$this->dateField)",
				"YEAR({$modelName}.$this->dateField)",
			),
			'limit' => 10
		);

		$conditions = Set::merge($defaults, $conditions);

		$elements = $this->controller->{$modelName}->find('all', $conditions);
		$dates = array();
		foreach ($elements as $element) {
			$date = $element[$modelName][$this->dateField];
			$year = date('Y', strtotime($date));
			$month = date('m', strtotime($date));
			$count = $element[0]['month_count'];
			$dates[] = compact('year', 'month', 'count');
		}
		return $dates;
	}

/**
 * Build conditions based on the date passed in an url
 *
 * Default construction of date based parameters for archive pagination
 * Can be overloaded in a model by implementing buildArchiveConditions()
 *
 * @param array
 */
	protected function _buildArchiveConditions($dateParams) {
		$duration = '1 month';
		extract($dateParams, EXTR_SKIP);
		if (!isset($year)) {
			$year = date('Y');
			$duration = '1 year';
		}
		if (!isset($month) || $month > 12 || $month < 1) {
			$month = '01';
			$duration = '1 year';
		}
		if (!isset($day) || $day > 31 || $day < 1) {
			$day = '01';
		} else {
			$duration = '2 days';
		}

		$startDate = sprintf('%s-%s-%s', $year, $month, $day);
		if (strtotime($startDate) > time()) {
			$this->cakeError('error', array(
				'name' => sprintf(__d('utils', 'No %s found for that date range'), Inflector::humanize(Inflector::pluralize($this->modelName))),
				'message' => $this->controller->here,
				'code' => 404,
			));
			$this->_stop();
		}
		$endDatetime = new DateTime($startDate);
		$endDatetime->modify($duration);
		$endDatetime->modify('-1 day');
		$endDate = $endDatetime->format('Y-m-d');
		$field = $this->modelName . '.' . $this->dateField .' BETWEEN ? AND ?';
		return array($field => array($startDate, $endDate));
	}

}
