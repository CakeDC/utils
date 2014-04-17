Referer Component
=================

The Referer component will preserve the referer to the page you came from, even if the form was submitted multiple time and has shown errors.

To make the referer work properly you **must** add this field to your form:

```php
$this->Form->input('Data.referer', array(
	'type' => 'hidden',
	'value' => $referer
);
```

That's all to make it work!