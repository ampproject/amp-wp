## Function `get_optimizer`

```php
function get_optimizer( $args );
```

Optimizer instance to use.

### Arguments

* `array $args` - Associative array of arguments to pass into the transformation engine.

### Return value

`\AmpProject\Optimizer\TransformationEngine` - Optimizer transformation engine to use.

### Source

:link: [.jono-hero-image-debug/optimize.php:97](/.jono-hero-image-debug/optimize.php#L97-L111)

<details>
<summary>Show Code</summary>

```php
function get_optimizer( $args ) {
	$configuration = get_optimizer_configuration( $args );

	$fallback_remote_request_pipeline = new FallbackRemoteGetRequest(
		new WpHttpRemoteGetRequest(),
		new FilesystemRemoteGetRequest( Optimizer\LocalFallback::getMappings() )
	);

	$cached_remote_request = new CachedRemoteGetRequest( $fallback_remote_request_pipeline, WEEK_IN_SECONDS );

	return new Optimizer\TransformationEngine(
		$configuration,
		$cached_remote_request
	);
}
```

</details>
