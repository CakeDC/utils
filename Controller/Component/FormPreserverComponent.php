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
 * Utils Form Preserver Component
 *
 * @package utils
 * @subpackage utils.controllers.components
 */
class FormPreserverComponent extends Object {
/**
 * Components that are required
 *
 * @var array $components
 */
	public $components = array('Session', 'Auth');

/**
 * Actions used to fetch the post data
 *
 * @var array actions
 */
	public $actions = array();

/**
 * Session key
 *
 * @var string
 * @accesss public
 */
	public $sessionKey = 'PreservedForms';

/**
 * Session path
 *
 * Generates a path like PreservedForms.Controller.action
 *
 * @var string
 */
	public $sessionPath = null;
/**
 * Flash message for the redirect
 *
 * @var string Message to be shown in the flash message
 */
	public $redirectMessage = null;

/**
 * @var mixed Array or string URL syntax
 */
	public $loginRedirect = null;

/**
 * Directly post the data after a user logged in or not
 *
 * @var boolean If true the data will be directly posted, if not the form appears filled with the preserved data. Default is false.
 */
	public $directPost = false;

/**
 * Constructor
 *
 * @return void
 */
	public function __construct() {
		parent::__construct();
		$this->redirectMessage = __d('utils', 'Your form data is preserved you\'ll be redirected to it after login.');
	}

/**
 * Intialize Callback
 *
 * @param object Controller object
 */
	public function initialize(&$Controller) {
		$this->Controller = $Controller;
		$this->sessionPath = $this->sessionKey . '.' . $Controller->name . '.' . $Controller->action;
	}

/**
 * Startup
 *
 * @param object Controller instance
 * @return void
 */

	public function startUp(&$Controller) {
		if (in_array($Controller->action, $this->actions)) {
			if (empty($Controller->data) && $Controller->Session->check($this->sessionPath)) {
				if ($this->directPost == true) {
					$Controller->data = $Controller->Session->read($this->sessionPath);
					$Controller->Session->delete($this->sessionPath);
				}
			} elseif (!empty($Controller->data) && !$Controller->Auth->user()) {
				$this->preserve($Controller->data);
				if (empty($this->loginAction) && !empty($Controller->Auth->loginAction)) {
					$this->loginAction = $Controller->Auth->loginAction;
					if (!empty($this->redirectMessage)) {
						$Controller->Session->setFlash($this->redirectMessage);
					}

					// Code from AuthComponent to store the redirect url so the user get redirected 
					// to the correct location after a successful login
					if (isset($Controller->Auth)) {
						$url = '';
						if (isset($Controller->params['url']['url'])) {
							$url = $Controller->params['url']['url'];
						}
						$url = Router::normalize($url);
						if (!empty($Controller->params['url']) && count($Controller->params['url']) >= 2) {
							$query = $Controller->params['url'];
							unset($query['url'], $query['ext']);
							$url .= Router::queryString($query, array());
						}
						$this->Session->write('Auth.redirect', $url);
					}

					$Controller->redirect($this->loginAction);
				}
			}
		}
	}

/**
 * Preserves the form data in a session
 *
 * @param array Data from Controller->data
 * @return boolean
 */
	public function preserve($data = null, $sessionPath = null) {
		$this->_overridPath($sessionPath);
		if (isset($data['_Token'])) {
			unset($data['_Token']);
		}
		return $this->Controller->Session->write($this->sessionPath, $data);
	}

/**
 * Restores the form data
 *
 * @param string Session path, allows to override the default path to get the form data on other pages manually
 * @return void
 */
	public function restore($sessionPath = null) {
		$this->_overridPath($sessionPath);
		if (empty($this->Controller->data) && $this->Controller->Session->check($this->sessionPath)) {
			if (!empty($this->Controller->data)) {
				$this->Controller->data = array_merge($this->Controller->Session->read($this->sessionPath), $this->Controller->data);
			} else {
				$this->Controller->data = $this->Controller->Session->read($this->sessionPath);
			}
			$this->Controller->Session->delete($this->sessionPath);
		}
	}

/**
 * Overrides the session path
 *
 * @param string
 * @return void
 */
	protected function _overridPath($sessionPath = null) {
		if (!empty($sessionPath)) {
			$this->sessionPath = $this->sessionKey . '.' . $sessionPath;
		}
	}

/**
 * beforeRender callback
 *
 * @return void
 */
	public function beforeRender() {
		$this->restore();
	}

}
