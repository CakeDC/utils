<?php
App::uses('EmailErrorHandler', 'Utils.Error');
/**
 * EmailErrorHandler Test Case
 * 
 * Copyright 2007-2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2007-2010, Cake Development Corporation (http://cakedc.com)
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */
class EmailErrorHandlerTest extends CakeTestCase {

/**
 * Fixtures
 *
 * @var array
 */
	public $fixtures = array(
	);

/**
 * setUp method
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
	}

/**
 * tearDown method
 *
 * @return void
 */
	public function tearDown() {
		parent::tearDown();
	}

/**
 * testHandleError
 *
 * @return void
 */
	public function testHandleError() {
		$Mock = $this->getMock('EmailErrorHandler', array('getEmailInstance'));

		Configure::write('Error', array(
			'handler' => 'AppErrorHandler::handleError',
			'level' => E_ALL & ~E_DEPRECATED,
			'trace' => true));

		Configure::write('ErrorHandler', array(
			'receiver' => 'error@localhost.loc',
			'emailNotifications' => true,
			'duration' => 60,
			'codes' => array(E_NOTICE),
			'logLevels' => array(LOG_NOTICE)));

		$Mock->expects($this->once())
			->method('getEmailInstance');

		$Mock->handleError(LOG_NOTICE, 'test');
	}

}
