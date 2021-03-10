## Method `PairedUrlStructure::__construct()`

```php
public function __construct( \AmpProject\AmpWP\PairedUrl $paired_url );
```

PairedUrlStructure constructor.

### Arguments

* `\AmpProject\AmpWP\PairedUrl $paired_url` - Paired URL service.

### Source

:link: [src/PairedUrlStructure.php:30](/src/PairedUrlStructure.php#L30-L32)

<details>
<summary>Show Code</summary>

```php
public function __construct( PairedUrl $paired_url ) {
	$this->paired_url = $paired_url;
}
```

</details>
