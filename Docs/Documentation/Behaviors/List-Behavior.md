List Behavior
=============

The **List** Behavior allows you to have records act like a list, for example a track list and to move records in this list.

Behavior Options
----------------

* **positionColumn:** The column in the table used to store the position, default is ```position```.
* **scope:** Find condition like array to define a scope, default is empty string ```''```.
* **validate:** Validate the data when the behavior is saving the changes, default is ```false```.
* **callbacks:** Use callbacks when the behavior saves the data, default is ```false```.
* **addToTop:** Adds a new list entry on top of the list, default is ```false```.

Example
-------

```php
TrackList extends AppModel {
	public $actsAs = array(
		'Utils.List' => array(
			'scope' => array(
				'album_id
			)
		)
	);
}
```

When you add now a new record with an ```album_id``` of 1 and the record is not already present, the position of that record will become 1. Adding a second record with the same ```album_id``` will add it with the position value of 2.

Now let's assume you've added three records:

<table>
	<tr>
		<th>Id</th>
		<th>Title</th>
	</tr>
	<tr>
		<td>1</td>
		<td>First Track</td>
	</tr>
	<tr>
		<td>2</td>
		<td>Second Track</td>
	</tr>
	<tr>
		<td>3</td>
		<td>Third track</td>
	</tr>
</table>

You can now manipulate the order of these records by using the behaviors methods. They're pretty self explaining by just looking at their name.

* **insertAt($position, $id):** Moves an existing record to a given position in the list.
* **moveUp($id):** Moves a record one position up.
* **moveHigher($id):** Moves a record one position up.
* **moveDown($id):** Moves a record one position down.
* **moveLower($id):** Moves a record one position down.
* **moveToTop($id):** Moves a record to the first position of the list.
* **moveToBottom($id):** Moves a record to the bottom position of the list.
* **removeFromList($id):** Removes a record from the listing.
* **isFirst($id):** Checks if a record is the first in the list.
* **isLast($id):** Checks if a record is the last in the list.
* **higherItem($id):** Returns the next higher item in the list.
* **lowerItem($id):** Returns the next lower item in the list.
* **isInList():** Checks if an item is in the list, the data has to be set by Model::set() before calling this method.
* **fixListOrder():** Attempt to fix a messed up order for a list.
