<?php
App::uses('Component', 'Controller');

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
 * Controller instance reference
 *
 * @var object
 */
	public $controller;

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Session',
	);

/**
 * Initialize
 *
 * @param Controller $controller
 * @return void
 */
	public function initialize(Controller $controller) {
		$this->settings = array_merge($this->defaults, $this->settings);
		$this->controller = $controller;
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
		} else {
			if (!isset($this->controller->flashMessages)) {
				throw new \RuntimeException(__d('utils', 'Controller %s is missing the flashMessages property!', $this->controller->name));
			}

			if (!isset($this->controller->flashMessages[$this->controller->action][$key])) {
				throw new \RuntimeException(__d('utils', 'Invalid Flash Message key %s', $key));
			}
		}

		$flash = Set::merge($this->controller->flashMessages[$this->controller->action][$key], $options);

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
		$flash = $this->_beforeFlash(Set::merge($this->settings['flashDefaults'], $flash));

		if (isset($flash[0]) && is_string($flash[0])) {
			$flash['message'] = $flash[0];
			unset($flash[0]);
		}

		if (!isset($flash['message'])) {
			throw new \RuntimeException(__d('utils', 'No flash message text set!'));
		}

		if (!$this->controller->request->is('ajax')) {
			$this->Session->setFlash($flash['message'], $flash['element'], $flash['params'], $flash['key']);
		} else {
			$this->_setData($flash);
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

		if ($returnUrl === true) {
			return $this->_url($flash['url']);
		}

		$this->controller->redirect($flash['url'], $flash['status'], $flash['exit']);
	}

/**
 *
 */
	protected function _setData($flash) {
		$this->controller->set('json', array(
			'flash' => $flash,
			'redirect' => $this->redirect($flash, true)
		));
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