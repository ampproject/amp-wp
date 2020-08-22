## Class `AmpProject\AmpWP\MobileRedirection`

Service for redirecting mobile users to the AMP version of a page.

### Methods
<details>
<summary><code>register</code></summary>

```php
public register()
```

Register.


</details>
<details>
<summary><code>filter_default_options</code></summary>

```php
public filter_default_options( $defaults )
```

Add default option.


</details>
<details>
<summary><code>sanitize_options</code></summary>

```php
public sanitize_options( $options, $new_options )
```

Sanitize options.


</details>
<details>
<summary><code>get_current_amp_url</code></summary>

```php
public get_current_amp_url()
```

Get the AMP version of the current URL.


</details>
<details>
<summary><code>redirect</code></summary>

```php
public redirect()
```

Add redirection logic if available for request.


</details>
<details>
<summary><code>filter_amp_to_amp_linking_element_excluded</code></summary>

```php
public filter_amp_to_amp_linking_element_excluded( $excluded, $url )
```

Ensure that links/forms which point to ?noamp up-front are excluded from AMP-to-AMP linking.


</details>
<details>
<summary><code>filter_amp_to_amp_linking_element_query_vars</code></summary>

```php
public filter_amp_to_amp_linking_element_query_vars( $query_vars, $excluded )
```

Ensure that links/forms which point to ?noamp up-front are excluded from AMP-to-AMP linking.


</details>
<details>
<summary><code>is_mobile_request</code></summary>

```php
public is_mobile_request()
```

Determine if the current request is from a mobile device by looking at the User-Agent request header.

This only applies if client-side redirection has been disabled.


</details>
<details>
<summary><code>is_using_client_side_redirection</code></summary>

```php
public is_using_client_side_redirection()
```

Determine if mobile redirection should be done via JavaScript.

If auto-redirection is disabled due to being in the Customizer preview or in AMP Dev Mode (and thus possibly in Paired Browsing), then client-side redirection is forced.


</details>
<details>
<summary><code>get_mobile_user_agents</code></summary>

```php
public get_mobile_user_agents()
```

Get a list of mobile user agents to use for comparison against the user agent from the current request.

Each entry may either be a simple string needle, or it be a regular expression serialized as a string in the form of `/pattern/[i]*`. If a user agent string does not match this pattern, then the string will be used as a simple string needle for the haystack.


</details>
<details>
<summary><code>is_redirection_disabled_via_query_param</code></summary>

```php
public is_redirection_disabled_via_query_param()
```

Determine if mobile redirection is disabled via query param.


</details>
<details>
<summary><code>is_redirection_disabled_via_cookie</code></summary>

```php
public is_redirection_disabled_via_cookie()
```

Determine if mobile redirection is disabled via cookie.


</details>
<details>
<summary><code>set_mobile_redirection_disabled_cookie</code></summary>

```php
public set_mobile_redirection_disabled_cookie( $add )
```

Sets a cookie to disable/enable mobile redirection for the current browser session.


</details>
<details>
<summary><code>add_mobile_redirect_script</code></summary>

```php
public add_mobile_redirect_script()
```

Output the mobile redirection Javascript code.


</details>
<details>
<summary><code>add_mobile_alternative_link</code></summary>

```php
public add_mobile_alternative_link()
```

Add rel=alternate link for AMP version.


</details>
<details>
<summary><code>add_mobile_version_switcher_styles</code></summary>

```php
public add_mobile_version_switcher_styles()
```

Print the styles for the mobile version switcher.


</details>
<details>
<summary><code>add_mobile_version_switcher_link</code></summary>

```php
public add_mobile_version_switcher_link()
```

Output the link for the mobile version switcher.


</details>
