# CakePHP Utils Plugin #

The Utils plugin contains a lot of reusable components, behaviors and helpers.
Below are the details of each, and notes on usage.

## Behaviors ##

* Importable     - adds the ability to import csv data to the model.
* Inheritable    - allows a model to act as a subclass of another model
* Keyvalue       - allows to get and save group of settings in key/value representation.
* List           - provide a way to make collection ordered
* Lookupable     - looks associated records up based on a given field and its value
* Pingbackable   - allows make any model to send pingbacks and trackbacks on saving content.
* Serializable   - allows serialize/deserialize array data into large text field.
* Sluggable      - implement slugs for model.
* SoftDelete     - soft deleting for model.
* TinySluggable  - creats tiny slugs similar to known url shorteners like bit.ly
* Toggleable     - toggle field values

## Libraries ##

* Languages      - List of languages that can be used in selects

## Components ##

* Archive       - Creates the data for "archive" date ranges that can be used to generated links like "May 2010", "March 2010",...
* FormPreserver - Allow to keep form data between login redirect and returning back after login.
* Pingbacks     - Implement pingback and trackback server for pingbacks support.
* Referer       - Allow to keep referer url inside the add/edit form to reuse it for redirect on success POST or submit.

## Helpers ##

* Cleaner       - Allow to strip tags from input markup
* Gravatar      - Gravatar Helper.
* Tree          - Used the generate nested representations of hierarchial data

### Importable Behavior ##

You can configure the Importable behavior using these options:

* delimiter     - The delimiter for the values, default is ;
* eclosure      - The enclusure, default is "
* hasHeader     - Parse the header of the CSV file if it has one, default is true

The main method of this behavior is

	$this->Model->importCSV('myFile.csv');

It will read the csv file and try to save the records to the model. In the case of errors you'll get them by calling

	$this->Model->getImportErrors();

### Inheritable Behavior ##

Please see [the bakery article](http://bakery.cakephp.org/articles/view/inheritable-behavior-missing-link-of-cake-model) for more information.

### Keyvalue Behavior ##

You can configure the Importable behavior using these options:

* foreignKey    - The foreign key field, default is user_id
* scope         - Find condition like array to define a scope

## Languages Lib ##

The languages lib is basically just a helper lib that extends I10n to get a three character language code => country name array.

	App::import('Lib', 'Utils.Languages');
	$Languages = new Languages();
	$languageList = $Languages->lists();

`$languageList` will contain the three character code mapped to a country. This list can be used in language selects for example.

## Archive Component ##

## Referer Component ##

Allow to keep referer url inside the add/edit form to reuse it for redirect on success POST or submit.

