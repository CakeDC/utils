<?php
App::uses('SluggableBehavior', 'Utils.Model/Behavior');

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
		$this->Model->create(array('title' => 'Fourth Art'));
		$this->Model->save();
		$this->Model->create(array('title' => 'Fourth Article!!'));
		$this->Model->save();
		$this->Model->create(array('title' => 'Fourth "Article"'));
		$this->Model->save();

		$results = $this->Model->find('all', array('conditions' => array('title LIKE' => 'Fourth%')));
		$this->assertEqual(count($results), 4);
		$this->assertEqual($results[0]['SluggedArticle']['slug'], 'fourth_article');
		$this->assertEqual($results[1]['SluggedArticle']['slug'], 'fourth_art');
		$this->assertEqual($results[2]['SluggedArticle']['slug'], 'fourth_article_1');
		$this->assertEqual($results[3]['SluggedArticle']['slug'], 'fourth_article_2');
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
		$result = $this->Model->multibyteSlug('â½…â½†â½‡â½ˆâ½‰â½Š~!@#$%^&*()=+[]{}\\/,.:;"\'<>');
		$this->assertEqual('â½…â½†â½‡â½ˆâ½‰â½Š', $result);

		$result = $this->Model->multibyteSlug('ëˆ¡ëˆ¢ëˆ£ëˆ¤ëˆ¥ëˆ¦ëˆ§~!@#$%^&*()=+[]{}\\/,.:;"\'<>');
		$this->assertEqual('ëˆ¡ëˆ¢ëˆ£ëˆ¤ëˆ¥ëˆ¦ëˆ§', $result);

		$result = $this->Model->multibyteSlug('ëˆ¡~!@#$%^&*()=+[]{}\\/,.:;"\'<>ëˆ¢ëˆ£ëˆ¤ëˆ¥ëˆ¦ëˆ§');
		$this->assertEqual('ëˆ¡_ëˆ¢ëˆ£ëˆ¤ëˆ¥ëˆ¦ëˆ§', $result);

		$result = $this->Model->multibyteSlug('krÃ¤mer ~!@ # $% ^& *() =+[]{}\\/,.:;"\'<>');
		$this->assertEqual('krÃ¤mer', $result);

		$result = $this->Model->multibyteSlug('Ã„rgerlich Ã–l Ãœberzogen StraÃŸe ~!@ # $% ^& *() =+[]{}\\/,.:;"\'<>');
		$this->assertEqual('Ã¤rgerlich_Ã¶l_Ã¼berzogen_straÃŸe', $result);

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