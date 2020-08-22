## Class `AMP_Base_Embed_Handler`

Class AMP_Base_Embed_Handler

### Methods
* `register_embed`

<details>

```php
abstract public register_embed()
```

Registers embed.


</details>
* `unregister_embed`

<details>

```php
abstract public unregister_embed()
```

Unregisters embed.


</details>
* `__construct`

<details>

```php
public __construct( $args = array() )
```

Constructor.


</details>
* `get_scripts`

<details>

```php
public get_scripts()
```

Get mapping of AMP component names to AMP script URLs.

This is normally no longer needed because the validating sanitizer will automatically detect the need for them via the spec.


</details>
* `match_element_attributes`

<details>

```php
protected match_element_attributes( $html, $tag_name, $attribute_names )
```

Get regex pattern for matching HTML attributes from a given tag name.


</details>
