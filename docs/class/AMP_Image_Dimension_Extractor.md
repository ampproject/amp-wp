## Class `AMP_Image_Dimension_Extractor`

Class with static methods to extract image dimensions.

### Methods
* `extract`

<details>

```php
static public extract( $urls )
```

Extracts dimensions from image URLs.


</details>
* `normalize_url`

<details>

```php
static public normalize_url( $url )
```

Normalizes the given URL.

This method ensures the URL has a scheme and, if relative, is prepended the WordPress site URL.


</details>
* `register_callbacks`

<details>

```php
static private register_callbacks()
```

Registers the necessary callbacks.


</details>
* `extract_by_downloading_images`

<details>

```php
static public extract_by_downloading_images( $dimensions, $mode = false )
```

Extract dimensions from downloaded images (or transient/cached dimensions from downloaded images)


</details>
* `determine_which_images_to_fetch`

<details>

```php
static private determine_which_images_to_fetch( $dimensions, $urls_to_fetch )
```

Determine which images to fetch by checking for dimensions in transient/cache.

Creates a short lived transient that acts as a semaphore so that another visitor doesn&#039;t trigger a remote fetch for the same image at the same time.


</details>
* `fetch_images`

<details>

```php
static private fetch_images( $urls_to_fetch, $images )
```

Fetch dimensions of remote images


</details>
* `process_fetched_images`

<details>

```php
static private process_fetched_images( $urls_to_fetch, $images, $dimensions, $transient_expiration )
```

Determine success or failure of remote fetch, integrate fetched dimensions into url to dimension mapping, cache fetched dimensions via transient and release/delete semaphore transient


</details>
* `get_default_user_agent`

<details>

```php
static public get_default_user_agent()
```

Get default user agent


</details>
