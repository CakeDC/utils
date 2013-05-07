<?php
App::uses('EmailErrorHandler', 'Utils.Error');
App::uses('CakeEmail', 'Network/Email');
/**
 * EmailErrorHandler Test Case
 * 
 * Copyright 2007-2012, Cake Development Corporation (http://cakedc.com)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2009 - 2013, Cake Development Corporation (http://cakedc.com)
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
		$ErrorHandler = $this->getMockClass('EmailErrorHandler', array('getEmailInstance'));
		$ErrorHandler::$Email = $this->getMock('CakeEmail', array('send'));

		Configure::write('Error', array(
			'handler' => array($ErrorHandler, 'handleError'),
			'level' => E_ALL & ~E_DEPRECATED,
			'trace' => true));

		Configure::write('ErrorHandler', array(
			'caching' => false,
			'receiver' => 'error@localhost.loc',
			'emailNotifications' => true,
			'duration' => 60,
			'codes' => array(E_NOTICE),
			'logLevels' => array(LOG_NOTICE)));

		$ErrorHandler::$Email->expects($this->once())
			->method('send')
			->will($this->returnValue(true));

		$ErrorHandler::handleError(E_NOTICE, 'Test error');
	}

}
