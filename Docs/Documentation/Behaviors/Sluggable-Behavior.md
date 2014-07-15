Sluggable Behavior
==================

The **Sluggable** Behavior generates unique slugs for records. If a slug is already existing a number is appended to the slug.

Behavior Options
----------------

* **label:** The field used to generate the slug from, default is ```title```.
* **slug:** The field to store the slug in, default is ```slug```.
* **scope:** Conditions for the find query to check if the slug already exists, default is ```array()```
* **separator:** The character used to separate the words in the slug, default is ```_```.
* **length:** The maximum length of the slug, default is ```255```.
* **unique:** Check if the slug is unique, default is ```true```.
* **update:** Update the slug or not after the record was created, default is ```false```.
* **trigger:** Defines a property in the model that has to be true to generate the slug, default is ```false```.
* **priority:** See [Object Callback Priorities](http://book.cakephp.org/2.0/en/core-libraries/collections.html#object-callback-priorities) in the CakePHP book, default is ```9```, CakePHPs default is 10.

Before And After Slug Generation Callbacks
------------------------------------------

The behavior has two callbacks that you can implement in your model using the behavior to modify the slugs.

* **beforeSlugGeneration:** Is called before the actual slug generation starts in the behavior. The slug and separator are passed as arguments.
* **afterSlugGeneration:** Is called after the slug was  generated. The slug and separator are passed as arguments.

```php
class Product extends AppModel {
	public $actsAs = array(
		'Utils.Sluggable'
	);
	public function beforeSlugGeneration($slug, $separator) {
		return str_replace('§', 'paragraph');
	}
}

Basic Example
-------------

```php
BlogPost extends AppModel {
	public $actsAs = array(
		'Utils.Sluggable'
	);
}
```

When saving a blog post with the title ```Some new title``` the behavior will generate a slug named ```some_new_title``` and set it to the ```slug``` field of the table.

More Complex Example
--------------------

```php
Category extends AppModel {
	public $actsAs = array(
		'Utils.Sluggable' => array(
			'label' => 'name',
			'separator' => '-',
			'update' => true
		)
	);
}
```

When saving a category with the name ```Some category``` the behavior will generate a slug named ```some-category``` and set it to the ```slug``` field of the table.

When you edit the category now and change the title the slug will be updated as well because it is configured to update it on change.

Using Slugglable with other behaviors
-------------------------------------

Change the priority of the behaviors to make sure that Sluggable is called *before* **or** *after* other behaviors that use or rely on the *same* data. It really depends on what the other behaviors.

See [Object Callback Priorities](http://book.cakephp.org/2.0/en/core-libraries/collections.html#object-callback-priorities) in the CakePHP book.