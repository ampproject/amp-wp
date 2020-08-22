## Class `AMP_Image_Dimension_Extractor`

Class with static methods to extract image dimensions.

### Methods
<details>
<summary>`extract`</summary>

```php
static public extract( $urls )
```

Extracts dimensions from image URLs.


</details>
<details>
<summary>`normalize_url`</summary>

```php
static public normalize_url( $url )
```

Normalizes the given URL.

This method ensures the URL has a scheme and, if relative, is prepended the WordPress site URL.


</details>
<details>
<summary>`register_callbacks`</summary>

```php
static private register_callbacks()
```

Registers the necessary callbacks.


</details>
<details>
<summary>`extract_by_downloading_images`</summary>

```php
static public extract_by_downloading_images( $dimensions, $mode = false )
```

Extract dimensions from downloaded images (or transient/cached dimensions from downloaded images)


</details>
<details>
<summary>`determine_which_images_to_fetch`</summary>

```php
static private determine_which_images_to_fetch( $dimensions, $urls_to_fetch )
```

Determine which images to fetch by checking for dimensions in transient/cache.

Creates a short lived transient that acts as a semaphore so that another visitor doesn&#039;t trigger a remote fetch for the same image at the same time.


</details>
<details>
<summary>`fetch_images`</summary>

```php
static private fetch_images( $urls_to_fetch, $images )
```

Fetch dimensions of remote images


</details>
<details>
<summary>`process_fetched_images`</summary>

```php
static private process_fetched_images( $urls_to_fetch, $images, $dimensions, $transient_expiration )
```

Determine success or failure of remote fetch, integrate fetched dimensions into url to dimension mapping, cache fetched dimensions via transient and release/delete semaphore transient


</details>
<details>
<summary>`get_default_user_agent`</summary>

```php
static public get_default_user_agent()
```

Get default user agent


</details>
