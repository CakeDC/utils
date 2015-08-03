# Utils Plugin for CakePHP #

for cake 2.x

The utils plugin contain a lot of reusable components, behaviors and helpers. Here we will list and detail 
each component.

## Behaviors 

* Btree          - 
* CsvImport      - adds the ability to import csv data to the model.
* Inheritable    - 
* Keyvalue       - allows to get and save group of settings in key/value representation.
* List           - provide a way to make collection ordered
* Lookupable     - looks up associated records up based on a given field and its value
* Pingbackable   - 
* Publishable    - 
* Serializable   - allows serialize/deserialize array data into large text field.
* Sluggable      - implement slugs for model.
* SoftDelete     - soft deleting for model.
* TinySluggable  - creates tiny slugs similar to known url shorteners like bit.ly
* Toggleable     - toggle field values

## Libraries

* Languages      - List of languages that can be used in selects

## Components

* Archive        - Creates the data for "archive" date ranges that can be used to generated links like "May 2010", "March 2010",...
* FormPreserver  - Allow to keep form data between login redirect and returning back after login.
* Pingbacks      - 
* Referer        - Allow to keep referer url inside the add/edit form to reuse it for redirect on success POST or submit.
* Utils          - 

## Helpers

* Cleaner        - Allow to strip tags from input markup
* Gravatar       - Gravatar Helper
* Tree           - Generates nested representations of hierarchial data
* Place          - Allows to display image and text placeholders

### CsvImport Behavior

You can configure the Importable behavior using these options:

* delimiter      - The delimiter for the values, default is ;
* enclosure      - The enclosure, default is "
* hasHeader      - Parse the header of the CSV file if it has one, default is true

The main method of this behavior is
```php
<?php
$this->Model->importCSV('myFile.csv');
```

It will read the csv file and try to save the records to the model. In the case of errors you'll get them by calling
```php
<?php
$this->Model->getImportErrors();
```

### Keyvalue Behavior

You can configure the Importable behavior using these options:

* foreignKey     - The foreign key field, default is user_id
* scope          - Find condition like array to define a scope

### List Behavior 

The list behavior allows you to have records act like a list, for example a tracklist and to move records in this list.

* positionColumn - The column in the table used to store the positiot, default is 'position'.
* scope          - Find condition like array to define a scope, default is empty string ''.
* validate       - validate the data when the behavior is saving the changes, default is false.
* callbacks      - use callbacks when the behavior saves the data, default is false.

### SoftDelete Behavior 

The SoftDelete behavior allows you to keep records on database and do not show them to users having a "deleted" flag. By default you should have "deleted" and "deleted_date" fields on your database table. 

Since "exists" method in Model disable callbacks you may experience problems using it. To avoid these problems you can use the "existsAndNotDeleted" method from the behavior and we provide the following code to be put into AppModel to make this transparent:

```php
<?php
public function exists($id = null) {
	if ($this->Behaviors->loaded('SoftDelete')) {
		return $this->existsAndNotDeleted($id);
	} else {
		return parent::exists($id);
	}
}
```

It will call SoftDelete::existsAndNotDeleted() for models that use SoftDelete Behavior and Model:exists for models that do not use it

When deleting an item the SoftDelete behavior will override the `delete()` and update the record instead. This means that the response to the `delete()` will be false. In order to override this and return true, you will need to include the following in your `AppModel.php` file.

```php
<?php
public function delete($id = null, $cascade = true) {
    $result = parent::delete($id, $cascade);
    if ($result === false && $this->Behaviors->enabled('SoftDelete')) {
       return (bool)$this->field('deleted', array('deleted' => 1));
    }
    return $result;
}
```

## Languages Lib

The languages lib is basically just a helper lib that extends I10n to get a three character language code => country name array.

```php
<?php
App::import('Lib', 'Utils.Languages');
$Languages = new Languages();
$languageList = $Languages->lists();
```

`$languageList` will contain the three character code mapped to a country. This list can be used in language selects for example.

## Archive Component

## Referer Component

Allow to keep referer url inside the add/edit form to reuse it for redirect on success POST or submit.

## Requirements ##

* PHP version: PHP 5.2+
* CakePHP version: 1.3 Stable

## Support ##

To report bugs or request features, please visit the [CakeDC/Utils Issue Tracker](https://github.com/CakeDC/utils/issues).

For more information about our Professional CakePHP Services please visit the [Cake Development Corporation website](http://cakedc.com).

## Branch strategy ##

The master branch holds the STABLE latest version of the plugin. 
Develop branch is UNSTABLE and used to test new features before releasing them. 

Previous maintenance versions are named after the CakePHP compatible version, for example, branch 1.3 is the maintenance version compatible with CakePHP 1.3.
All versions are updated with security patches.

## Contributing to this Plugin ##

Please feel free to contribute to the plugin with new issues, requests, unit tests and code fixes or new features. If you want to contribute some code, create a feature branch from develop, and send us your pull request. Unit tests for new features and issues detected are mandatory to keep quality high. 

## License ##

Copyright 2009-2010, [Cake Development Corporation](http://cakedc.com)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.

## Copyright ###

Copyright 2009-2011<br/>
[Cake Development Corporation](http://cakedc.com)<br/>
1785 E. Sahara Avenue, Suite 490-423<br/>
Las Vegas, Nevada 89104<br/>
http://cakedc.com<br/>
