<a href="https://amp.dev/">
    <img src="https://ps.w.org/amp/assets/icon-256x256.png" alt="AMP logo" title="AMP" align="right" height="60" />
</a>

# AMP Optimizer for PHP

AMP Optimizer is a library for doing server-side optimization to AMP markup by automatically applying [AMP performance best practices](https://amp.dev/documentation/guides-and-tutorials/optimize-and-measure/optimize_amp?format=websites) and enabling [AMP server-side-rendering](https://amp.dev/documentation/guides-and-tutorials/optimize-and-measure/server-side-rendering?format=websites).

<!-- TODO: Provide shields once we have a separate repository -->

<details>
  <summary><strong>Table of Contents</strong> (click to expand)</summary>

- [Conceptual Overview](#conceptual-overview)
- [Installation](#installation)
- [Basic Usage](#basic-usage)
- [Usage with a DOM Representation](#usage-with-a-dom-representation)
- [Working with Errors](#working-with-errors)
- [Included Transformers](#included-transformers)
- [Configuring the Transformers](#configuring-the-transformers)
- [Creating a Custom Transformer](#creating-a-custom-transformer)
- [Making a Custom Transformer Configurable](#making-a-custom-transformer-configurable)
- [Transformers Requesting External Data](#transformers-requesting-external-data)
- [Adapting the Handling of Remote Requests](#adaptingthe-handling-of-remote-requests)

</details>

## Conceptual Overview

<!-- TODO: Once the repositories have been extracted out, all PHP references should link to the corresponding files in the repositories -->

The AMP Optimizer is a `AmpProject\Optimizer\TransformationEngine` object that sets up a pipeline of consecutive `AmpProject\Optimizer\Transformer` objects. The engine takes unoptimized input in the form of either a HTML markup string or an `AmpProject\Dom\Document` object and turns it into an optimized HTML markup string.

During the process, errors might occur that make parts of the optimization impossible. These are collected within an `AmpProject\Optimizer\ErrorCollection` object that you can then iterate over to find out more and provide feedback as needed.

![Diagram showing the conceptual parts of the AMP Optimizer](https://via.placeholder.com/1200x400.png?text=Conceptual overview diagram)

## Installation

> Note: This is preliminary documentation. The following installation steps will not work until the library has been extracted into its own repository. Right now, it is only usable from within the [AMP for WordPress](https://wordpress.org/plugins/amp/) plugin.

The AMP Optimizer is a library that you can pull into your project via the [Composer PHP package manager](https://getcomposer.org/):

```BASH
composer require ampproject/optimizer
```

## Basic Usage

The following code snippet shows the most basic way of using the AMP Optimizer:

```php
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\TransformationEngine;

$transformationEngine = new TransformationEngine();    // 1.
$errorCollection      = new ErrorCollection;           // 2.

$optimizedHtml = $transformationEngine->optimizeHtml(  // 3.
    $unoptimizedHtml,                                  // 4.
    $errorCollection                                   // 5.
);
```

1. First we instantiate the transformation engine itself.
2. Then we instantiate an `AmpProject\Optimizer\ErrorCollection` object as we need a "bag" to collect the errors in and pass them around.
3. As a final step, we store the result of calling the trasnformation engine's `optimizeHtml()` method, which requires...
4. ... the unoptimized input HTML markup as a string and ...
5. ... the empty error collection we've already instantiated. After the transformation engine ran, this collection will contain all errors that were encountered during the transformation.

## Usage with a DOM Representation

If you already have a DOM representation, there's no need to save it as HTML first to use it with the transformation engine. The transformation engine accepts an `AmpProject\Dom\Document` object[^1] directly via its `optimizeDom()` method.

If you have a regular PHP built-in `DOMDocument` instead, you can turn it into an `AmpProject\Dom\Document` using `AmpProject\Dom\Document::fromNode()`.

```php
use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\TransformationEngine;

if (! $dom instanceof Document) {
    $dom = Document::fromNode($dom);
}

$transformationEngine = new TransformationEngine();
$errorCollection      = new ErrorCollection;
$transformationEngine->optimizeDom($unoptimizedDom, $errorCollection);
```

Do note that the `optimizeDom()` doesn't have a return value, as it changes the provided `AmpProject\Dom\Document` in-place.

## Working with Errors

The `AmpProject\Optimizer\ErrorCollection` that you pass into the transformation engine's `optimizeHtml()` or `optimizeDom()`  method should ideally stay empty after the optimiztation pass.

To check whether errors were found, you can iterate over the collection, which will provide you with 0 or more `AmpProject\Optimizer\Error` objects.

```php
$errorCollection = new ErrorCollection;

// Do the transformation here, while passing in the $errorCollection object.

foreach ($errorCollection as $error) {
	printf(
	    "Error code: %s\nError Message: %s\n",
	    $error->getCode(),
	    $error->getMessage()
	);
}
```

A quick count of the errors can be done for early returns as needed:

```php
if ($errorCollection->count() > 0) {
	$this->log('The AMP serverside optimization process produced one or more errors.');
}
```

You can check whether the collection of errors contains an error with a specific code as well. The current convention is that all errors have their class shortname (the class name without the namespace) as the error code.

```php
if ($errorCollection->has('CannotRemoveBoilerplate')) {
	$this->log('The boilerplate was not removed by the Optimizer.');
}
```

Note that this only lets you check whether an error "category" popped up. It can be one or more errors with that same code. If you need a more detailed check, you should iterate over the collection instead.

## Included Transformers

| Class (short name) | Description |
|-------|-------------|
| `AmpBoilerplate` | Transformer that removes AMP boilerplate `<style>` and `<noscript>` tags in `<head>`, keeping only the `amp-custom` style tag. It then (re-)inserts the `amp-boilerplate` unless the document is marked with the `i-amphtml-no-boilerplate` attribute. |
| `AmpRuntimeCss` | Transformer adding `https://cdn.ampproject.org/v0.css` if server-side-rendering is applied (known by the presence of the `<style amp-runtime>` tag). AMP runtime css (`v0.css`) will always be inlined as it'll get automatically updated to the latest version once the AMP runtime has loaded. |
| `ReorderHead` | Transformer applying the head reordering transformations to the HTML input. `ReorderHead` reorders the children of `<head>`. Specifically, it orders the `<head>` like so:<br>(0) `<meta charset>` tag<br>(1) `<style amp-runtime>` (inserted by `AmpRuntimeCss`)<br>(2) remaining `<meta>` tags (those other than `<meta charset>`)<br>(3) AMP runtime `.js` `<script>` tag<br>(4) AMP viewer runtime `.js` `<script>`<br>(5) `<script>` tags that are render delaying<br>(6) `<script>` tags for remaining extensions<br>(7) `<link>` tag for favicons<br>(8) `<link>` tag for resource hints<br>(9) `<link rel=stylesheet>` tags before `<style amp-custom>`<br>(10) `<style amp-custom>`<br>(11) any other tags allowed in `<head>`<br>(12) AMP boilerplate (first `<style>` boilerplate, then `<noscript>`) |
| `ServerSideRendering` | Transformer applying the server-side rendering transformations to the HTML input. This does immediately on the server what would normally be done on the client _after_ the runtime was downloaded and executed to process the DOM. As such, it allows for the removal of the boilerplate CSS that _hides_ the page while it has not yet been processed on the client, drastically improving time it takes for the First Contentful Paint (FCP).|
| `TransformedIdentifier` | Transformer applying the transformed identifier transformations to the HTML input. This is what marks an AMP document as "already optimized", so that the AMP runtime does not need to process it anymore. |

## Configuring the Transformers

You can inject a configuration object into the `AmpProject\Optimizer\TransformationEngine` to override the default configuration.

The main `AmpProject\Optimizer\Configuration` object will provide the list of transformers to use, as well as give access to child objects it stores that are Transformaer-specific configuration objects.

To override the list of transformers to use, you can provide an array containing the `AmpProject\Optimizer\Configuration::KEY_TRANSFORMERS` key.

```php
use AmpProject\Optimizer\Configuration;
use AmpProject\Optimizer\TransformationEngine;
use AmpProject\Optimizer\Transformer;

$configurationData = [
	Configuration::KEY_TRANSFORMERS => [
		Transformer\ServerSideRendering::class,
		Transformer\AmpRuntimeCss::class,
		Transformer\TransformedIdentifier::class,
	],
];

$transformationEngine = new TransformationEngine(
	new Configuration($configurationData);
);
```

Configuration values for the transformers can be stored under the fully qualified class name of these transformers. This can be easily done by using their `::class` constant.

They will also usually provide publically accessible constants for the known configuration keys as well.

In the following example, we configure the `AmpProject\Optimizer\AmpRuntimeCss` transformer by setting its `'canary'` option to `true` (which would default to false).

```php
use AmpProject\Optimizer\Configuration;
use AmpProject\Optimizer\TransformationEngine;
use AmpProject\Optimizer\Transformer;

$configurationData = [
	Transformer\AmpRuntimeCss::class => [
		Configuration\AmpRuntimeCssConfiguration::CANARY => true,
	],
];

$transformationEngine = new TransformationEngine(
	new Configuration($configurationData);
);
```

## Creating a Custom Transformer

A custom transformer is at the most basic level an object that implements the `AmpProject\Optimizer\Transformer` interface.

This means it needs to have at the very least the following method:

```php
public function transform(Document $document, ErrorCollection $errors)
{
	// Apply transformations to the provided $document and ...

	// ... add any encountered errors to the $errors collection.
}
```

To make this transformer then known to the transformation engine, you add it to the `AmpProject\Optimizer\Configuration::KEY_TRANSFORMERS` key of the `AmpProject\Optimizer\Configuration` object you pass into it.

```php
use AmpProject\Optimizer\Configuration;
use AmpProject\Optimizer\TransformationEngine
use MyProject\MyCustomTransformer;

$configurationData = [
	Configuration::KEY_TRANSFORMERS => array_merge(
		Configuration::DEFAULT_TRANSFORMERS,
		[
			MyCustomTransformer::class
		],
	),
];

$transformationEngine = new TransformationEngine(
	new Configuration($configurationData);
);
```

## Making a Custom Transformer Configurable

Configuration objects for the individual transformers need to be registered with the main `AmpProject\Optimizer\Configuration` object using its `registerConfigurationClass()` method, which takes a fully qualified class name of the transformer as well as a fully qualified class name of the corresponding configuration object as its two arguments.

The configuration objects for the transformers that ship with this library are already registered by default. But if you add third-party or custom transformers, you'll need to register whatever configuration objects they might need with the main `AmpProject\Optimizer\Configuration` object first.

In the following example, we add a new `MyProject\MyCustomTransformer` transformer in addition to the default set and configure it with a default value, and then we register its corresponding configuration object to make sure the configuration can be properly validated and passed around.

```php
use AmpProject\Optimizer\Configuration;
use AmpProject\Optimizer\TransformationEngine
use MyProject\MyCustomTransformer;
use MyProject\MyCustomTransformerConfiguration;

$configurationData = [
	Configuration::KEY_TRANSFORMERS => array_merge(
		Configuration::DEFAULT_TRANSFORMERS,
		[
			MyCustomTransformer::class
		],
	),
	MyCustomTransformer::class => [
		MyCustomTransformerConfiguration::SOME_CONFIG_KEY = 'some value';
	],
];

$configuration = new Configuration($configurationData);

$configuration->registerConfigurationClass(
	MyCustomTransformer::class,
	MyCustomTransformerConfiguration::class
);

$transformationEngine = new TransformationEngine(configuration);
```

For the wiring to work correctly, the `MyProject\MyCustomTransformer` class should accept within its constructor an object implementing the `AmpProject\Optimizer\TransformerConfiguration` interface as its first argument and it should implement the `AmpProject\Optimizer\Configurable` interface to let the transformation engine know it expects a configuration object.

The `MyProject\MyCustomTransformerConfiguration` class should then implement that same `AmpProject\Optimizer\TransformerConfiguration` interface. For convenience, it can do so easily by extending the abstract `AmpProject\Optimizer\Configuration\BaseTransformerConfiguration` base class.

The configuration object will then be automatically injected into the transformer's constructor as needed.

Here's an example configuration class for our custom `MyProject\MyCustomTransformer` transformer:

```php
namespace MyProject;

use AmpProject\Optimizer\BaseTransformerConfiguration;

final class MyCustomTransformerConfiguration extends BaseTransformerConfiguration
{
	const SOME_CONFIG_KEY = 'some_config_key';

	protected function getAllowedKeys()
	{
		return [
			self::SOME_CONFIG_KEY => 'default value',
		];
	}

	protected function validate($key, $value)
	{
		switch ($key) {
			case self::SOME_CONFIG_KEY:
				// Validate configuration value here.
		}

		return $value;
	}
}
```

Here's how the transformer itself can accept and make use of the configuration object:

```php
namespace MyProject;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\Configurable;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\TransformerConfiguration;
use AmpProject\Optimizer\Transformer;

final class MyCustomTransformer implements Transformer, Configurable
{
	private $configuration;

	public function __construct(TransformerConfiguration $configuration)
	{
		$this->configuration = $configuration;
	}

	public function transform(Document $document, ErrorCollection $errors)
	{
		// Bogus transformer logic that adds the configuration value as a body attribute.
		$document->body->setAttribute(
			'data-my-custom-transformer-body-attribute,
			$this->configuration->get(
				MyCustomTransformerConfiguration::SOME_CONFIG_KEY
			)
		);
	}
}
```

## Transformers Requesting External Data

In case your transformer needs to make remote requests to fetch external data (like the `AmpProject\Optimizer\Transformer\AmpRuntimeCss` does for fetching the latest version of the CSS to inline), you should make use of the provided `AmpProject\Optimizer\MakesRemoteRequests` abstraction.

This abstraction allows code outside of the transformation engine to control the specific conditions and limits that govern these remote request, like for example throttling them or integrating them with the caching subsystem of the framework in use.

To add support for this abstraction, the transformer needs to implement the `AmpProject\Optimizer\MakesRemoteRequests` marker interface and accept an `AmpProject\RemoteGetRequest` object via its constructor.

<!-- TODO: This reflects code changes that are yet to be merged via https://github.com/ampproject/amp-wp/issues/4612 -->
<table>
<tr>
<td>❗️</td><td>If the object implements <strong>both</strong> the <code>AmpProject\Optimizer\MakesRemoteRequests</code> and the <code>AmpProject\Optimizer\Configurable</code> interfaces, the <code>AmpProject\RemoteGetRequest</code> needs to be the <strong>second</strong> argument, <strong>after</strong> the <code>AmpProject\Optimizer\TransformerConfiguration</code> object.<br>This requirement stems from the fact that the Optimizer package is not tied to any given auto-wiring dependency resolution.</td>
</tr>
</table>

```php
namespace MyProject;

use AmpProject\Dom\Document;
use AmpProject\Optimizer\ErrorCollection;
use AmpProject\Optimizer\MakesRemoteRequests;
use AmpProject\Optimizer\RemoteGetRequest;
use AmpProject\Optimizer\Transformer;

final class MyCustomTransformer implements Transformer, MakesRemoteRequests
{
	const END_POINT = 'https://example.com/some_endpoint/';

	private $remoteRequest;

	public function __construct(RemoteGetRequest $remoteRequest)
	{
		$this->remoteRequest = $remoteRequest;
	}

	public function transform(Document $document, ErrorCollection $errors)
	{
		try {
			$response = $this->remoteRequest->get(self::END_POINT);
		} catch (Throwable $exception) {
			// Add error handling here.
		}

		$statusCode = $response->getStatusCode();

		if (200 < $statusCode || $statusCode >= 300) {
			// Add error handling here.
		}

		$content = $response->getBody();
		
		// Make use of the $content you've just retrieved from an external source.
	}
}
```

## Adapting the Handling of Remote Requests

The implementation to use for fulfilling requests made via the `AmpProject\RemoteGetRequest` interface can be injected into the `AmpProject\Optimizer\TransformationEngine` via its second, optional argument:

```php
use AmpProject\Optimizer\Configuration;
use AmpProject\Optimizer\TransformationEngine;

$transformationEngine = new TransformationEngine(
	new Configuration(),

	// A custom implementation that lets you control how eremote requests are handled.
	new MyCustomRemoteGetRequestImplementation()
);
```

If this optional second argument is not provided when instancing the transformation engine, the default `AmpProject\RemoteRequest\CurlRemoteGetRequest` implementation is used.

There are other implementations already provided via the `ampproject/common` library that can be useful:

<!-- TODO: The documentation for these transports should go into the `ampproject/common` library instead, as they are part of that package. For completeness sake for the optimizer, we're putting them here for now while the other package has no documentation yet. -->

| Class (short name) | Description |
|-------|-------------|
| `CurlRemoteGetRequest` | Remote request transport using cURL. This is the default implementation that will be used if you don't provide one explicitly.<br><br>It has the following configuration settings as constructor arguments:<br>**$sslVerify** - Whether to verify SSL certificates. Defaults to true.<br>**$timeout** - Timeout value to use in seconds. Defaults to 10.<br>**$retries** - Number of retry attempts to do if an error code was thrown that is worth retrying. Defaults to 2. |
| `FallbackRemoteGetRequest` | Fallback pipeline implementation to go through a series of fallback requests until a request succeeds. The request will be tried with the first instance provided, and follow the instance series from one to the next until a successful response was returned.<br><br>It has the following configuration settings as constructor arguments:<br>**...$pipeline** - Variadic array of RemoteGetRequest instances to use as consecutive fallbacks. |
| `FilesystemRemoteGetRequest` | Fetch the response for a remote request from the local filesystem instead. This can be used to provide offline fallbacks.<br><br>It has the following configuration settings as constructor arguments:<br>**$argumentMap** - Associative array of data for mapping between provided URLs and the filepaths they should map to. |
| `StubbedRemoteGetRequest` | Stub for simulating remote requests. This is mainly used for writing tests.<br><br>It has the following configuration settings as constructor arguments:<br>**$argumentMap** - Associative array of data for mapping between provided URLs and the body content they should return. |

The following code shows an example of how to use a remote request via cURL while falling back to files stored on the disk when an external request fails (probably due to network issues).

```php
use AmpProject\Optimizer\Configuration;
use AmpProject\Optimizer\TransformationEngine;
use AmpProject\RemoteRequest\CurlRemoteGetRequest;
use AmpProject\RemoteRequest\FallbackRemoteGetRequest;
use AmpProject\RemoteRequest\FilesystemRemoteGetRequest;

const FALLBACK_MAPPING = [
	'https://example.com/some_endpoint/' => __DIR__ . '/../fallback_files/some_endpoint.json',
];

$remoteRequest = new FallbackRemoteGetRequest(
	new CurlRemoteGetRequest(true, 5, 0),                  // 5 second timeout with no retries, and ...
	new FilesystemRemoteGetRequest(self::FALLBACK_MAPPING) // ... fall back to shipped files.
);

$transformationEngine = new TransformationEngine(new Configuration(), $remoteRequest);
```

To build your own transport, you'll need to implement the `AmpProject\RemoteGetRequest` interface. For a more involved example of a custom transport or for integrating with your stack of choice, see the two implementations provided by the `Amp for WordPress` WordPress plugin:

- [`AmpProject\AmpWP\RemoteRequest\CachedRemoteGetRequest`](https://github.com/ampproject/amp-wp/blob/develop/src/RemoteRequest/CachedRemoteGetRequest.php)
- [`AmpProject\AmpWP\RemoteRequest\WpHttpRemoteGetRequest`](https://github.com/ampproject/amp-wp/blob/develop/src/RemoteRequest/WpHttpRemoteGetRequest.php)

<!-- TODO: Add information about how to contribute -->

<!-- TODO: Add licensing information -->
