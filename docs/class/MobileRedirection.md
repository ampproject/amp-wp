## Class `AmpProject\AmpWP\MobileRedirection`

Service for redirecting mobile users to the AMP version of a page.

### Methods
<details>
<summary>`register`</summary>

```php
public register()
```

Register.


</details>
<details>
<summary>`filter_default_options`</summary>

```php
public filter_default_options( $defaults )
```

Add default option.


</details>
<details>
<summary>`sanitize_options`</summary>

```php
public sanitize_options( $options, $new_options )
```

Sanitize options.


</details>
<details>
<summary>`get_current_amp_url`</summary>

```php
public get_current_amp_url()
```

Get the AMP version of the current URL.


</details>
<details>
<summary>`redirect`</summary>

```php
public redirect()
```

Add redirection logic if available for request.


</details>
<details>
<summary>`filter_amp_to_amp_linking_element_excluded`</summary>

```php
public filter_amp_to_amp_linking_element_excluded( $excluded, $url )
```

Ensure that links/forms which point to ?noamp up-front are excluded from AMP-to-AMP linking.


</details>
<details>
<summary>`filter_amp_to_amp_linking_element_query_vars`</summary>

```php
public filter_amp_to_amp_linking_element_query_vars( $query_vars, $excluded )
```

Ensure that links/forms which point to ?noamp up-front are excluded from AMP-to-AMP linking.


</details>
<details>
<summary>`is_mobile_request`</summary>

```php
public is_mobile_request()
```

Determine if the current request is from a mobile device by looking at the User-Agent request header.

This only applies if client-side redirection has been disabled.


</details>
<details>
<summary>`is_using_client_side_redirection`</summary>

```php
public is_using_client_side_redirection()
```

Determine if mobile redirection should be done via JavaScript.

If auto-redirection is disabled due to being in the Customizer preview or in AMP Dev Mode (and thus possibly in Paired Browsing), then client-side redirection is forced.


</details>
<details>
<summary>`get_mobile_user_agents`</summary>

```php
public get_mobile_user_agents()
```

Get a list of mobile user agents to use for comparison against the user agent from the current request.

Each entry may either be a simple string needle, or it be a regular expression serialized as a string in the form of `/pattern/[i]*`. If a user agent string does not match this pattern, then the string will be used as a simple string needle for the haystack.


</details>
<details>
<summary>`is_redirection_disabled_via_query_param`</summary>

```php
public is_redirection_disabled_via_query_param()
```

Determine if mobile redirection is disabled via query param.


</details>
<details>
<summary>`is_redirection_disabled_via_cookie`</summary>

```php
public is_redirection_disabled_via_cookie()
```

Determine if mobile redirection is disabled via cookie.


</details>
<details>
<summary>`set_mobile_redirection_disabled_cookie`</summary>

```php
public set_mobile_redirection_disabled_cookie( $add )
```

Sets a cookie to disable/enable mobile redirection for the current browser session.


</details>
<details>
<summary>`add_mobile_redirect_script`</summary>

```php
public add_mobile_redirect_script()
```

Output the mobile redirection Javascript code.


</details>
<details>
<summary>`add_mobile_alternative_link`</summary>

```php
public add_mobile_alternative_link()
```

Add rel=alternate link for AMP version.


</details>
<details>
<summary>`add_mobile_version_switcher_styles`</summary>

```php
public add_mobile_version_switcher_styles()
```

Print the styles for the mobile version switcher.


</details>
<details>
<summary>`add_mobile_version_switcher_link`</summary>

```php
public add_mobile_version_switcher_link()
```

Output the link for the mobile version switcher.


</details>
