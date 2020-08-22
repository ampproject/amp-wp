## Class `AMP_Editor_Blocks`

Class AMP_Editor_Blocks

### Methods
<details>
<summary>`init`</summary>

```php
public init()
```

Init.


</details>
<details>
<summary>`include_block_atts_in_wp_kses_allowed_html`</summary>

```php
public include_block_atts_in_wp_kses_allowed_html( $tags, $context )
```

Allowlist elements and attributes used for AMP.

This prevents AMP markup from being deleted in


</details>
<details>
<summary>`tally_content_requiring_amp_scripts`</summary>

```php
public tally_content_requiring_amp_scripts( $content )
```

Tally the AMP component scripts that are needed in a dirty AMP document.


</details>
<details>
<summary>`print_dirty_amp_scripts`</summary>

```php
public print_dirty_amp_scripts()
```

Print AMP scripts required for AMP components used in a non-AMP document (dirty AMP).


</details>
