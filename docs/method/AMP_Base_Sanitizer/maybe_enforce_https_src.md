## Method `AMP_Base_Sanitizer::maybe_enforce_https_src()`

```php
public function maybe_enforce_https_src( $src, $force_https = false );
```

Decide if we should remove a src attribute if https is required.

If not required, the implementing class may want to try and force https instead.

### Arguments

* `string $src` - URL to convert to HTTPS if forced, or made empty if $args[&#039;require_https_src&#039;].
* `boolean $force_https` - Force setting of HTTPS if true.

