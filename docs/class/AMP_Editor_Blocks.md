## Class `AMP_Editor_Blocks`

Class AMP_Editor_Blocks

### Methods
<details>
<summary><code>init</code></summary>

```php
public init()
```

Init.


</details>
<details>
<summary><code>include_block_atts_in_wp_kses_allowed_html</code></summary>

```php
public include_block_atts_in_wp_kses_allowed_html( $tags, $context )
```

Allowlist elements and attributes used for AMP.

This prevents AMP markup from being deleted in


</details>
<details>
<summary><code>tally_content_requiring_amp_scripts</code></summary>

```php
public tally_content_requiring_amp_scripts( $content )
```

Tally the AMP component scripts that are needed in a dirty AMP document.


</details>
<details>
<summary><code>print_dirty_amp_scripts</code></summary>

```php
public print_dirty_amp_scripts()
```

Print AMP scripts required for AMP components used in a non-AMP document (dirty AMP).


</details>
