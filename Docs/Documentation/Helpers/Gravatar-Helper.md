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
echo $this->Gravatar->image('team@cakedc.com');
echo $this->Gravatar->imageUrl('team@cakedc.com');
```
