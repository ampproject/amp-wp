## Filter `amp_supportable_templates`

```php
apply_filters( 'amp_supportable_templates', $templates );
```

Filters list of supportable templates.

Each array item should have a key that corresponds to a template conditional function. If the key is such a function, then the key is used to evaluate whether the given template entry is a match. Otherwise, a supportable template item can include a callback value which is used instead. Each item needs a &#039;label&#039; value. Additionally, if the supportable template is a subset of another condition (e.g. is_singular &gt; is_single) then this relationship needs to be indicated via the &#039;parent&#039; value.

### Arguments

* `array $templates` - Supportable templates.

### Source

:link: [includes/class-amp-theme-support.php:789](/includes/class-amp-theme-support.php#L789)

<details>
<summary>Show Code</summary>

```php
$templates = apply_filters( 'amp_supportable_templates', $templates );
```

</details>
