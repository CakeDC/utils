Tiny Sluggable Behavior
=======================

The **Tiny Sluggable** Behavior generates unique short slugs for records. Each slug will be unique per model.

Short slugs are good for generating short urls like ```my.domain.com/a1``` to make it easy to type them on mobile devices for example.

Behavior Options
----------------

* **tinySlug:** The field used to store the tiny slug in, default is ```tiny_slug```.
* **codeset:** The set of characters used to generate the short slug, default is ```0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ```.
* **orderField:** Conditions for the find query to check if the slug already exists, default is ```orderField```

Basic Example
-------------

```php
BlogPost extends AppModel {
	public $actsAs = array(
		'Utils.TinySluggable'
	);
}
```

When saving a blog post the behavior will generate a short slug, the first one is going to become ```0``` with the standard code set.
