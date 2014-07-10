Home
====

The **Utils** plugin provides a lot of useful components, helpers and behaviours.

Requirements
------------

* CakePHP 2.4+
* PHP 5.2.8+

Documentation
-------------

* **[Installation.md](Documentation/Installation.md)**

**Components**

* **[Archive](Documentation/Components/Archive-Component.md)**: Creates the data for "archive" date ranges that can be used to generated links like "May 2010", "March 2010",...
* **[FormPreserver](Documentation/Components/Form-Preserver-Component.md)**: Allow to keep form data between login redirect and returning back after login.
* **[Pingbacks](Documentation/Components/Pingbacks-Component.md)**: A pingback is one of three types of linkback methods. The component helps building that.
* **[Referer](Documentation/Components/Referer-Component.md)**: Allow to keep referrer url inside the add/edit form to reuse it for redirect on success POST or submit.

**Behaviours**

* **[Btree](Documentation/Behaviors/Btree-Behavior.md)**: Adds the ability to import csv data to the model.
* **[Csv](Documentation/Behaviors/Csv-Behavior.md)**: Adds the ability to import csv data to the model.
* **[Inheritable](Documentation/Behaviors/Inheritable-Behavior.md)**: Allows you to inherit tables.
* **[Keyvalue](Documentation/Behaviors/Keyvalue-Behavior.md)**: Allows to get and save group of settings in key/value representation.
* **[List](Documentation/Behaviors/List-Behavior.md)**: Provides a way to make a set of records ordered.
* **[Lookupable](Documentation/Behaviors/Lookupable-Behavior.md)**: Looks up associated records up based on a given field and its value
* **[Pingbackable](Documentation/Behaviors/Pingbackable-Behavior.md)**: A pingback is one of three types of linkback methods. The behavior helps building that.
* **[Serializeable](Documentation/Behaviors/Serializeable-Behavior.md)**: Allows serialize/deserialize array data into large text field.
* **[SoftDelete](Documentation/Behaviors/Soft-Delete-Behavior.md)**: Soft deleting for model.
* **[Sluggable](Documentation/Behaviors/Sluggable-Behavior.md)**: Implement slugs for model.
* **[TinySluggable](Documentation/Behaviors/Tiny-Sluggable-Behavior.md)**: Creates tiny slugs similar to known url shorteners like bit.ly.
* **[Toggable](Documentation/Behaviors/Toggleable-Behavior.md)**: Toggle field values.

**Helpers**

* **[AutoJavascript](Documentation/Helpers/Auto-Javascript-Helper.md)**:
* **[Gravatar](Documentation/Helpers/Gravatar-Helper.md)**: Gravatar Helper
* **[Place](Documentation/Helpers/Place-Helper.md)**: Lorem Lipsum placeholder generator
* **[Tree](Documentation/Helpers/Tree-Helper.md)**: Generates nested representations of hierarchial data
* **Cleaner**: Removed, use [HtmlPurifier](https://github.com/burzum/cakephp-html-purifier) instead.
* **HtmlPlus:** Removed, can be done by the core functionality in 2.0

**Libs**

* **[I18nCountry](Documentation/Lib/I18nCountry.md)**: Returns a country list that is translateable
* **[Languages](Documentation/Lib/Languages.md)**: A convenience lib to generate language lists
