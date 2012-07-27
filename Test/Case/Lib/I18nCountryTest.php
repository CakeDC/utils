<?php
App::import('Lib', 'Utils.I18nCountry');

class I18nCountryTest extends CakeTestCase {

/**
 * testGetList
 *
 * @return void
 */
	public function testGetList() {
		$Country = new I18nCountry();
		$result = $Country->getList();
		$this->assertTrue(is_array($result));
		$this->assertEqual($result['AF'], 'Afghanistan');
	}

/**
 * testGetBy
 *
 * @return void
 */
	public function testGetBy() {
		$Country = new I18nCountry();
		$this->assertEqual($Country->getBy('US'), 'United States');

		$result = $Country->getBy('US', true);
		$this->assertTrue(is_array($result));
	}

}