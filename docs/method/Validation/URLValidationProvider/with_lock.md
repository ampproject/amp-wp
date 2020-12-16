## Method `URLValidationProvider::with_lock()`

```php
public function with_lock( $callback );
```

Runs a callback with a lock set for the duration of the callback.

### Arguments

* `callable $callback` - Callback to run with the lock set.

### Return value

`mixed` - WP_Error if a lock is in place. Otherwise, the result of the callback or void if it doesn&#039;t return anything.

### Source

:link: [src/Validation/URLValidationProvider.php:107](/src/Validation/URLValidationProvider.php#L107-L120)

<details>
<summary>Show Code</summary>

```php
public function with_lock( $callback ) {
	if ( $this->is_locked() ) {
		return new WP_Error(
			'amp_url_validation_locked',
			__( 'URL validation cannot start right now because another process is already validating URLs. Try again in a few minutes.', 'amp' )
		);
	}
	$this->lock();
	$result = $callback();
	$this->unlock();
	return $result;
}
```

</details>
