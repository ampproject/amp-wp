## Hook `amp_dev_mode_enabled`

### Source

:link: [includes/amp-helper-functions.php:1405](https://github.com/ampproject/amp-wp/blob/develop/includes/amp-helper-functions.php#L1405-L1415)

<details>
<summary>Show Code</summary>

```php
return apply_filters(
	'amp_dev_mode_enabled',
	(
		// For the few sites that forcibly show the admin bar even when the user is logged out, only enable dev
		// mode if the user is actually logged in. This prevents the dev mode from being served to crawlers
		// when they index the AMP version. The theme support check disables dev mode in Reader mode.
		( is_admin_bar_showing() && is_user_logged_in() )
		||
		is_customize_preview()
	)
);
```

</details>
