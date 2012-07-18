<?php
class AllUtilsPluginTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return void
 */
	public static function suite() {
		$suite = new PHPUnit_Framework_TestSuite('All Utils Plugin Tests');

		$basePath = CakePlugin::path('Utils') . DS . 'Test' . DS . 'Case' . DS;		
		//Controller
		$suite->addTestFile($basePath . 'Controller' . DS . 'Component' . DS . 'ArchiveTest.php');
		//$suite->addTestFile($basePath . 'Controller' . DS . 'Component' . DS . 'FormPreserverTest.php');		
		$suite->addTestFile($basePath . 'Controller' . DS . 'Component' . DS . 'RefererTest.php');
		
		//Model
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'BtreeTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'CsvImportTest.php');
		//$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'InheritableTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'ListTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'LookupableTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'SerializableTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'SoftDeleteTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'SoftDeleteTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'TinySluggableTest.php');
		$suite->addTestFile($basePath . 'Model' . DS . 'Behavior' . DS . 'ToggleableTest.php');
		
		//View
		$suite->addTestFile($basePath . 'View' . DS . 'Helper' . DS . 'CleanerTest.php');
		$suite->addTestFile($basePath . 'View' . DS . 'Helper' . DS . 'GravatarTest.php');				
		
		$suite->addTestFile($basePath . 'Lib' . DS . 'I18nCountryTest.php');				
		
		return $suite;
	}


}