SoftDelete Behavior
===================

The SoftDelete behavior allows you to keep records on database and do not show them to users having a "deleted" flag. By default you should have "deleted" and "deleted_date" fields on your database table.

Since "exists" method in Model disable callbacks you may experience problems using it. To avoid these problems you can use the "existsAndNotDeleted" method from the behavior and we provide the following code to be put into AppModel to make this transparent:

```php
<?php
public function exists($id = null) {
	if ($this->Behaviors->attached('SoftDelete')) {
		return $this->existsAndNotDeleted($id);
	} else {
		return parent::exists($id);
	}
}
```

It will call ```existsAndNotDeleted()``` for models that use SoftDelete Behavior and Model:exists for models that do not use it

When deleting an item the SoftDelete behavior will override the ```delete()``` and update the record instead. This means that the response to the ```delete()``` will be false. In order to override this and return true, you will need to include the following in your ```AppModel.php``` file.

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