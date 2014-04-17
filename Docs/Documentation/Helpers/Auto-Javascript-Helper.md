Auto Javascript Helper
======================

Dynamically loads JS files based on the current controller and action if the JS files are present.

Just load the helper and put your files in

* app/webroot/js/autoload/<controller>.js
* app/webroot/js/autoload/<controller>/<action>.js

```php
public $helpers = array(
	'Utils.Autoload'
);
```

The helper will pick them up from there and load them in the layout.

Options
-------

* **path**: String, by default `autoload`, change this to change the name of the folder in /app/webroot/ from where the helper loads the js files.