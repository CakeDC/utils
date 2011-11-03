<?php
/**
 * This code is an improved version of the Inheritable Behavior published
 * at http://bakery.cakephp.org/articles/view/inheritable-behavior-missing-link-of-cake-model
 * 
 * It was itself based on top of the SubclassBehavior by Eldon Bite <eldonbite@gmail.com>
 * and the ExtendableBehavior class by Matthew Harris which can be found at
 * http://bakery.cakephp.org/articles/view/extendablebehavior 
 * 
 * @author Cake Development Corporation (http://cakedc.com)
 * @license   http://www.opensource.org/licenses/mit-license.php The MIT License 
 */
App::uses('AppModel', 'Model');
App::uses('Model', 'Model');
App::uses('InheritableBehavior', 'Utils.Model/Behavior');

// STI Models
class Content extends CakeTestModel {
	public $useTable = 'contents';
}

class Article extends Content {
	public $actsAs = array('Utils.Inheritable');
}

class Page extends Content {
	public $actsAs = array('Utils.Inheritable');
}

// CTI models
class Asset extends CakeTestModel {
	public $validate = array('title' => array('rule' => 'notEmpty')); 
}

class Link extends Asset {
	public $actsAs = array('Utils.Inheritable' => array('method'=>'CTI'));
	public $validate = array('url' => array('rule' => 'notEmpty')); 
}
class Image extends Asset {
	public $actsAs = array('Utils.Inheritable' => array('method'=>'CTI'));
}


/**
 * InheritableTest class
 *
 * @package              cake
 * @subpackage           cake.tests.cases.libs.model.behaviors
 */
class InheritableTest extends CakeTestCase {

/**
 * Fixtures associated with this test case
 *
 * @var array
 * @access public
 */
	public $fixtures = array('plugin.utils.content', 'plugin.utils.asset', 'plugin.utils.link', 'plugin.utils.image');

/**
 * Start Test callback
 *
 * @param string $method
 * @return void
 * @access public
 */
	public function startTest($method) {
		parent::startTest($method);

		// STI models
		$this->Article = ClassRegistry::init('Article');
		$this->Page = ClassRegistry::init('Page');
		$this->Content = ClassRegistry::init('Content');

		$this->Page->Behaviors->attach('Containable');
		$this->Article->Behaviors->attach('Containable');

		// CTI models
		$this->Asset = ClassRegistry::init('Asset');
		$this->Link = ClassRegistry::init('Link');
		$this->Image = ClassRegistry::init('Image');
	}

/**
 * End Test callback
 *
 * @param string $method
 * @return void
 * @access public
 */
	public function endTest($method) {
		parent::endTest($method);
		unset($this->Article, $this->Content, $this->Page, $this->Asset, $this->Link, $this->Image);
		ClassRegistry::flush();
	}

/**
 * Test the Parent class instance of each subclass (STI)
 * 
 * @return void
 * @access public
 */
	public function testSubclassParentClass() {
		$this->assertIsA($this->Article->parent, 'Content');
		$this->assertIsA($this->Page->parent, 'Content');
		$this->assertEqual($this->Content->find('all'), $this->Article->parent->find('all'));
	}

/**
 * Test a find call on a subclass (STI)
 * 
 * @return void
 * @access public
 */
	public function testSubclassFind() {
		// Test subclass Article
		$result = $this->Article->find('all');
		$this->assertTrue(Set::matches('/Article[type=Article]', $result));
		$this->__assertMatches('/Article[type=Article]', $result);
		$this->__assertMatches('/Article[type!=Page]', $result);

		// Test Suclass Page
		$result = $this->Page->find('all');
		$this->__assertMatches('/Page[type=Page]', $result);
		$this->__assertMatches('/Page[type!=Article]', $result);

		// Test String Condition
		$r1 = $this->Page->find('all', array('conditions' => array('Page.permalink' => 'about-us')));
		$r2 = $this->Page->find('all', array('conditions' => 'Page.permalink = "about-us"'));
		$this->assertEqual($r1, $r2, "should support string conditions");

		// Test condition build properly for different subclasses, should not conflict
		$r1 = $this->Page->find('all', array('conditions' => "permalink = 'about-us'"));
		$r2 = $this->Article->find('all', array('conditions' => "permalink = 'about-us'"));
		$this->__assertMatches('/Page[body=/CakePHP is a MVC PHP framework/i]', $r1);
		$this->__assertMatches('/Article[body=/company/i]', $r2, "shit");
		$this->assertNotEqual($r1, $r2);
	}

/**
 * Test creating a subclass (STI)
 * 
 * @return void
 * @access public
 */
	public function testSubclassCreation() {
		// Test subclass creation
		$this->Article->create(array(
			'title' => 'monday morning train rush',
			'body' => 'Bus transport break down around the city... ',
			'permalink' => 'monday-morning-train-rush'));
		$result = $this->Article->save();
		$this->assertTrue(Set::matches('/Article[title=/monday morning train rush/i]', $result));

		$result = $this->Article->findByPermalink('monday-morning-train-rush');
		$this->assertEqual(count($result), 1);
		$this->assertTrue(Set::matches('/Article[body=/bus transport/i]', $result));

		// another test
		$result = $this->Page->save(array(
			'title' => 'profile page',
			'body' => 'We have a profile page here',
			'permalink' => 'profile-page'));
		$this->assertTrue(Set::matches('/Page[title=/profile page/i]', $result));

		$result = $this->Page->find('all', array('conditions' => "Page.permalink='profile-page'"));
		$this->assertTrue(Set::matches('/Page[body=/profile page/]', $result));

		// beforeFind shouldn't invoke here
		$result = $this->Page->saveField('published', 'Y');
		$this->assertTrue(Set::matches('/Page[published=/Y/i]', $result));
	}
	
/**
 * Test CTI models validation
 * 
 * @return void
 * @access public
 */
	public function testClassInheritanceValidate() {
		$this->Link->create(array(
			'title' => '',
			'description' => 'yahoo homepage',
			'url' => ''));
		$this->assertFalse($this->Link->validates());
		ksort($this->Link->validationErrors);
		$this->assertEqual(array_keys($this->Link->validationErrors), array('title', 'url'));
	}

/**
 * Test a find on CTI models
 * 
 * @return void
 * @access public
 */
	public function testClassTableInheritanceFind() {
		$result = $this->Link->find('all');
		$this->__assertMatches('/Link/title', $result);
		$this->__assertMatches('/Link/description', $result);
		$this->__assertMatches('/Link/url', $result);

		$result = $this->Image->find('all');
		$this->__assertMatches('/Image/title', $result);
		$this->__assertMatches('/Image/description', $result);
		$this->__assertMatches('/Image/file_name', $result);
		$this->__assertMatches('/Image/content_type', $result);
	}

/**
 * Test creating a CTI model
 * 
 * @return void
 * @access public
 */
	public function testClassTableCreation() {
		$this->Link->create(array(
			'title' => 'yahoo',
			'description' => 'yahoo homepage',
			'url' => 'http://yahoo.com'));
		$this->Image->create(array(
			'title' => 'BSD logo',
			'description' => 'Daemon powers',
			'file_name' => 'bsd.bmp',
			'file_size' => '653445',
			'content_type' => 'image/bitmap'));
		$result = $this->Link->save();
		$this->assertTrue(Set::matches('/Link[title=/yahoo/i]', $result));
		$result = $this->Image->save();
		$this->assertTrue(Set::matches('/Image[title=/BSD logo/i]', $result));

		$result = $this->Link->saveAll(array(
			array('Link' => array(
				'title' => 'yahoo',
				'description' => 'yahoo homepage',
				'url' => 'http://yahoo.com')),
			array('Link' => array(
				'title' => 'wikipedia',
				'description' => 'wikipedia is the free enclopedia',
				'url' => 'http://wikipedia.org'))));
		$this->assertTrue($result);
		
		$result = $this->Image->saveAll(array(
			array('Image' => array(
				'title' => 'sony logo',
				'description' => 'logo of sony',
				'file_name' => 'sony.png',
				'file_size' => '342331',
				'content_type' => 'image/png')),
			array('Image' => array(
				'title' => 'microsoft logo',
				'description' => 'logo of the evil corp that still use shitty gif logo and do not even compress em',
				'file_name' => 'm$.gif',
				'file_size' => '984700',
				'content_type' => 'image/gif'))));
		$this->assertTrue($result);
	}
	
/**
 * Test editing a CTI model
 * 
 * @return void
 * @access public
 */
	public function testClassTableEdition() {
		$data = $this->Link->findById(11);
		$data['Link']['title'] = 'yahoo UK';
		$data['Link']['url'] = 'http://yahoo.co.uk';
		$this->Link->create($data);
		$result = $this->Link->save();
		$this->assertTrue(Set::matches('/Link[title=/yahoo UK/i]', $result));
		
		$result = $this->Link->findById(11);
		$this->assertEqual($this->Link->find('count'), 2);
		$this->assertEqual($result['Link']['title'], $data['Link']['title']);
		$this->assertEqual($result['Link']['url'], $data['Link']['url']);
	}

/**
 * Test CTI models deletion
 * 
 * @return void
 * @access public
 */
	public function testClassInheritanceDelete() {
		$this->Link->delete(11);
		$this->assertFalse($this->Link->findById(11));
		$this->assertFalse($this->Asset->findById(11));

		$result = $this->Image->deleteAll(true, true, true);
		$this->assertTrue($result);
	}
	
/**
 * Test the afterFind callback in a more exhaustive way for CTI relationsihps
 * 
 * @return void
 * @access public
 */
	public function testAfterFind() {
		$linkData = array('id' => 11, 'url'=> 'http://cakephp.org');
		$assetData = array('id' => 11, 'title' => 'home page link', 'description' => 'link back to home page');
		
		$results = $this->Link->Behaviors->Inheritable->afterFind($this->Link, array());
		$this->assertEqual($results, array());
		
		$data = array(
			array(
				'Link' => array_merge($linkData, array('Asset' => $assetData))));
		$results = $this->Link->Behaviors->Inheritable->afterFind($this->Link, $data);
		$expected = array(
			array(
				'Link' => array_merge($linkData, $assetData)));
		$this->assertEqual($results, $expected);
		
		$data = array(
			array(
				'Link' => array(
					array_merge($linkData, array('Asset' => $assetData)))));
		$results = $this->Link->Behaviors->Inheritable->afterFind($this->Link, $data);
		$expected = array(
			array('Link' => array(
				array_merge($linkData, $assetData))));
		$this->assertEqual($results, $expected);
	}
	
/**
 * Tests the afterfind callback on a related model - in this case it is called with a different 
 * results formatting
 * 
 * @return void
 * @access public
 */
	public function testAfterFindRelatedModel() {
		$linkData = array('id' => 11, 'url'=> 'http://cakephp.org');
		$assetData = array('id' => 11, 'title' => 'home page link', 'description' => 'link back to home page');
		
		$data = array_merge($linkData, array('Asset' => $assetData));
		$results = $this->Link->Behaviors->Inheritable->afterFind($this->Link, $data);
		$expected = array_merge($linkData, $assetData);
		$this->assertEqual($results, $expected);
		
		// Another format that can be found
		$data = array('Link' => array(
			array_merge($linkData, array('Asset' => $assetData))));
		$results = $this->Link->Behaviors->Inheritable->afterFind($this->Link, $data);
		$expected = array('Link' => array(
			array_merge($linkData, $assetData)));
		$this->assertEqual($results, $expected);
	}

/**
 * Convenience function to assert Matches using Set::matches
 * 
 * @param unknown_type $pattern
 * @param unknown_type $object
 * @param unknown_type $message
 * @return void
 * @access private
 */
	private function __assertMatches($pattern, $object, $message = '') {
		return $this->assertTrue(Set::matches($pattern, $object), $message);
	}
}