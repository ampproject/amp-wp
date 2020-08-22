## Class `AMP_Base_Embed_Handler`

Class AMP_Base_Embed_Handler

### Methods
<details>
<summary><code>register_embed</code></summary>

```php
abstract public register_embed()
```

Registers embed.


</details>
<details>
<summary><code>unregister_embed</code></summary>

```php
abstract public unregister_embed()
```

Unregisters embed.


</details>
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $args = array() )
```

Constructor.


</details>
<details>
<summary><code>get_scripts</code></summary>

```php
public get_scripts()
```

Get mapping of AMP component names to AMP script URLs.

This is normally no longer needed because the validating sanitizer will automatically detect the need for them via the spec.


</details>
<details>
<summary><code>match_element_attributes</code></summary>

```php
protected match_element_attributes( $html, $tag_name, $attribute_names )
```

Get regex pattern for matching HTML attributes from a given tag name.


</details>
