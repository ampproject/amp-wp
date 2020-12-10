## Method `URLValidationProvider::reset_lock()`

```php
public function reset_lock();
```

Resets the lock timeout. This allows long-running processes to keep running beyond the lock timeout.

### Source

:link: [src/Validation/URLValidationProvider.php:125](/src/Validation/URLValidationProvider.php#L125-L127)

<details>
<summary>Show Code</summary>

```php
public function reset_lock() {
	$this->lock();
}
```

</details>
