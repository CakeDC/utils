<?php
App::uses('Controller', 'Controller');
App::uses('SessionComponent', 'Controller/Component');
App::uses('FlashMessageComponent', 'Utils.Controller/Component');

class FlashMessageComponentTestController extends Controller {

/**
 * Components
 *
 * @var array
 */
	public $components = array(
		'Utils.FlashMessage',
	);

	public $flashMessages = array(
		'testAction' => array(
			'test1' => 'First message'
		),
		'testAction2' => array(
			'test1' => array(
				'message' => 'Second message',
				'element' => 'testElement'
			)
		),
		'testAction3' => array(
			'test1' => array(
				'message' => 'Third Message',
				'redirect' => array(
					'url' => array(
						'controller' => 'tests',
						'action' => 'index'
					)
				)
			)
		)
	);

}

class FlashMessageComponentTest extends CakeTestCase {

/**
 * setUp
 *
 * @return void
 */
	public function setUp() {
		$this->Request = $this->getMock(
			'CakeRequest',
			array('is'));
		$this->Response = new CakeResponse();
		$this->Controller = $this->getMock(
			'FlashMessageComponentTestController',
			array('redirect'),
			array($this->Request, $this->Response));
		$this->Controller->constructClasses();
		$this->Controller->FlashMessage->initialize($this->Controller, array());
		$this->Controller->FlashMessage->Session = $this->getMock(
			'SessionComponent',
			array(),
			array($this->Controller->Components));
	}

/**
 * tearDown
 *
 * @return void
 */
	public function tearDown() {
		unset($this->Request);
		unset($this->Response);
		unset($this->Controller);
	}

/**
 * testShow
 *
 * @return void
 */
	public function testShow() {
		$this->Controller->FlashMessage->Session->expects($this->
			at(0))->
			method('setFlash')->
			with('First message', 'default', array(), 'flash');

		$this->Controller->action = 'testAction';
		$this->Controller->FlashMessage->show('test1');

		$this->Controller->FlashMessage->Session->expects($this->
			at(0))->
			method('setFlash')->
			with('Second message', 'testElement', array(), 'flash');

		$this->Controller->action = 'testAction2';
		$this->Controller->FlashMessage->show('test1');
	}

/**
 * RunTimeExceptionMissingFlashMessageProperty
 *
 * @expectedException \RuntimeException
 * @return void
 */
	public function testRunTimeExceptionMissingFlashMessageProperty() {
		unset($this->Controller->flashMessages);
		$this->Controller->action = 'testAction';
		$this->Controller->FlashMessage->show('test1');
	}

/**
 * RunTimeExceptionMissingFlashMessageProperty
 *
 * @expectedException \RuntimeException
 * @return void
 */
	public function testRunTimeExceptionMissingFlashMessageActionKey() {
		unset($this->Controller->flashMessages['testAction']['test1']);
		$this->Controller->action = 'testAction';
		$this->Controller->FlashMessage->show('test1');
	}

/**
 * testShowWithExceptionArgument
 *
 * @return void
 */
	public function testShowWithExceptionArgument() {
		$Exception = new NotFoundException('Record not found!');
		$this->Controller->FlashMessage->Session->expects($this->
			at(0))->
			method('setFlash')->
			with('Record not found!', 'default', array(), 'error');

		$this->Controller->action = 'testAction2';
		$this->Controller->FlashMessage->show($Exception, array('key' => 'error'));
	}

/**
 * testFlashAjax
 *
 * @return void
 */
	public function testFlashAjax() {
		$this->Controller->request->expects(
			$this->at(0))->
			method('is')->
			with('ajax')->
			will($this->returnvalue(true));
		$this->Controller->action = 'testAction2';
		$this->Controller->FlashMessage->show('test1');
		$this->assertEqual($this->Controller->viewVars, array(
			'flashData' => array(
				'flash' => array(
					'element' => 'testElement',
					'params' => array(),
					'key' => 'flash',
					'redirect' => array(
						'url' => false,
						'status' => null,
						'exit' => true
					),
					'message' => 'Second message'
				),
				'redirect' => false
			)
		));
	}

}