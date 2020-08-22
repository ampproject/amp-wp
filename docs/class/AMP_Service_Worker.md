## Class `AMP_Service_Worker`

Class AMP_Service_Worker.

### Methods
<details>
<summary><code>init</code></summary>

```php
static public init()
```

Init.


</details>
<details>
<summary><code>add_query_var</code></summary>

```php
static public add_query_var( $vars )
```

Add query var for iframe service worker request.


</details>
<details>
<summary><code>add_cdn_script_caching</code></summary>

```php
static public add_cdn_script_caching( $service_workers )
```

Add runtime caching for scripts loaded from the AMP CDN with a stale-while-revalidate strategy.


</details>
<details>
<summary><code>add_image_caching</code></summary>

```php
static public add_image_caching( $service_workers )
```

Add runtime image caching from the origin with a cache-first strategy.


</details>
<details>
<summary><code>add_google_fonts_caching</code></summary>

```php
static public add_google_fonts_caching( $service_workers )
```

Add runtime caching of Google Fonts with stale-while-revalidate strategy for stylesheets and cache-first strategy for webfont files.


</details>
<details>
<summary><code>get_precached_script_cdn_urls</code></summary>

```php
static public get_precached_script_cdn_urls()
```

Register URLs that will be precached in the runtime cache. (Yes, this sounds somewhat strange.)

Note that the PWA plugin handles the precaching of custom logo, custom header, and custom background. The PWA plugin also handles precaching &amp; serving of the offline/500 error pages and enabling navigation preload.


</details>
<details>
<summary><code>add_install_hooks</code></summary>

```php
static public add_install_hooks()
```

Add hooks to install the service worker from AMP page.


</details>
<details>
<summary><code>install_service_worker</code></summary>

```php
static public install_service_worker()
```

Install service worker(s).


</details>
<details>
<summary><code>handle_service_worker_iframe_install</code></summary>

```php
static public handle_service_worker_iframe_install()
```

Handle request to install service worker via iframe.


</details>
