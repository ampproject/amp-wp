## Hook `amp_validation_error_source_file_editor_url_template`


Filters the template for the URL for linking to an external editor to open a file for editing.

Users of IDEs that support opening files in via web protocols can use this filter to override the edit link to result in their editor opening rather than the theme/plugin editor.
 The initial filtered value is null, requiring extension plugins to supply the URL template string themselves. If no template string is provided, links to the theme/plugin editors will be provided if available. For example, for an extension plugin to cause file edit links to open in PhpStorm, the following filter can be used:
     add_filter( &#039;amp_validation_error_source_file_editor_url_template&#039;, function () {         return &#039;phpstorm://open?file={{file}}&amp;line={{line}}&#039;;     } );
 For a template to be considered, the string &#039;{{file}}&#039; must be present in the filtered value.

### Source

:link: [includes/validation/class-amp-validation-error-taxonomy.php:2280](../../includes/validation/class-amp-validation-error-taxonomy.php#L2280)

<details>
<summary>Show Code</summary>

```php
$editor_url_template = apply_filters( 'amp_validation_error_source_file_editor_url_template', null );
```

</details>
