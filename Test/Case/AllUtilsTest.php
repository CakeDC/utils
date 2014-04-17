<?php
class AllUtilsPluginTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return Suite
 */
	public static function suite() {
		$Suite = new CakeTestSuite('All Plugin Tests');
		$path = dirname(__FILE__);
		$Suite->addTestDirectoryRecursive($path);
		return $Suite;
	}
}