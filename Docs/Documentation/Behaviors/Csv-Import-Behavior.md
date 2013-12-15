CsvImport Behavior
==================

You can configure the Importable behavior using these options:

* **delimiter:** The delimiter for the values, default is ;
* **enclosure:** The enclosure, default is "
* **hasHeader:** Parse the header of the CSV file if it has one, default is true

The main method of this behavior is
```php
$this->Model->importCSV('myFile.csv');
```

It will read the csv file and try to save the records to the model. In the case of errors you'll get them by calling
```php
$this->Model->getImportErrors();
```