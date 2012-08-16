<?php
App::uses('ErrorHandler', 'Error');
App::uses('CakeEmail', 'Network/Email');
App::uses('CakeSession', 'Model/Datasource');
App::uses('Cache', 'Cache');
App::uses('Event', 'CakeEvent');
/**
 * EmailErrorHandler
 *
 * To use this error handler you'll have to add this to your apps config:
 *
 * In app/Config/core.php
 * Configure::write('Error.handler ', 'EmailErrorHandler::handleError');
 *
 * In app/Config/bootstrap.php:
 * App::uses('EmailErrorHandler', 'Lib');
 *
 *
 * @link http://book.cakephp.org/2.0/en/development/errors.html#error-handling
 * @link http://book.cakephp.org/2.0/en/development/errors.html#creating-your-own-error-handler
 */
class EmailErrorHandler extends ErrorHandler {
/**
 * HandleError
 *
 * @param integer $code Code of error
 * @param string $description Error description
 * @param string $file File on which error occurred
 * @param integer $line Line that triggered the error
 * @param array $context Context
 * @return boolean true if error was handled
 */
	public static function handleError($code, $description, $file = null, $line = null, $context = null) {
		extract(self::handlerSettings());

		$args = compact('code', 'description', 'file', 'line', 'context', 'session');

		if ($emailNotifications === true && !empty($receiver)) {
			$cacheHash = 'error-' . md5(serialize(compact($args)));
			self::setCacheConfig($duration);

			if (Cache::read($cacheHash, 'error_handler') === false) {
				list($error, $log) = self::mapErrorCode($code);

				if (in_array($log, $logLevels) || in_array($code, $codes)) {
					$trace = Debugger::trace(array('start' => 1, 'format' => 'log'));
					$session = CakeSession::read();
					$server = $_SERVER;
					$request = $_REQUEST;
					$Email = self::getEmailInstance();
					$Email->viewVars(compact('code', 'description', 'file', 'line', 'context', 'session', 'server', 'request', 'trace'));
					$Email->send();
				}

				Cache::write($cacheHash, true, 'error_handler');
			}
		}

		return parent::handleError($code, $description, $file, $line, $context);
	}

/**
 * Sets up the handler settings from the configured value and defaults
 *
 * @return array
 */
	public function handlerSettings() {
		$defaults = array(
			'receiver' => null,
			'emailNotifications' => false,
			'duration' => ini_get('max_execution_time'),
			'codes' => array(),
			'logLevels' => array());
		$config = Configure::read('ErrorHandler');
		if (empty($config)) {
			$config = array();
		}
		return array_merge($defaults, $config);
	}

/**
 * Sets the cache config for the error handle rup
 *
 * @return void
 */
	public function setCacheConfig($duration) {
		Cache::config('error_handler', array(
			'engine' => 'File',
			'duration' => '+' . $duration . ' seconds'));
	}

/**
 * Prepares the CakeEmail instance
 *
 * @return CakeEmail
 */
	public function getEmailInstance() {
		$Email = new CakeEmail();
		$Email->subject(__('Error notification from CakePHP Certification'))
			->from(array('error@' . env('HTTP_HOST') => 'Error Handler'))
			->to(Configure::read('ErrorHandler.receiver'))
			->emailFormat('both')
			->template('Utils.error_notification');
		return $Email;
	}

}