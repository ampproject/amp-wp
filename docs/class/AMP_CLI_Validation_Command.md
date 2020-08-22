## Class `AMP_CLI_Validation_Command`

Crawls the site for validation errors or resets the stored validation errors.

### Methods
* `run`

<details>

```php
public run( $args, $assoc_args )
```

Crawl the entire site to get AMP validation results.

## OPTIONS
 [--limit=&lt;count&gt;] : The maximum number of URLs to validate for each template and content type. --- default: 100 ---
 [--include=&lt;templates&gt;] : Only validates a URL if one of the conditionals is true.
 [--force] : Force validation of URLs even if their associated templates or object types do not have AMP enabled.
 ## EXAMPLES
     wp amp validation run --include=is_author,is_tag


</details>
* `reset`

<details>

```php
public reset( $args, $assoc_args )
```

Reset all validation data on a site.

This deletes all amp_validated_url posts and all amp_validation_error terms.
 ## OPTIONS
 [--yes] : Proceed to empty the site validation data without a confirmation prompt.
 ## EXAMPLES
     wp amp validation reset --yes


</details>
* `generate_nonce`

<details>

```php
public generate_nonce()
```

Generate the authorization nonce needed for a validate request.


</details>
* `check_url`

<details>

```php
public check_url( $args )
```

Get the validation results for a given URL.

The results are returned in JSON format.
 ## OPTIONS
 &lt;url&gt; : The URL to check. The host name need not be included. The URL must be local to this WordPress install.
 ## EXAMPLES
     wp amp validation check-url /about/     wp amp validation check-url $( wp option get home )/?p=1


</details>
* `count_urls_to_validate`

<details>

```php
private count_urls_to_validate()
```

Gets the total number of URLs to validate.

By default, this only counts AMP-enabled posts and terms. But if $force_crawl_urls is true, it counts all of them, regardless of their AMP status. It also uses $this-&gt;maximum_urls_to_validate_for_each_type, which can be overridden with a command line argument.


</details>
* `get_posts_that_support_amp`

<details>

```php
private get_posts_that_support_amp( $ids )
```

Gets the posts IDs that support AMP.

By default, this only gets the post IDs if they support AMP. This means that &#039;Posts&#039; isn&#039;t deselected in &#039;AMP Settings&#039; &gt; &#039;Supported Templates&#039;. And &#039;Enable AMP&#039; isn&#039;t unchecked in the post&#039;s editor. But if $force_crawl_urls is true, this simply returns all of the IDs.


</details>
* `does_taxonomy_support_amp`

<details>

```php
private does_taxonomy_support_amp( $taxonomy )
```

Gets whether the taxonomy supports AMP.

This only gets the term IDs if they support AMP. If their taxonomy is unchecked in &#039;AMP Settings&#039; &gt; &#039;Supported Templates,&#039; this does not return them. For example, if &#039;Categories&#039; is unchecked. This can be overridden by passing the self::FLAG_NAME_FORCE_VALIDATION argument to the WP-CLI command.


</details>
* `is_template_supported`

<details>

```php
private is_template_supported( $template )
```

Gets whether the template is supported.

If the user has passed an include argument to the WP-CLI command, use that to find if this template supports AMP. For example, wp amp validation run --include=is_tag,is_category would return true only if is_tag() or is_category(). But passing the self::FLAG_NAME_FORCE_VALIDATION argument to the WP-CLI command overrides this.


</details>
* `get_posts_by_type`

<details>

```php
private get_posts_by_type( $post_type, $offset = null, $number = null )
```

Gets the IDs of public, published posts.


</details>
* `get_taxonomy_links`

<details>

```php
private get_taxonomy_links( $taxonomy, $offset = '', $number = 1 )
```

Gets the front-end links for taxonomy terms.

For example, https://example.org/?cat=2


</details>
* `get_author_page_urls`

<details>

```php
private get_author_page_urls( $offset = '', $number = '' )
```

Gets the author page URLs, like https://example.com/author/admin/.

Accepts an $offset parameter, for the query of authors. 0 is the first author in the query, and 1 is the second.


</details>
* `get_search_page`

<details>

```php
private get_search_page()
```

Gets a single search page URL, like https://example.com/?s=example.


</details>
* `get_date_page`

<details>

```php
private get_date_page()
```

Gets a single date page URL, like https://example.com/?year=2018.


</details>
* `crawl_site`

<details>

```php
private crawl_site()
```

Validates the URLs of the entire site.

Includes the URLs of public, published posts, public taxonomies, and other templates. This validates one of each type at a time, and iterates until it reaches the maximum number of URLs for each type.


</details>
* `validate_and_store_url`

<details>

```php
private validate_and_store_url( $url, $type )
```

Validates the URL, stores the results, and increments the counts.


</details>
