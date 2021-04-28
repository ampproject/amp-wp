## Function `amp_generate_script_hash`

```php
function amp_generate_script_hash( $script );
```

Generate hash for inline amp-script.

The sha384 hash used by amp-script is represented not as hexadecimal but as base64url, which is defined in RFC 4648 under section 5, "Base 64 Encoding with URL and Filename Safe Alphabet". It is sometimes referred to as "web safe".

### Arguments

* `string $script` - Script.

### Return value

`string|null` - Script hash or null if the sha384 algorithm is not supported.

### Source

:link: [includes/amp-helper-functions.php:1827](/includes/amp-helper-functions.php#L1827-L1838)

<details>
<summary>Show Code</summary>

```php
function amp_generate_script_hash( $script ) {
	$sha384 = hash( 'sha384', $script, true );
	if ( false === $sha384 ) {
		return null;
	}
	$hash = str_replace(
		[ '+', '/', '=' ],
		[ '-', '_', '.' ],
		base64_encode( $sha384 ) // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
	);
	return 'sha384-' . $hash;
}
```

</details>
