## Function `_amp_xdebug_admin_notice`

> :warning: This function is deprecated: 1.5.0

```php
function _amp_xdebug_admin_notice();
```

Print admin notice if the Xdebug extension is loaded.

### Source

:link: [includes/deprecated.php:269](../../includes/deprecated.php#L269-L284)

<details>
<summary>Show Code</summary>

```php
function _amp_xdebug_admin_notice() {
	_deprecated_function( __FUNCTION__, '1.5.0' );

	?>
	<div class="notice notice-warning">
		<p>
			<?php
			esc_html_e(
				'Your server currently has the Xdebug PHP extension loaded. This can cause some of the AMP plugin\'s processes to timeout depending on your system resources and configuration. Please deactivate Xdebug for the best experience.',
				'amp'
			);
			?>
		</p>
	</div>
	<?php
}
```

</details>
