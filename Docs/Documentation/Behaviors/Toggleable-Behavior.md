Toggleable Behavior
===================

The **Toggleable** behavior allows you to make it easy to toggle the state of a field in your model.

Behavior Options
----------------

* **fields:** An array of fields and their toggleable values, for example ```array('active' => array(1, 0))```. You have to set this.
* **checkRecord:** Checks if the given record id exists, default is ```true```.

Example
-------

The behavior in this example is set up to toggle two fields of the User model, ```active``` and ```gender```.

```php
Users extends AppModel {
	public $actsAs = array(
		'Utils.Toggleable' => array(
			'fields' => array(
				'active' => array(1, 0),
				'gender' => array('male', 'female')
			)
		)
	);
}
```

If you now want to toggle the status of an user, for example making it active or inactive you can do this. The first argument is the record id the second the field you want to toggle. The return value of the ```toggle()``` method is the new state of the field or false if something went wrong.

```php
$this->User->toggle(1, 'active');
```

Assuming the value was 1 before, active will now be 0.

Here is another example, assuming the current gender is male you can toggle it to female.

```php
$this->User->toggle(1, 'gender');
```

Your users gender is now female.