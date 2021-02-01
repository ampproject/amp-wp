## Function `_amp_bootstrap_customizer`

```php
function _amp_bootstrap_customizer();
```

Bootstraps the AMP customizer.

Uses the priority of 12 for the &#039;after_setup_theme&#039; action. Many themes run `add_theme_support()` on the &#039;after_setup_theme&#039; hook, at the default priority of 10. And that function&#039;s documentation suggests adding it to that action. So this enables themes to `add_theme_support( AMP_Theme_Support::SLUG )`. And `amp_init_customizer()` will be able to recognize theme support by calling `amp_is_canonical()`.

### Source

:link: [includes/amp-helper-functions.php:557](../../includes/amp-helper-functions.php#L557-L559)

<details>
<summary>Show Code</summary>

```php
function _amp_bootstrap_customizer() {
	add_action( 'after_setup_theme', 'amp_init_customizer', 12 );
}
```

</details>
