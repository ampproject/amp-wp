## Filter `amp_to_amp_linking_element_excluded`

```php
apply_filters( 'amp_to_amp_linking_element_excluded', $excluded, $url, $rel, $element );
```

Filters whether AMP-to-AMP is excluded for an element.

The element may be either a link (`a` or `area`) or a `form`.

### Arguments

* `bool $excluded` - Excluded. Default value is whether element already has a `noamphtml` link relation or the URL is among `excluded_urls`.
* `string $url` - URL considered for exclusion.
* `string[] $rel` - Link relations.
* `\DOMElement $element` - The element considered for excluding from AMP-to-AMP linking. May be instance of `a`, `area`, or `form`.

### Source

:link: [includes/sanitizers/class-amp-link-sanitizer.php:216](/includes/sanitizers/class-amp-link-sanitizer.php#L216)

<details>
<summary>Show Code</summary>

```php
$excluded = (bool) apply_filters( 'amp_to_amp_linking_element_excluded', $excluded, $url, $rel, $element );
```

</details>
