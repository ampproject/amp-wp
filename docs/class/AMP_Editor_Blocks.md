## Class `AMP_Editor_Blocks`

Class AMP_Editor_Blocks

### Methods
* `init`

	<details>

	```php
	public init()
	```

	Init.


	</details>
* `include_block_atts_in_wp_kses_allowed_html`

	<details>

	```php
	public include_block_atts_in_wp_kses_allowed_html( $tags, $context )
	```

	Allowlist elements and attributes used for AMP.

This prevents AMP markup from being deleted in


	</details>
* `tally_content_requiring_amp_scripts`

	<details>

	```php
	public tally_content_requiring_amp_scripts( $content )
	```

	Tally the AMP component scripts that are needed in a dirty AMP document.


	</details>
* `print_dirty_amp_scripts`

	<details>

	```php
	public print_dirty_amp_scripts()
	```

	Print AMP scripts required for AMP components used in a non-AMP document (dirty AMP).


	</details>
