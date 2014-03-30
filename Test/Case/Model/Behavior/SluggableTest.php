<?php
App::uses('Utils.Sluggable', 'Model/Behavior');

/**
 * Slugged Article
 */
class SluggedArticle extends CakeTestModel {
	public $useTable = 'articles';
	public $actsAs = array('Utils.Sluggable');
}

/**
 * Slugged Article
 */
class SluggedCustomArticle extends CakeTestModel {
	public $useTable = 'articles';
	public $actsAs = array('Utils.Sluggable');

	function multibyteSlug($string = null, $separator = '_') {
		return 'slug';
	}
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
	public function setUp() {
		$this->Model = new SluggedArticle();
		$this->ModelTwo = new SluggedCustomArticle();
		$this->Behavior = new SluggableBehavior();
	}

/**
 * Destroy the model instance
 *
 * @return void
 */
	public function tearDown() {
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
		$this->Model->create(array(
			'SluggedArticle' => array(
				'title' => 'Fourth Article')));
		$this->Model->save();	

		$result = $this->Model->read();
		$this->assertEquals($result['SluggedArticle']['slug'], 'fourth_article');

		// Should not update
		$this->Model->saveField('title', 'Fourth Article (Part 1)');
		$result = $this->Model->read();
		$this->assertEquals($result['SluggedArticle']['slug'], 'fourth_article');

		// Should update
		$this->Model->Behaviors->load('Utils.Sluggable', array('update' => true));
		$this->Model->saveField('title', 'Fourth Article (Part 2)');
		$result = $this->Model->read();
		$this->assertEquals($result['SluggedArticle']['slug'], 'fourth_article_part_2');

		// Updating the item should not update the slug
		$this->Model->saveField('body', 'Here goes the content.');
		$result = $this->Model->read();
		$this->assertEquals($result['SluggedArticle']['slug'], 'fourth_article_part_2');
	}

/**
 * Test saving a item
 *
 * @return void
 */
	public function testCustomMultibyteSlug() {
		$this->ModelTwo->create(array('title' => 'Fifth Article'));
		$this->ModelTwo->save();

		$result = $this->ModelTwo->read();
		$this->assertEquals($result['SluggedCustomArticle']['slug'], 'slug');
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
		$this->Model->create(array('title' => 'Fourth'));
		$this->Model->save();
		$this->Model->create(array('title' => 'Fourth'));
		$this->Model->save();

		$results = $this->Model->find('all', array('conditions' => array('title LIKE' => 'Fourth%')));
		$this->assertEquals(count($results), 5);
		$this->assertEquals($results[0]['SluggedArticle']['slug'], 'fourth_article');
		$this->assertEquals($results[1]['SluggedArticle']['slug'], 'fourth_article_1');
		$this->assertEquals($results[2]['SluggedArticle']['slug'], 'fourth_article_2');
		$this->assertEquals($results[3]['SluggedArticle']['slug'], 'fourth');
		$this->assertEquals($results[4]['SluggedArticle']['slug'], 'fourth_1');
	}

/**
 * Test save unique2
 *
 * @return void
 */
    public function testSaveUnique2() {
        $this->Model->create(array('title' => 'Puertas para muebles'));
        $this->Model->save();
		
		$this->Model->create(array('title' => 'Puertas'));
        $this->Model->save();
        
		$results = $this->Model->find('all', array('conditions' => array('title LIKE' => 'Puertas%')));
        $this->assertEquals(count($results), 2);
        $this->assertEquals($results[0]['SluggedArticle']['slug'], 'puertas_para_muebles');
        $this->assertEquals($results[1]['SluggedArticle']['slug'], 'puertas');
    }

/**
 * Test slug generation/update based on trigger
 *
 * @access public
 * @return void
 */
	public function testSluggenerationBasedOnTrigger() {
		$this->Model->Behaviors->unload('Sluggable');
		$this->Model->Behaviors->load('Sluggable', array(
			'trigger' => 'generateSlug'));

		$this->Model->generateSlug = false;
		$this->Model->create(array('title' => 'Some Article 25271'));
		$result = $this->Model->save();
		$result['SluggedArticle']['id'] = $this->Model->id;
		$this->assertTrue(empty($result['SluggedArticle']['slug']));
		$this->Model->generateSlug = true;
		$result = $this->Model->save($result);
		$this->assertEquals($result['SluggedArticle']['slug'], 'some_article_25271');
	}

/**
 * Test save duplicate
 *
 * @return void
 */
	public function testSaveDuplicate() {
		$this->Model->Behaviors->unload('Sluggable');
		$this->Model->Behaviors->load('Sluggable', array('unique' => false));

		$this->Model->create(array('title' => 'Fourth Article'));
		$this->Model->save();
		$this->Model->create(array('title' => 'Fourth Article'));
		$this->Model->save();

		$results = $this->Model->find('all', array('conditions' => array('title LIKE' => 'Fourth Article')));
		$this->assertEquals(count($results), 2);
		$this->assertEquals($results[0]['SluggedArticle']['slug'], 'fourth_article');
		$this->assertEquals($results[1]['SluggedArticle']['slug'], 'fourth_article');
	}

/**
 * Test saveUrl()
 *
 * @return void
 */
	public function testMultibyteSlug() {
		$result = $this->Model->multibyteSlug('⽅⽆⽇⽈⽉⽊~!@#$%^&*()=+[]{}\\/,.:;"\'<>');
		$this->assertEquals('⽅⽆⽇⽈⽉⽊', $result);

		$result = $this->Model->multibyteSlug('눡눢눣눤눥눦눧~!@#$%^&*()=+[]{}\\/,.:;"\'<>');
		$this->assertEquals('눡눢눣눤눥눦눧', $result);

		$result = $this->Model->multibyteSlug('눡~!@#$%^&*()=+[]{}\\/,.:;"\'<>눢눣눤눥눦눧');
		$this->assertEquals('눡_눢눣눤눥눦눧', $result);

		$result = $this->Model->multibyteSlug('krämer ~!@ # $% ^& *() =+[]{}\\/,.:;"\'<>');
		$this->assertEquals('krämer', $result);

		$result = $this->Model->multibyteSlug('Ärgerliche Öl Überzogen Straße ~!@ # $% ^& *() =+[]{}\\/,.:;"\'<>');
		$this->assertEquals('ärgerliche_öl_überzogen_straße', $result);

		$result = $this->Model->multibyteSlug('ñÑ áéí óúÁ ÉÍÓÚ');
		$this->assertEquals($result, 'ññ_áéí_óúá_éíóú');

		$result = $this->Model->multibyteSlug('Foo\'s book');
		$this->assertEquals('foos_book', $result);
	}

/**
 * Test if a slug is getting updated properly
 *
 * @return void
 * @access public
 */
	public function testUpdatingSlug() {
		$this->Model->Behaviors->unload('Sluggable');
		$this->Model->Behaviors->load('Sluggable', array(
			'update' => true));

		$this->Model->create(array('title' => "Andersons Fairy Tales"));
		$this->Model->save();
		$result = $this->Model->read();
		$this->assertEquals($result['SluggedArticle']['slug'], 'andersons_fairy_tales');

		$this->Model->save(array('title' => "Andersons Fairy Tales II"));
		$result = $this->Model->read();
		$this->assertEquals($result['SluggedArticle']['slug'], 'andersons_fairy_tales_ii');
	}
}
