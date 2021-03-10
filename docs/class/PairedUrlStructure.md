## Class `AmpProject\AmpWP\PairedUrlStructure`

Interface for classes that implement a PairedUrl.

### Methods

* [`__construct`](../method/PairedUrlStructure/__construct.md) - PairedUrlStructure constructor.
* [`has_endpoint`](../method/PairedUrlStructure/has_endpoint.md) - Determine a given URL is for a paired AMP request.
* [`add_endpoint`](../method/PairedUrlStructure/add_endpoint.md) - Turn a given URL into a paired AMP URL.
* [`remove_endpoint`](../method/PairedUrlStructure/remove_endpoint.md) - Remove the paired AMP endpoint from a given URL.
### Source

:link: [src/PairedUrlStructure.php:16](/src/PairedUrlStructure.php#L16-L59)

<details>
<summary>Show Code</summary>

```php
abstract class PairedUrlStructure {

	/**
	 * Paired URL service.
	 *
	 * @var PairedUrl
	 */
	protected $paired_url;

	/**
	 * PairedUrlStructure constructor.
	 *
	 * @param PairedUrl $paired_url Paired URL service.
	 */
	public function __construct( PairedUrl $paired_url ) {
		$this->paired_url = $paired_url;
	}

	/**
	 * Determine a given URL is for a paired AMP request.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return bool True if the URL has the paired endpoint.
	 */
	public function has_endpoint( $url ) {
		return $url !== $this->remove_endpoint( $url );
	}

	/**
	 * Turn a given URL into a paired AMP URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string AMP URL.
	 */
	abstract public function add_endpoint( $url );

	/**
	 * Remove the paired AMP endpoint from a given URL.
	 *
	 * @param string $url URL (or REQUEST_URI).
	 * @return string URL with AMP stripped.
	 */
	abstract public function remove_endpoint( $url );
}
```

</details>
