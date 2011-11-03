<?php
App::import('Behavior', 'Utils.Sluggable');

/**
 * Sluggable Test Behavior
 **/
class SluggableTestBehavior extends SluggableBehavior {
}

/**
 * Slugged Article
 */
class SluggedArticle extends CakeTestModel {
	public $useTable = 'articles';
	public $actsAs = array('SluggableTest');
}

/**
 * Sluggable Test case
 */
class SluggableTest extends CakeTestCase {
/**
 * fixtures property
 *
 * @var array
 * @access public
 */
	public $fixtures = array('plugin.utils.article');

/**
 * Creates the model instance
 *
 * @return void
 */
	public function startTest() {
		$this->Model = new SluggedArticle();
		$this->Behavior = new SluggableTestBehavior();
	}

/**
 * Destroy the model instance
 *
 * @return void
 */
	public function endTest() {
		unset($this->Model);
		unset($this->Behavior);
		ClassRegistry::flush();
	}

/**
 * Test saving a item
 *
 * @return void
 */
	public function testSave() {
		$this->Model->create(array('title' => 'Fourth Article'));
		$this->Model->save();

		$result = $this->Model->read();
		$this->assertEqual($result['SluggedArticle']['slug'], 'fourth_article');

		// Should not update
		$this->Model->saveField('title', 'Fourth Article (Part 1)');
		$result = $this->Model->read();
		$this->assertEqual($result['SluggedArticle']['slug'], 'fourth_article');

		// Should update
		$this->Model->Behaviors->SluggableTest->settings['SluggedArticle']['update'] = true;
		$this->Model->saveField('title', 'Fourth Article (Part 2)');
		$result = $this->Model->read();
		$this->assertEqual($result['SluggedArticle']['slug'], 'fourth_article_part_2');

		// Updating the item should not update the slug
		$this->Model->saveField('body', 'Here goes the content.');
		$result = $this->Model->read();
		$this->assertEqual($result['SluggedArticle']['slug'], 'fourth_article_part_2');
	}

/**
 * Test save unique
 *
 * @return void
 */
	public function testSaveUnique() {
		$this->Model->create(array('title' => 'Fourth Article'));
		$this->Model->save();
		$this->Model->create(array('title' => 'Fourth Article!!'));
		$this->Model->save();
		$this->Model->create(array('title' => 'Fourth "Article"'));
		$this->Model->save();

		$results = $this->Model->find('all', array('conditions' => array('title LIKE' => 'Fourth%')));
		$this->assertEqual(count($results), 3);
		$this->assertEqual($results[0]['SluggedArticle']['slug'], 'fourth_article');
		$this->assertEqual($results[1]['SluggedArticle']['slug'], 'fourth_article_1');
		$this->assertEqual($results[2]['SluggedArticle']['slug'], 'fourth_article_2');
	}

/**
 * Test slug generation/update based on trigger
 *
 * @access public
 * @return void
 */
	public function testSluggenerationBasedOnTrigger() {
		$this->Model->Behaviors->detach('SluggableTest');
		$this->Model->Behaviors->attach('SluggableTest', array(
			'trigger' => 'generateSlug'));

		$this->Model->generateSlug = false;
		$this->Model->create(array('title' => 'Some Article 25271'));
		$result = $this->Model->save();
		$result['SluggedArticle']['id'] = $this->Model->id;
		$this->assertTrue(empty($result['SluggedArticle']['slug']));
		$this->Model->generateSlug = true;
		$result = $this->Model->save($result);
		$this->assertEqual($result['SluggedArticle']['slug'], 'some_article_25271');
	}

/**
 * Test save duplicate
 *
 * @return void
 */
	public function testSaveDuplicate() {
		$this->Model->Behaviors->SluggableTest->settings['SluggedArticle']['unique'] = false;
		$this->Model->create(array('title' => 'Fourth Article'));
		$this->Model->save();
		$this->Model->create(array('title' => 'Fourth Article'));
		$this->Model->save();

		$results = $this->Model->find('all', array('conditions' => array('title LIKE' => 'Fourth Article')));
		$this->assertEqual(count($results), 2);
		$this->assertEqual($results[0]['SluggedArticle']['slug'], 'fourth_article');
		$this->assertEqual($results[1]['SluggedArticle']['slug'], 'fourth_article');
	}

/**
 * Test saveUrl()
 *
 * @return void
 */
	public function testMultibyteSlug() {
		$result = $this->Model->multibyteSlug('⽅⽆⽇⽈⽉⽊~!@#$%^&*()=+[]{}\\/,.:;"\'<>');
		$this->assertEqual('⽅⽆⽇⽈⽉⽊', $result);

		$result = $this->Model->multibyteSlug('눡눢눣눤눥눦눧~!@#$%^&*()=+[]{}\\/,.:;"\'<>');
		$this->assertEqual('눡눢눣눤눥눦눧', $result);

		$result = $this->Model->multibyteSlug('눡~!@#$%^&*()=+[]{}\\/,.:;"\'<>눢눣눤눥눦눧');
		$this->assertEqual('눡_눢눣눤눥눦눧', $result);

		$result = $this->Model->multibyteSlug('krämer ~!@ # $% ^& *() =+[]{}\\/,.:;"\'<>');
		$this->assertEqual('krämer', $result);

		$result = $this->Model->multibyteSlug('Ärgerlich Öl Überzogen Straße ~!@ # $% ^& *() =+[]{}\\/,.:;"\'<>');
		$this->assertEqual('ärgerlich_öl_überzogen_straße', $result);

		$result = $this->Model->multibyteSlug('Foo\'s book');
		$this->assertEqual('foos_book', $result);
	}

/**
 * Test if a slug is getting updated properly
 *
 * @return void
 * @access public
 */
	public function testUpdatingSlug() {
		$this->Model->Behaviors->detach('SluggableTest');
		$this->Model->Behaviors->attach('SluggableTest', array(
			'update' => true));

		$this->Model->create(array('title' => "Andersons Fairy Tales"));
		$this->Model->save();
		$result = $this->Model->read();
		$this->assertEqual($result['SluggedArticle']['slug'], 'andersons_fairy_tales');

		$this->Model->save(array('title' => "Andersons Fairy Tales II"));
		$result = $this->Model->read();
		$this->assertEqual($result['SluggedArticle']['slug'], 'andersons_fairy_tales_ii');
	}

}
?>