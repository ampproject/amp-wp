## Class `AMP_Link_Sanitizer`

Class AMP_Link_Sanitizer.

Adapts links for AMP-to-AMP navigation:  - In paired AMP (Transitional and Reader modes), internal links get &#039;?amp&#039; added to them.  - Internal links on AMP pages get rel=amphtml added to them.  - Forms with internal actions get a hidden &#039;amp&#039; input added to them.  - AMP pages get meta[amp-to-amp-navigation] added to them.  - Any elements in the admin bar are excluded.
 Adapted from https://gist.github.com/westonruter/f9ee9ea717d52471bae092879e3d52b0

### Methods
<details>
<summary><code>__construct</code></summary>

```php
public __construct( $dom, array $args = array() )
```

Sanitizer constructor.


</details>
<details>
<summary><code>sanitize</code></summary>

```php
public sanitize()
```

Sanitize.


</details>
<details>
<summary><code>add_meta_tag</code></summary>

```php
public add_meta_tag( $content = self::DEFAULT_META_CONTENT )
```

Add the amp-to-amp-navigation meta tag.


</details>
<details>
<summary><code>process_links</code></summary>

```php
public process_links()
```

Process links by adding adding AMP query var to links in paired mode and adding rel=amphtml.


</details>
<details>
<summary><code>process_element</code></summary>

```php
private process_element( \DOMElement $element, $attribute_name )
```

Process element.


</details>
<details>
<summary><code>is_frontend_url</code></summary>

```php
public is_frontend_url( $url )
```

Determine whether a URL is for the frontend.


</details>
