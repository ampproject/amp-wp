## Class `AmpProject\AmpWP\Component\Carousel`

Class Carousel

Gets the markup for an &lt;amp-carousel&gt;.

### Methods
* `__construct`

	<details>

	```php
	public __construct( Document $dom, ElementList $slides )
	```

	Instantiates the class.


	</details>
* `get_dom_element`

	<details>

	```php
	public get_dom_element()
	```

	Gets the carousel element.


	</details>
* `get_dimensions`

	<details>

	```php
	private get_dimensions()
	```

	Gets the carousel&#039;s width and height, based on its elements.

This will return the width and height of the slide (possibly image) with the widest aspect ratio, not necessarily that with the biggest absolute width.


	</details>
* `is_image_element`

	<details>

	```php
	private is_image_element( DOMElement $element )
	```

	Determine whether an element is an image (either an &lt;amp-img&gt; or an &lt;img&gt;).


	</details>
