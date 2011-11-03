<?php
App::uses('CsvImportBehavior', 'Utils.Model/Behavior');

/**
 * Content Test Model
 */
class Content extends CakeTestModel {
	public $useTable = 'contents';
}

/**
 * Content Test Model
 */
class ImportObserver {

	public function onImportRow($data) {}
	public function listen($action, $data) {}
}

/**
 * ContentCallback Test Model
 */
class ContentCallback extends CakeTestModel {
	public $useTable = 'contents';

	public function beforeImport($data) {
		$data['ContentCallback']['type'] = $data['ContentCallback']['type'] . '-modified';
		return $data;
	}
}
/**
 * ImportableBehavior Test case
 */
class CsvImportTest extends CakeTestCase {
/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	public $fixtures = array('plugin.utils.content');

/**
 * Creates the model instance
 *
 * @return void
 */
	public function startTest() {
		$this->Content = new Content();
	}

/**
 * Destroy the model instance
 *
 * @return void
 */
	public function endTest() {
		unset($this->Content);
		unset($this->Behavior);
		ClassRegistry::flush();
	}

/**
 * testImportCSVNoFile
 *
 * @access public
 * @return void
 */
	public function testImportCSVNoFile() {
		$this->Content->Behaviors->attach('Utils.CsvImport');
		$this->expectException();
		$this->expectError();
		$this->Content->importCSV('/unexistent/file');
	}
	
/**
 * testImportCSV
 *
 * @access public
 * @return void
 */
	public function testImportCSV() {
		$this->Content->Behaviors->attach('Utils.CsvImport');
		$path = App::pluginPath('Utils');
		$result = $this->Content->importCSV($path . 'Test' . DS . 'tmp' . DS . 'test1.csv');
		$this->assertTrue($result);

		$records = $this->Content->find('all', array('order' => 'created DESC', 'limit' => 2));
		$titles = Set::extract('/Content/title', $records);
		$this->assertEqual($titles, array('Unearthed rare monster in london', 'Another Title'));
		
		$permalinks = Set::extract('/Content/permalink', $records);
		$this->assertEqual($permalinks, array(13444555, 'A permalink'));
	}

/**
 * testImportCSVWithCallback
 *
 * @access public
 * @return void
 */
	public function testImportCSVWithCallback() {
		$this->Content = new ContentCallback();
		$this->Content->Behaviors->attach('Utils.CsvImport');
		$path = App::pluginPath('Utils');
		$result = $this->Content->importCSV($path . 'Test' . DS . 'tmp' . DS . 'test1.csv');
		$this->assertTrue($result);

		$records = $this->Content->find('all', array('order' => 'created DESC', 'limit' => 2));
		$types = Set::extract('/ContentCallback/type', $records);
		$this->assertEqual($types, array('Article-modified', 'Book-modified'));
	}

/**
 * testImportCSVWithFixedData
 *
 * @access public
 * @return void
 */
	public function testImportCSVWithFixedData() {
		$this->Content->Behaviors->attach('Utils.CsvImport');
		$path = App::pluginPath('Utils');
		$fixed = array('Content' => array('parent_id' => 10));
		$result = $this->Content->importCSV($path . 'Test' . DS . 'tmp' . DS . 'test1.csv', $fixed);
		$this->assertTrue($result);

		$records = $this->Content->find('all', array('order' => 'created DESC', 'limit' => 2));
		$parents = Set::extract('/Content/parent_id', $records);
		$this->assertEqual($parents, array(10, 10));
	}

/**
 * testImportCSVWithValidation
 *
 * @access public
 * @return void
 */
	public function testImportCSVWithValidation() {
		$this->Content->Behaviors->attach('Utils.CsvImport');
		$path = App::pluginPath('Utils');
		$this->Content->validate = array(
			'title' => array(
				'long' => array('rule' => array('minLength', 100))));
		$result = $this->Content->importCSV($path . 'Test' . DS . 'tmp' . DS . 'test1.csv');
		$this->assertFalse($result);
		$errors = $this->Content->getImportErrors();
		$expected = array(
			array('validation' => array('title' => array('long'))),
			array('validation' => array('title' => array('long')))
		);
		$this->assertEqual($errors, $expected);
	}

/**
 * testImportCSVWithSomeErrors
 *
 * @access public
 * @return void
 */
	public function testImportCSVWithSomeErrors() {
		$this->Content->Behaviors->attach('Utils.CsvImport');
		$path = App::pluginPath('Utils');
		$this->Content->validate = array(
			'type' => array(
				'list' => array('rule' => array('inList', array('Article')))));
				
		$result = $this->Content->importCSV($path . 'Test' . DS . 'tmp' . DS . 'test1.csv', array(), true);
		$this->assertEqual($result, array(0)); // The numbers of the rows that were saved

		$errors = $this->Content->getImportErrors();
		$expected = array(
			1 => array('validation' => array('type' => array('list'))) // the index 1 indicates the number of the failing row
		);
		$this->assertEqual($errors, $expected);
	}

/**
 * testImportCSVWithSomeErrors
 *
 * @access public
 * @return void
 */
	public function testListeners() {
		$mock = $this->getMock('ImportObserver');
		$path = App::pluginPath('Utils');
		$this->Content->Behaviors->attach('Utils.CsvImport');
		$this->Content->attachImportListener($mock);
		$this->Content->attachImportListener(array(&$mock, 'listen'));
		$mock->expects($this->any())
			->method('onImportRow')
			->will($this->returnValue(2));
		$mock->expects($this->any())
			->method('listen')
			->will($this->returnValue(2));
		$result = $this->Content->importCSV($path . 'Test' . DS . 'tmp' . DS . 'test1.csv');
		$this->assertTrue($result);
	}
}