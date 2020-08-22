## Class `AmpProject\AmpWP\Admin\ReenableCssTransientCachingAjaxAction`

Base class to define a new AJAX action.

### Methods
* `register`

	<details>

	```php
	public register()
	```

	Register the AJAX action with the WordPress system.


	</details>
* `register_ajax_script`

	<details>

	```php
	public register_ajax_script( $hook_suffix )
	```

	Register the AJAX logic.


	</details>
* `reenable_css_transient_caching`

	<details>

	```php
	public reenable_css_transient_caching()
	```

	Re-enable the CSS Transient caching.

This is triggered via an AJAX call from the Site Health panel.


	</details>
