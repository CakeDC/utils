Archive Component
=================

The archive component will take the primary model of the controller from Controller::$modelClass if not configured otherwise and use it to build a list of records ordered by month and years to build a blog style listing of them.

The component has just one method that needs to be called.

```php
$this->Archive->archiveLinks();
```