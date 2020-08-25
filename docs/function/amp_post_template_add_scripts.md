## Function `amp_post_template_add_scripts`

> :warning: This function is deprecated: Scripts are now automatically added.

```php
function amp_post_template_add_scripts( $amp_template );
```

Print scripts.

### Arguments

* `\AMP_Post_Template $amp_template` - Template.

### Source

:link: [includes/deprecated.php:195](../../includes/deprecated.php#L195-L207)

<details>
<summary>Show Code</summary>

```php
function amp_post_template_add_scripts( $amp_template ) {
	_deprecated_function( __FUNCTION__, '1.5' );
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	echo amp_render_scripts(
		array_merge(
			[
				// Just in case the runtime has been overridden by amp_post_template_data filter.
				'amp-runtime' => $amp_template->get( 'amp_runtime_script' ),
			],
			$amp_template->get( 'amp_component_scripts', [] )
		)
	);
}
```

</details>
