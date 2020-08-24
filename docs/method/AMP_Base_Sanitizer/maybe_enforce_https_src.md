## Method `AMP_Base_Sanitizer::maybe_enforce_https_src()`

```php
public function maybe_enforce_https_src( $src, $force_https = false );
```

Decide if we should remove a src attribute if https is required.

If not required, the implementing class may want to try and force https instead.

### Arguments

* `string $src` - URL to convert to HTTPS if forced, or made empty if $args[&#039;require_https_src&#039;].
* `boolean $force_https` - Force setting of HTTPS if true.

### Source

[includes/sanitizers/class-amp-base-sanitizer.php:394](https://github.com/ampproject/amp-wp/blob/develop/includes/sanitizers/class-amp-base-sanitizer.php#L394-L409)

<details>
<summary>Show Code</summary>
```php
public function maybe_enforce_https_src( $src, $force_https = false ) {
	$protocol = strtok( $src, ':' ); // @todo What about relative URLs? This should use wp_parse_url( $src, PHP_URL_SCHEME )
	if ( 'https' !== $protocol ) {
		// Check if https is required.
		if ( isset( $this->args['require_https_src'] ) && true === $this->args['require_https_src'] ) {
			// Remove the src. Let the implementing class decide what do from here.
			$src = '';
		} elseif ( ( ! isset( $this->args['require_https_src'] ) || false === $this->args['require_https_src'] )
			&& true === $force_https ) {
			// Don't remove the src, but force https instead.
			$src = set_url_scheme( $src, 'https' );
		}
	}
	return $src;
}
```
</details>
