Lookup Behavior
===============

Lookup behavior is used to find an associated record id based on a string input.

For example you want that your user enters a name of an author but to associate it with an Author record. The behavior will lookup the author based on the name and set the id to author_id in the model the behavior is attached to.

Behavior Options
----------------

* **types:** , default is ```array()```.

Example
-------

Add the behavior. We're adding containable as well for this example.

```php
Book extends AppModel {
	public $actsAs = array(
		'Containable',
		'Utils.Lookupable' => array(
			'types' => array(
				'Author'
			)
		)
	);
}
```

Now lets add a record.

```php
$this->Book->create();
$firstResult = $this->Book->save(array(
	'Book' => array(
		'title' => 'Foobar',
		'author_name' => 'Im looked up!'
	)
));
```

When you now do a search for that book you'll see it got the Author associated:

```php
$result = $this->Book->find('first', array(
	'contain' => array(
		'Author',
	),
	'conditions' => array(
		'Book.title' => 'foobar')
	)
);
debug($result);
```

The result will be something similar to:

```php
array(
	'Book' => array(
		'id' => 1,
		'author_id' => 1
		'title' => 'Foobar',
	),
	'Author' => array(
		'id' => 1,
		'name' => 'Im looked up!'
	)
)
```
