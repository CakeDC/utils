<?php
/**
 * Copyright 2009 - 2014, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2014, Cake Development Corporation (http://cakedc.com)
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
	public $components = array(
		'Session',
		'Auth'
	);

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
 * @return FormPreserverComponent
 */
	public function __construct() {
		parent::__construct();
		$this->redirectMessage = __d('utils', 'Your form data is preserved you\'ll be redirected to it after login.');
	}

/**
 * Intialize Callback
 *
 * @param Controller $Controller
 */
	public function initialize(Controller $Controller) {
		$this->Controller = $Controller;
		$this->sessionPath = $this->sessionKey . '.' . $Controller->name . '.' . $Controller->action;
	}

/**
 * Startup
 *
 * @param Controller $Controller
 * @return void
 */
	public function startUp(Controller $Controller) {
		if (in_array($Controller->action, $this->actions)) {
			if (empty($Controller->request->data) && $this->Session->check($this->sessionPath)) {
				if ($this->directPost == true) {
					$Controller->request->data = $this->Session->read($this->sessionPath);
					$this->Session->delete($this->sessionPath);
				}
			} elseif (!empty($Controller->request->data) && !$this->Auth->user()) {
				$this->preserve($Controller->request->data);
				if (empty($this->loginAction) && !empty($this->Auth->loginAction)) {
					$this->loginAction = $this->Auth->loginAction;
					if (!empty($this->redirectMessage)) {
						$this->Session->setFlash($this->redirectMessage);
					}

					// Code from AuthComponent to store the redirect url so the user get redirected to the correct location after a successful login
					if (isset($Controller->Auth)) {
						$url = '';
						if (isset($Controller->request->params['url']['url'])) {
							$url = $Controller->request->params['url']['url'];
						}
						$url = Router::normalize($url);
						if (!empty($Controller->request->params['url']) && count($Controller->request->params['url']) >= 2) {
							$query = $Controller->request->params['url'];
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
 * @param string $sessionPath
 * @return boolean
 */
	public function preserve($data = null, $sessionPath = null) {
		$this->_overridPath($sessionPath);
		if (isset($data['_Token'])) {
			unset($data['_Token']);
		}
		return $this->Session->write($this->sessionPath, $data);
	}

/**
 * Restores the form data
 *
 * @param string Session path, allows to override the default path to get the form data on other pages manually
 * @return void
 */
	public function restore($sessionPath = null) {
		$this->_overridPath($sessionPath);
		if (empty($this->Controller->request->data) && $this->Session->check($this->sessionPath)) {
			if (!empty($this->Controller->request->data)) {
				$this->Controller->request->data = array_merge($this->Session->read($this->sessionPath), $this->Controller->request->data);
			} else {
				$this->Controller->request->data = $this->Session->read($this->sessionPath);
			}
			$this->Session->delete($this->sessionPath);
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
