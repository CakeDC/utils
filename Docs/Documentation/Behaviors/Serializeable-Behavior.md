Serializeable Behavior
======================

The **Serializeable** behavior allows to you serialize fields using phps serialize() function, phps json de- and encode methods and csv as well via implode().

The behavior implements two methods that can be used to serialize or deserialize data at any time.

Behavior Options
----------------

* **fields:** An array of fields that that should be serialized.
* **engine:** ```json```, ```csv``` or ```serialize```, default is  ```serialize```.
* **field:** Deprecated, use ```fields```.
