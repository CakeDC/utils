<?php
App::uses('Component', 'Controller');
App::uses('CakeSession', 'Model\Datasource');

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
 * Flash Message Component
 *
 * @package utils
 * @subpackage utils.controllers.components
 */
class FlashMessageComponent extends Component {

/**
 * Default settings for the Component
 *
 * @var array
 */
	public $defaults = array(
		'flashDefaults' => array(
			'element' => 'default',
			'params' => array(),
			'key' => 'flash',
			'redirect' => array(
				'url' => false,
				'status' => null,
				'exit' => true
			),
		),
	);

/**
 * Default settings for exception flash messages
 *
 * @var array
 */
	public $exceptionDefaults = array();

/**
 * Controller instance reference
 *
 * @var Controller
 */
	public $controller;

/**
 * Initialize
 *
 * @param Controller $controller
 * @throws RuntimeException
 * @return void
 */
	public function initialize(Controller $controller) {
		$this->settings = array_merge($this->defaults, $this->settings);
		$this->controller = $controller;

		if (!isset($this->controller->flashMessages)) {
			throw new \RuntimeException(__d('utils', 'Controller %s is missing the flashMessages property!', $this->controller->name));
		}
	}

/**
 * Triggers a flash message based on an exception
 *
 * @param Exception $Exception
 * @param array $options
 * @return void
 */
	public function exception(Exception $Exception, array $options = array()) {
		$options['message'] = $Exception->getMessage();
		$exceptionClass = get_class($Exception);
		if (isset($this->exceptionDefaults[$exceptionClass])) {
			$options = Hash::merge($this->exceptionDefaults, $options);
		}
		$this->flash($options);
		$this->redirect($options);
	}

/**
 * Displays a flash message based on its config key for the current action
 *
 * @param string $key
 * @param array $options
 * @throws \RuntimeException
 * @return void
 */
	public function show($key = 'default', array $options = array()) {
		if ($key instanceof \Exception) {
			$this->exception($key, $options);
			return;
		}

		if (isset($this->controller->flashMessages[$key])) {
			$flash = $this->controller->flashMessages[$key];
		} else {
			if (!isset($this->controller->flashMessages[$this->controller->action][$key])) {
				throw new \RuntimeException(__d('utils', 'Invalid Flash Message key %s', $key));
			}
			$flash = $this->controller->flashMessages[$this->controller->action][$key];
		}

		$flash = Hash::merge($flash, $options);

		$this->flash($flash);
		$this->redirect($flash);
	}

/**
 * Sets the flash message
 *
 * @param array $flash
 * @throws RuntimeException
 * @return void
 */
	public function flash(array $flash = array()) {
		$flash = $this->_beforeFlash(Hash::merge($this->settings['flashDefaults'], $flash));

		if (isset($flash[0]) && is_string($flash[0])) {
			$flash['message'] = $flash[0];
			unset($flash[0]);
		}

		if (!isset($flash['message'])) {
			throw new \RuntimeException(__d('utils', 'No flash message text set!'));
		}

		if (!$this->controller->request->is('ajax')) {
			$this->setFlash($flash['message'], $flash['element'], $flash['params'], $flash['key']);
		} else {
			$this->setData($flash);
		}
	}

/**
 * Redirect
 *
 * @param array $flash
 * @param bool $returnUrl
 * @return mixed
 */
	public function redirect(array $flash, $returnUrl = false) {
		if (!isset($flash['redirect']) || $flash['redirect'] === false || $flash['redirect']['url'] === false) {
			return false;
		}

		$redirect = $flash['redirect'];
		if (is_string($redirect)) {
			$redirect = array(
				'url' => $redirect,
				'status' => null,
				'exit' => true
			);
		}

		if ($returnUrl === true) {
			return $this->_url($redirect['url']);
		}

		$this->controller->redirect($redirect['url'], $redirect['status'], $redirect['exit']);
	}

/**
 * Sets the flash data to the view in the case of json or xml responses
 *
 * @param array $flash
 * @return void
 */
	public function setData($flash) {
		$this->controller->set('flashData', array(
			'flash' => $flash,
			'redirect' => $this->redirect($flash, true)
		));
	}

/**
 * Writes the flash mesage to the session
 *
 * @param string $message
 * @param string $element
 * @param array $params
 * @param string $key
 * @return void
 */
	public function setFlash($message, $element = 'default', $params = array(), $key = 'flash') {
		CakeSession::write('Message.' . $key, compact('message', 'element', 'params'));
	}

/**
 * Returns an absolute URL
 *
 * @param string $url
 */
	protected function _url($url) {
		Router::url($url, true);
	}

/**
 * beforeFlash callback
 *
 * @param array $flash
 * @return array
 */
	protected function _beforeFlash(array $flash) {
		if (method_exists($this->controller, 'beforeFlash')) {
			return (array)$this->controller->beforeFlash($flash);
		}
		return $flash;
	}

}