## Function `amp_print_schemaorg_metadata`

```php
function amp_print_schemaorg_metadata();
```

Output schema.org metadata.

### Source

:link: [includes/amp-helper-functions.php:1821](../../includes/amp-helper-functions.php#L1821-L1829)

<details>
<summary>Show Code</summary>

```php
function amp_print_schemaorg_metadata() {
	$metadata = amp_get_schemaorg_metadata();
	if ( empty( $metadata ) ) {
		return;
	}
	?>
	<script type="application/ld+json"><?php echo wp_json_encode( $metadata, JSON_UNESCAPED_UNICODE ); ?></script>
	<?php
}
```

</details>
