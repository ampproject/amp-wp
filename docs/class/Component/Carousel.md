## Class `AmpProject\AmpWP\Component\Carousel`

Class Carousel

Gets the markup for an &lt;amp-carousel&gt;.

### Methods
<details>
<summary>`__construct`</summary>

```php
public __construct( Document $dom, ElementList $slides )
```

Instantiates the class.


</details>
<details>
<summary>`get_dom_element`</summary>

```php
public get_dom_element()
```

Gets the carousel element.


</details>
<details>
<summary>`get_dimensions`</summary>

```php
private get_dimensions()
```

Gets the carousel&#039;s width and height, based on its elements.

This will return the width and height of the slide (possibly image) with the widest aspect ratio, not necessarily that with the biggest absolute width.


</details>
<details>
<summary>`is_image_element`</summary>

```php
private is_image_element( DOMElement $element )
```

Determine whether an element is an image (either an &lt;amp-img&gt; or an &lt;img&gt;).


</details>
