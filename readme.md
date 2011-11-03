# Utils Plugin for CakePHP #

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
* Tree           - 

### CsvImport Behavior

You can configure the Importable behavior using these options:

* delimiter      - The delimiter for the values, default is ;
* enclosure      - The enclosure, default is "
* hasHeader      - Parse the header of the CSV file if it has one, default is true

The main method of this behavior is

	$this->Model->importCSV('myFile.csv');

It will read the csv file and try to save the records to the model. In the case of errors you'll get them by calling

	$this->Model->getImportErrors();

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

## Languages Lib

The languages lib is basically just a helper lib that extends I10n to get a three character language code => country name array.

	App::import('Lib', 'Utils.Languages');
	$Languages = new Languages();
	$languageList = $Languages->lists();

`$languageList` will contain the three character code mapped to a country. This list can be used in language selects for example.

## Archive Component

## Referer Component

Allow to keep referer url inside the add/edit form to reuse it for redirect on success POST or submit.

## Requirements ##

* PHP version: PHP 5.2+
* CakePHP version: 2.0 Stable

## Support ##

For support and feature request, please visit the [Utils Plugin Support Site](http://cakedc.lighthouseapp.com/projects/59607-utils-plugin/).

For more information about our Professional CakePHP Services please visit the [Cake Development Corporation website](http://cakedc.com).

## License ##

Copyright 2009-2010, [Cake Development Corporation](http://cakedc.com)

Licensed under [The MIT License](http://www.opensource.org/licenses/mit-license.php)<br/>
Redistributions of files must retain the above copyright notice.

## Copyright ###

Copyright 2009-2010<br/>
[Cake Development Corporation](http://cakedc.com)<br/>
1785 E. Sahara Avenue, Suite 490-423<br/>
Las Vegas, Nevada 89104<br/>
http://cakedc.com<br/>
