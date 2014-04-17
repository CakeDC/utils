<?php
/**
 * Serialized Session
 */
class SerializedSession extends CakeTestModel {
	public $alias = 'Session';
	public $useTable = 'sessions';
	public $actsAs = array(
		'Utils.Serializable' => array(
			'field' => array('data')));
}

/**
 * Serialized Post
 */
class SerializedContent extends CakeTestModel {
	public $alias = 'Content';
	public $useTable = 'contents';
	public $actsAs = array(
		'Utils.Serializable' => array(
			'field' => array('title', 'body')));
}

class SerializableTestCase extends CakeTestCase {

/**
 * Fixtures used in the SessionTest
 *
 * @var array
 * @access public
 */
	public $fixtures = array(
		'core.session',
		'plugin.utils.content');

/**
 * SerializableBehavior Settings
 *
 * @var mixed
 */
	public $settings = array();

/**
 * setUp
 *
 * @return void
 * @access public
 */
	public function setUp() {
		parent::setUp();

		$this->Session = ClassRegistry::init('SerializedSession');
		$this->Content = ClassRegistry::init('SerializedContent');

		$this->settings = &$this->Session->Behaviors->Serializable->settings;
	}

/**
 * tearDown
 *
 * @return void
 * @access public
 */
	public function tearDown() {
		parent::tearDown();
		unset($this->Session, $this->Content, $this->settings);
	}

	public function testEmptyDataValue() {
		$record = array('Session' => array('id' => 1, 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();

		$record = array('Session' => array('id' => 1, 'data' => array(), 'expires' => 1000));
		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEquals($data, $record);
	}

	public function testJsonEmptyArrayDataValue() {
		$this->settings['Session']['engine'] = 'json';
		$record = array('Session' => array('id' => 1, 'data' => array(), 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();
		$this->assertEquals($result, array('Session' => array('id' => 1, 'data' => '[]', 'expires' => 1000)));

		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEquals($data, $record);
	}

	public function testCsvEmptyArrayDataValue() {
		$this->settings['Session']['engine'] = 'csv';
		$record = array('Session' => array('id' => 1, 'data' => array(), 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();
		$this->assertEqual($result, array('Session' => array('id' => 1, 'data' => '', 'expires' => 1000)));

		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEqual($data, $record);
	}

	public function testSerializeEmptyArrayDataValue() {
		$this->settings['Session']['engine'] = 'serialize';
		$record = array('Session' => array('id' => 1, 'data' => array(), 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();
		$this->assertEquals($result, array('Session' => array('id' => 1, 'data' => 'a:0:{}', 'expires' => 1000)));

		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEquals($data, $record);
	}


	public function testJsonOneElementInData() {
		$this->settings['Session']['engine'] = 'json';
		$record = array('Session' => array('id' => 1, 'data' => array('k' => 'value'), 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();
		$this->assertEquals($result, array('Session' => array('id' => 1, 'data' => '{"k":"value"}', 'expires' => 1000)));

		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEquals($data, $record);
	}

	public function testCsvOneElementInData() {
		$this->settings['Session']['engine'] = 'csv';
		$record = array('Session' => array('id' => 1, 'data' => array('k' => 'value'), 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();
		$this->assertEqual($result, array('Session' => array('id' => 1, 'data' => 'value', 'expires' => 1000)));

		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$expect = $record;
		$expect['Session']['data'] = array('value');
		$this->assertEqual($data, $expect);
	}

	public function testSerializeOneElementInData() {
		$this->settings['Session']['engine'] = 'serialize';
		$record = array('Session' => array('id' => 1, 'data' => array('k' => 'value'), 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();
		$this->assertEquals($result, array('Session' => array('id' => 1, 'data' => 'a:1:{s:1:"k";s:5:"value";}', 'expires' => 1000)));

		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEquals($data, $record);
	}


	public function testSaveAllSerialized() {
		$this->settings['Session']['engine'] = 'json';
		$record = array('Session' => array(array('id' => 1, 'data' => array('k' => 'value'), 'expires' => 1000), array('id' => 2, 'data' => array('k' => 'value'), 'expires' => 1000)));
		$result = $this->Session->saveAll($record['Session']);
		$this->assertEquals($result, true);

		$data = $this->Session->find('all', array('conditions' => array('id' => array(1, 2))));
		$this->assertEquals(Set::classicExtract($data, '{n}.Session'), $record['Session']);
	}


	public function testJsonSeveralDataFields() {
		$this->settings['Content']['engine'] = 'json';
		$record = array('Content' => array('id' => 1, 'title' => array('k' => 'value'), 'body' => array('k' => 'value'), 'published' => 'N'));
		$this->Content->create($record);
		$result = $this->Content->save();
		unset($result['Content']['updated'], $result['Content']['created']);
		$this->assertEquals($result, array('Content' => array('id' => 1, 'title' => '{"k":"value"}', 'body' => '{"k":"value"}', 'published' => 'N')));

		$data = $this->Content->find('first', array('conditions' => array('id' => 1), 'fields' => array('id', 'title', 'body', 'published')));
		$this->assertEquals($data, $record);
	}

	public function testCsvSeveralDataFields() {
		$this->settings['Content']['engine'] = 'csv';
		$record = array('Content' => array('id' => 1, 'title' => array('k' => 'value'), 'body' => array('value1', 'value2'), 'published' => 'N'));
		$this->Content->create($record);
		$result = $this->Content->save();
		unset($result['Content']['updated'], $result['Content']['created']);
		$this->assertEqual($result, array('Content' => array('id' => 1, 'title' => 'value', 'body' => 'value1,value2', 'published' => 'N')));

		$data = $this->Content->find('first', array('conditions' => array('id' => 1), 'fields' => array('id', 'title', 'body', 'published')));
		$expect = $record;
		$expect['Content']['title'] = array('value');
		$expect['Content']['body'] = array('value1', 'value2');
		$this->assertEqual($data, $expect);
	}

	public function testSerializeSeveralDataFields() {
		$this->settings['Content']['engine'] = 'serialize';
		$record = array('Content' => array('id' => 1, 'title' => array('k' => 'value'), 'body' => array('k' => 'value'), 'published' => 'N'));
		$this->Content->create($record);
		$result = $this->Content->save();
		unset($result['Content']['updated'], $result['Content']['created']);
		$this->assertEquals($result, array('Content' => array('id' => 1, 'title' => 'a:1:{s:1:"k";s:5:"value";}', 'body' => 'a:1:{s:1:"k";s:5:"value";}', 'published' => 'N')));

		$data = $this->Content->find('first', array('conditions' => array('id' => 1), 'fields' => array('id', 'title', 'body', 'published')));
		$this->assertEquals($data, $record);
	}


	public function testErrorIsSerializedData() {
		$this->Session->Behaviors->unload('Serializable');
		$record = array('Session' => array('id' => 1, 'data' => 'error text', 'expires' => 1000));
		$this->Session->create($record);
		$result = $this->Session->save();

		$this->Session->Behaviors->load('Utils.Serializable', array('field' => array('data')));
		$record = array('Session' => array('id' => 1, 'data' => array(), 'expires' => 1000));
		$data = $this->Session->find('first', array('conditions' => array('id' => 1)));
		$this->assertEquals($data, $record);
	}
}
