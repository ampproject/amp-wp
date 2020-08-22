## Class `AMP_DOM_Utils`

Class AMP_DOM_Utils

Functionality to simplify working with Dom\Documents and DOMElements.

### Methods
<details>
<summary><code>get_dom</code></summary>

```php
static public get_dom( $document, $encoding = null )
```

Return a valid Dom\Document representing HTML document passed as a parameter.


</details>
<details>
<summary><code>is_valid_head_node</code></summary>

```php
static public is_valid_head_node( \DOMNode $node )
```

Determine whether a node can be in the head.


</details>
<details>
<summary><code>get_amp_bind_placeholder_prefix</code></summary>

```php
static public get_amp_bind_placeholder_prefix()
```

Get attribute prefix for converted amp-bind attributes.

This contains a random string to prevent HTML content containing this data- attribute originally from being mutated to contain an amp-bind attribute when attributes are restored.


</details>
<details>
<summary><code>convert_amp_bind_attributes</code></summary>

```php
static public convert_amp_bind_attributes( $html )
```

Replace AMP binding attributes with something that libxml can parse (as HTML5 data-* attributes).

This is necessary because attributes in square brackets are not understood in PHP and get dropped with an error raised: &gt; Warning: DOMDocument::loadHTML(): error parsing attribute name This is a reciprocal function of AMP_DOM_Utils::restore_amp_bind_attributes().


</details>
<details>
<summary><code>restore_amp_bind_attributes</code></summary>

```php
static public restore_amp_bind_attributes( $html )
```

Convert AMP bind-attributes back to their original syntax.

This is a reciprocal function of AMP_DOM_Utils::convert_amp_bind_attributes().


</details>
<details>
<summary><code>get_dom_from_content</code></summary>

```php
static public get_dom_from_content( $content, $encoding = null )
```

Return a valid Dom\Document representing arbitrary HTML content passed as a parameter.


</details>
<details>
<summary><code>get_content_from_dom</code></summary>

```php
static public get_content_from_dom( Document $dom )
```

Return valid HTML *body* content extracted from the Dom\Document passed as a parameter.


</details>
<details>
<summary><code>get_content_from_dom_node</code></summary>

```php
static public get_content_from_dom_node( Document $dom, $node )
```

Return valid HTML content extracted from the DOMNode passed as a parameter.


</details>
<details>
<summary><code>create_node</code></summary>

```php
static public create_node( Document $dom, $tag, $attributes )
```

Create a new node w/attributes (a DOMElement) and add to the passed Dom\Document.


</details>
<details>
<summary><code>get_node_attributes_as_assoc_array</code></summary>

```php
static public get_node_attributes_as_assoc_array( $node )
```

Extract a DOMElement node&#039;s HTML element attributes and return as an array.


</details>
<details>
<summary><code>add_attributes_to_node</code></summary>

```php
static public add_attributes_to_node( $node, $attributes )
```

Add one or more HTML element attributes to a node&#039;s DOMElement.


</details>
<details>
<summary><code>is_node_empty</code></summary>

```php
static public is_node_empty( $node )
```

Determines if a DOMElement&#039;s node is empty or not.

.


</details>
<details>
<summary><code>recursive_force_closing_tags</code></summary>

```php
static public recursive_force_closing_tags( $dom, $node = null )
```

Forces HTML element closing tags given a Dom\Document and optional DOMElement


</details>
<details>
<summary><code>is_self_closing_tag</code></summary>

```php
static private is_self_closing_tag( $tag )
```

Determines if an HTML element tag is validly a self-closing tag per W3C HTML5 specs.


</details>
<details>
<summary><code>has_class</code></summary>

```php
static public has_class( \DOMElement $element, $class )
```

Check whether a given element has a specific class.


</details>
<details>
<summary><code>get_element_id</code></summary>

```php
static public get_element_id( $element, $prefix = 'amp-wp-id' )
```

Get the ID for an element.

If the element does not have an ID, create one first.


</details>
<details>
<summary><code>add_amp_action</code></summary>

```php
static public add_amp_action( \DOMElement $element, $event, $action )
```

Register an AMP action to an event on a given element.

If the element already contains one or more events or actions, the method will assemble them in a smart way.


</details>
<details>
<summary><code>merge_amp_actions</code></summary>

```php
static public merge_amp_actions( $first, $second )
```

Merge two sets of AMP events &amp; actions.


</details>
<details>
<summary><code>copy_attributes</code></summary>

```php
static public copy_attributes( $attributes, \DOMElement $from, \DOMElement $to, $default_separator = ',' )
```

Copy one or more attributes from one element to the other.


</details>
