## Method `AMP_Base_Sanitizer::__construct()`

```php
public function __construct( $dom, $args = array() );
```

AMP_Base_Sanitizer constructor.

### Arguments

* `\AmpProject\Dom\Document $dom` - Represents the HTML document to sanitize.
* `array $args` - {      Args.      @type int $content_max_width      @type bool $add_placeholder      @type bool $require_https_src      @type string[] $amp_allowed_tags      @type string[] $amp_globally_allowed_attributes      @type string[] $amp_layout_allowed_attributes }

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:107](/includes/sanitizers/class-amp-base-sanitizer.php#L107-L116)

<details>
<summary>Show Code</summary>

```php
public function __construct( $dom, $args = [] ) {
	$this->dom  = $dom;
	$this->args = array_merge( $this->DEFAULT_ARGS, $args );
	if ( ! empty( $this->args['use_document_element'] ) ) {
		$this->root_element = $this->dom->documentElement;
	} else {
		$this->root_element = $this->dom->body;
	}
}
```

</details>
