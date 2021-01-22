## Filter `amp_mobile_client_side_redirection`

```php
apply_filters( 'amp_mobile_client_side_redirection', $should_redirect_via_js );
```

Filters whether mobile redirection should be done client-side (via JavaScript).

If false, a server-side solution will be used instead (via PHP). It&#039;s important to verify that server-side redirection does not conflict with a site&#039;s page caching logic. To assist with this, you may need to hook into the `amp_pre_is_mobile` filter.
 Beware that disabling this will result in a cookie being set when the user decides to leave the mobile version. This may require updating the site&#039;s privacy policy or getting user consent for GDPR compliance. Nevertheless, since the cookie is not used for tracking this may not be necessary.
 Please note that this does not apply when in the Customizer preview or when in AMP Dev Mode (and thus possible Paired Browsing), since server-side redirects would not be able to be prevented as required.

### Arguments

* `bool $should_redirect_via_js` - Whether JS redirection should be used to take mobile visitors to the AMP version.

### Source

:link: [src/MobileRedirection.php:268](/src/MobileRedirection.php#L268)

<details>
<summary>Show Code</summary>

```php
return (bool) apply_filters( 'amp_mobile_client_side_redirection', true );
```

</details>
