Gravatar Helper
===============

Load the helper in your controller

```php
$helpers = array(
	'Utils.Gravatar'
);
```

In your view use it with an email address.

```php
// To display the image
echo $this->Gravatar->image('team@cakedc.com');

// To get only the URL
echo $this->Gravatar->imageUrl('team@cakedc.com');
```

Options
-------

* **default**: -
* **size**: Integer, size of the Gravatar image, 1 to 512.
* **rating**: String, Gravatar rating, `g`, `pg`, `r` or `x`.
* **ext**: Boolean, used to display the .jpeg extension for Gravatars. This helps systems that don't display images unless they have a specific image extension on the URL.
* **secure**: Boolean, use HTTPS or not, false by default.