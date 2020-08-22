## Class `AmpProject\AmpWP\Dom\ElementList`

Class ElementList

### Methods
* `add`

	<details>

	```php
	public add( DOMElement $element, DOMElement $caption = null )
	```

	Adds an element to the list, possibly with a caption.


	</details>
* `getIterator`

	<details>

	```php
	public getIterator()
	```

	Gets an iterator with the elements.

This together with the IteratorAggregate turns the object into a &quot;Traversable&quot;, so you can just foreach over it and receive its elements in the correct type.


	</details>
* `count`

	<details>

	```php
	public count()
	```

	Gets the count of the elements.


	</details>
