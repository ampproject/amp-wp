## Class `AmpProject\AmpWP\ObsoleteBlockAttributeRemover`

Removes obsolete data-amp-* attributes from block markup in post content.

These HTML attributes serve as processing instructions to control how the sanitizers handle converting HTML to AMP. For each HTML attribute there is also a block attribute, so if there is a data-amp-carousel HTML attribute then there is also an ampCarousel block attribute. The block attributes were originally mirrored onto the HTML attributes because the &#039;render_block&#039; filter was not available in Gutenberg (or WordPress Core) when this was first implemented; now that this filter is available, there is no need to duplicate/mirror the attributes, and so they are injected into the root HTML element via `AMP_Core_Block_Handler::filter_rendered_block()`. In hindsight, instead of having the data mirrored between block attributes and HTML attributes, the block attributes should have perhaps used an &#039;attribute&#039; as the block attribute &#039;source&#039;. Then again, that may have complicated things yet further to migrate away from using these data attributes. A key reason for why these HTML data-* attributes are bad is that they cause block validation errors. If someone creates a Gallery block and enables a carousel, then if they go and deactivate the AMP plugin, this block will then show as having a block validation error. If, however, we restrict the block attributes to only be in the block comment, then no block validation errors occur. Also, since the &#039;render_block&#039; filter is now available, the reason for storing these block attributes as data-amp-* HTML attributes in post_content is now obsolete.

### Methods
<details>
<summary>`get_registration_action`</summary>

```php
static public get_registration_action()
```

Get registration action.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Register the service with the system.


</details>
<details>
<summary>`get_obsolete_attribute_pattern`</summary>

```php
protected get_obsolete_attribute_pattern()
```

Get obsolete attribute regular expression to match the obsolete attribute key/value pair in an HTML start tag.

.


</details>
<details>
<summary>`filter_rest_prepare_post`</summary>

```php
public filter_rest_prepare_post( WP_REST_Response $response )
```

Filter post response object to purge obsolete attributes from the raw content.


</details>
