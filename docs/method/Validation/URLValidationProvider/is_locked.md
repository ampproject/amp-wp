## Method `URLValidationProvider::is_locked()`

```php
public function is_locked();
```

Returns whether validation is currently locked.

### Return value

`boolean`

### Source

:link: [src/Validation/URLValidationProvider.php:94](/src/Validation/URLValidationProvider.php#L94-L99)

<details>
<summary>Show Code</summary>

```php
public function is_locked() {
	$lock_time = (int) get_option( self::LOCK_KEY, 0 );
	// It's locked if the difference between the lock time and the current time is less than the lockout time.
	return time() - $lock_time < self::LOCK_TIMEOUT;
}
```

</details>
