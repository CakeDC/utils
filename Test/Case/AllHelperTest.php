<?php
class AllHelperTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return Suite
 */
	public static function suite() {
		$Suite = new CakeTestSuite('All Utils Plugin Helper Tests');
		$path = dirname(__FILE__);
		$Suite->addTestDirectoryRecursive($path . DS . 'View' . DS . 'Helper');
		return $Suite;
	}
}