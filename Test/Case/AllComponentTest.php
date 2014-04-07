<?php
class AllComponentTest extends PHPUnit_Framework_TestSuite {

/**
 * Suite define the tests for this suite
 *
 * @return Suite
 */
	public static function suite() {
		$Suite = new CakeTestSuite('All Utils Plugin Component Tests');
		$path = dirname(__FILE__);
		$Suite->addTestDirectoryRecursive($path . DS . 'Controller' . DS . 'Component');
		return $Suite;
	}
}