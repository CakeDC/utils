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
