## Method `AMP_Base_Sanitizer::add_buffering_hooks()`

```php
static public function add_buffering_hooks( $args = array() );
```

Add filters to manipulate output during output buffering before the DOM is constructed.

Add actions and filters before the page is rendered so that the sanitizer can fix issues during output buffering. This provides an alternative to manipulating the DOM in the sanitize method. This is a static function because it is invoked before the class is instantiated, as the DOM is not available yet. This method is only called when &#039;amp&#039; theme support is present. It is conceptually similar to the AMP_Base_Embed_Handler class&#039;s register_embed method.

### Arguments

* `array $args` - Args.

### Source

:link: [includes/sanitizers/class-amp-base-sanitizer.php:132](/includes/sanitizers/class-amp-base-sanitizer.php#L132)

<details>
<summary>Show Code</summary>

```php
public static function add_buffering_hooks( $args = [] ) {} // phpcs:ignore VariableAnalysis.CodeAnalysis.VariableAnalysis.UnusedVariable
```

</details>
