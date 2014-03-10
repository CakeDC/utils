Languages Lib
=============

The languages lib is basically just a helper lib that extends I10n to get a three character language code => country name array.

```php
App::uses('Languages', 'Utils.Lib');
$Languages = new Languages();
$languageList = $Languages->lists();
```

`$languageList` will contain the three character code mapped to a country. This list can be used in language selects for example.
