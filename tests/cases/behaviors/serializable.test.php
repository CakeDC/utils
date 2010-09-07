<?php

App::import('Core', 'Model');

class Session extends CakeTestModel {
	public $actsAs = array('Utils.Serializable');
}

class SerializableTestCase extends CakeTestCase { 

/**
 * Fixtures used in the SessionTest
 *
 * @var array
 * @access public
 */
	var $fixtures = array('core.session'); 

/**
 * startTest
 *
 * @return void
 * @access public
 */
	public function startTest() {
		$this->Session = ClassRegistry::init('Session');
	}

/**
 * endTest
 *
 * @return void
 * @access public
 */
	public function endTest() {
		unset($this->Session);
	}
 
	public function testEmptyArrayDataValue() {
		$record = array('Session' => array('id' => 1, 'data' => array(), 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();
		$this->assertEqual($result, array('Session' => array('id' => 1, 'data' => 'a:0:{}', 'expires' => 1000)));

		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEqual($data, $record);
	}

	public function testEmptyDataValue() {
		$record = array('Session' => array('id' => 1, 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();

		$record = array('Session' => array('id' => 1, 'data' => array(), 'expires' => 1000));
		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEqual($data, $record);
	}


	public function testOneElementInData() {
		$record = array('Session' => array('id' => 1, 'data' => array('k' => 'value'), 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();
		$this->assertEqual($result, array('Session' => array('id' => 1, 'data' => 'a:1:{s:1:"k";s:5:"value";}', 'expires' => 1000)));

		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEqual($data, $record);
	}
	
	
	public function testErrorIsSerializedData() {
		$this->Session->Behaviors->detach('Serializable');
		$record = array('Session' => array('id' => 1, 'data' => 'error text', 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();

		$this->Session->Behaviors->attach('Utils.Serializable');
		$record = array('Session' => array('id' => 1, 'data' => array(), 'expires' => 1000));
		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEqual($data, $record);
	}
}
?>