<?php
class AllUtilsPluginTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return Suite
 */
	public static function suite() {
		$Suite = new CakeTestSuite('All Plugin tests');
		$path = dirname(__FILE__);
		$Suite->addTestDirectory($path . DS . 'Controller' . DS . 'Component');
		$Suite->addTestDirectory($path . DS . 'Error');
		$Suite->addTestDirectory($path . DS . 'Lib');
		$Suite->addTestDirectory($path . DS . 'Model' . DS . 'Behavior');
		$Suite->addTestDirectory($path . DS . 'View' . DS . 'Helper');
		return $Suite;
	}
}