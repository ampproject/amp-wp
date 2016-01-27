# AMP for WordPress

## Overview

This plugin adds support for the Accelerated Mobile Pages (AMP) Project, which is an an open source initiative that aims to provide mobile optimized content that can load instantly everywhere.

With the plugin active, all content on your site will have dynamically generated AMP-compatible versions, accessible by appending `/amp/` to the end your permalinks. (If you do not have pretty permalinks enabled, you can do the same thing by appending `?amp=1`.)

Developers: please note that this plugin is still in early stages and the underlying APIs (like filters, classes, etc.) may change.

## Customization / Templating

The plugin ships with a default template that looks nice and clean and we tried to find a good balance between ease and extensibility when it comes to customization.

You can tweak small pieces of the template or the entire thing depending on your needs.

### Theme Mods

(This still needs to be implemented.)

The default template will attempt to draw from various theme mods, such as site icon and background and header color/image, if supported by the active theme.

### Template Tweaks

You can tweak various parts of the template via code.

#### Content Width

By default, your theme's `$content_width` value will be used to determine the size of the `amp` content well. You can change this:

```php
add_filter( 'amp_content_max_width', 'xyz_amp_change_content_width' );

function xyz_amp_change_content_width( $content_max_width ) {
	return 1200;
}
```

#### Template Data

Use the `amp_post_template_data` filter to override default template data. The following changes the placeholder image used for iframes to a file located in the current theme:

```php
add_filter( 'amp_post_template_data', 'xyz_amp_set_custom_placeholder_image' );

function xyz_set_custom_placeholder_image( $data ) {
	$data[ 'placeholder_image_url' ] = get_stylesheet_directory_uri() . '/images/amp-iframe-placeholder.png';
	return $data;
}
```

Note: The path must pass the default criteria set out by `[validate_file](https://developer.wordpress.org/reference/functions/validate_file/)` and must

#### Meta

For the meta section of the template (i.e. author, date, taxonomies, etc.), you can override templates for the existing sections, remove them, add new ones.

##### Example: Override Author Template from Theme

Create a folder in your theme called `amp` and add a file called `meta-author.php` with the following:

```php
<li class="byline">
	<span>Anonymous</span>
</li>
```

Replace the contents, as needed.

##### Example: Override Taxonomy Template via filter

This will load the file `t/meta-custom-tax.php` for the `taxonomy` section:

```php
add_filter( 'amp_post_template_file', 'xyz_amp_set_custom_tax_meta_template', 10, 3 );

function xyz_amp_set_custom_tax_meta_template( $file, $type, $post ) {
	if ( 'meta-taxonomy' === $type ) {
		$file = dirname( __FILE__ ) . '/t/meta-custom-tax.php';
	}
	return $file;
}
```

In `t/meta-custom-tax.php`, you can add something like the following to replace the default category and tags with your custom `author` taxonomy:

```php
<li class="tax-authors">
	<?php echo get_the_term_list( $this->get( 'post_id' ), 'xyz-author', '', ', ' ); ?>
</li>
```

##### Example: Remove Author from `meta`

This will completely remove the author section:

```php
add_filter( 'amp_post_template_meta_parts', 'xyz_amp_remove_author_meta' );

function xyz_amp_remove_author_meta( $meta_parts ) {
	foreach ( array_keys( $meta_parts, 'meta-author', true ) as $key ) {
		unset( $meta_parts[ $key ] );
	}
	return $meta_parts;
}
```

##### Example: Add Comment Count to `meta`

This adds a new section to display the comment count:

```php
add_filter( 'amp_post_template_meta_parts', 'xyz_amp_add_comment_count_meta' );

function xyz_amp_add_comment_count_meta( $meta_parts ) {
	$meta_parts[] = 'xyz-meta-comment-count';
	return $meta_parts;
}

add_filter( 'amp_post_template_file', 'xyz_amp_set_comment_count_meta_path', 10, 3 );

function xyz_amp_set_comment_count_meta_path( $file, $type, $post ) {
	if ( 'xyz-meta-comment-count' === $type ) {
		$file = dirname( __FILE__ ) . '/templates/xyz-meta-comment-count.php';
	}
	return $file;
}
```

Then, in `templates/xyz-meta-comment-count.php`:

```php
<li>
	<?php printf( _n( '%d comment', '%d comments', $this->get( 'post' )->comment_count, 'xyz-text-domain' ) ); ?>
</li>
```

#### Custom CSS

If you'd prefer to use your own styles, you can either:

- Create a folder in your theme called `amp` and add a file called `style.php` with your custom CSS.
- Specify a custom template using the `amp_post_template_file` filter for `'style' === $type`.

See the "Override" examples in the "Meta" section for examples.

#### Head and Footer

If you want to add stuff to the head or footer of the default AMP template, use the `amp_post_template_head` and `amp_post_template_footer` actions.

```php
add_action( 'amp_post_template_footer', 'xyz_amp_add_analytics' );

function xyz_amp_add_analytics( $amp_template ) {
	$post_id = $amp_template->get( 'post_id' );
	// see https://github.com/ampproject/amphtml/blob/master/extensions/amp-analytics/amp-analytics.md for more on amp-analytics
	?>
	<amp-analytics>
		<script type="application/json">
		{
			"requests": {
				"pageview": "https://example.com/analytics?url=${canonicalUrl}&title=${title}&acct=${account}",
				"event": "https://example.com/analytics?eid=${eventId}&elab=${eventLabel}&acct=${account}"
			}
			// ...
		}
		</script>
	</amp-analytics>
	<?php
}
```

### Custom Template

If you want complete control over the look and feel of your AMP content, you can override the default template using the `amp_post_template_file` filter and pass it the path to a custom template:

```php
add_filter( 'amp_post_template_file', 'xyz_amp_set_custom_template', 10, 3 );

function xyz_amp_set_custom_template( $file, $type, $post ) {
	if ( 'single' === $type ) {
		$file = dirname( __FILE__ ) . '/templates/my-amp-template.php';
	}
	return $file;
}
```

Note: there are some requirements for a custom template:

* You must trigger the `amp_post_template_head` action in the `<head>` section:

```
do_action( 'amp_post_template_head', $this );
```

* You must trigger the `amp_post_template_footer` action right before the `</body>` tag:

```
do_action( 'amp_post_template_footer', $this );
```

* You must include [all required mark-up](https://www.ampproject.org/docs/get_started/create/basic_markup.html) that isn't already output via the `amp_post_template_head` action.

## Handling Media

By default, the plugin attempts to gracefully handle the following media elements in your content:

- images (converted from `img` => `amp-img` or `amp-anim`)
- videos (converted from `video` => `amp-video`; Note: Flash is not supported)
- audio (converted from `audio` => `amp-audio`)
- iframes (converted from `iframes` => `amp-iframes`)
- YouTube, Instagram, Twitter, and Vine oEmbeds and shortcodes (converted from the embed to the matching `amp-` component)

For additional media content such as custom shortcodes, oEmbeds or manually inserted embeds, ads, etc. there are several customization options available and outlined below.

### Do Nothing

If your embeds/media use standard iframes, you can choose to do nothing and let the plugin handle things. They should "just work" in most cases.

### `the_content` filter

All existing hooks on `the_content` will continue to work. This can be a good or bad thing. Good, because existing plugin integrations will continue to work. Bad, because not all added content may make sense in an AMP context.

You can add additional callbacks to `the_content` filter to output additional content as needed. Use the `is_amp_endpoint()` function to check if an AMP version of a post is being viewed. However, we recommend using an Embed Handler instead.

Caveat: with this method, if you add a custom component that requires inclusion of a script, you will need to add that script manually to the template using the `amp_post_template_head` action.

### Update Existing Shortcodes

In your existing shortcode or oEmbed callbacks, you can branch using the `is_amp_endpoint()` and output customized content for AMP content.

The same caveat about scripts for custom AMP components applies.

### Custom Embed Handler

Embed Handlers are helper classes to inject AMP-specific content for your oEmbeds and shortcodes.

Embed Handlers register the embeds they handle using standard WordPress functions such as `add_shortcode`. For working examples, check out the existing implementations for Instagram, Twitter, etc. as guides to build your own.

While the primary purpose of Embed Handlers is for use with embeds, you can use them for adding AMP-specific `the_content` callbacks as well.

#### Step 1: Build the Embed Handler

Your Embed Handler class needs to extend the `AMP_Base_Embed_Handler` class.

Note: make sure to set proper priorities or remove existing callbacks for your regular content.

In `classes/class-amp-related-posts-embed.php`:

```php
class XYZ_AMP_Related_Posts_Embed extends AMP_Base_Embed_Handler {
	public function register_embed() {
		// If we have an existing callback we are overriding, remove it.
		remove_filter( 'the_content', 'xyz_add_related_posts' );

		// Add our new callback
		add_filter( 'the_content', array( $this, 'add_related_posts' ) );
	}

	public function unregister_embed() {
		// Let's clean up after ourselves, just in case.
		add_filter( 'the_content', 'xyz_add_related_posts' );
		remove_filter( 'the_content', array( $this, 'add_related_posts' ) );
	}

	public function get_scripts() {
		return array( 'amp-mustache' => 'https://cdn.ampproject.org/v0/amp-mustache-0.1.js' );
	}

	public function add_related_posts( $content ) {
		// See https://github.com/ampproject/amphtml/blob/master/extensions/amp-list/amp-list.md for details on amp-list
		$related_posts_list = '
<amp-list src="https://data.com/articles.json?ref=CANONICAL_URL" width=300 height=200 layout=responsive>
	<template type="amp-mustache">
		<div>
			<amp-img src="{{imageUrl}}" width=50 height=50></amp-img>
			{{title}}
		</div>
	</template>
	<div overflow role=button aria-label="Show more" class="list-overflow">
		Show more
	</div>
</amp-list>';

		$content .= $related_posts_list;

		return $content;
	}
}
```

#### Step 2: Load the Embed Handler

```php
add_filter( 'amp_content_embed_handlers', 'xyz_amp_add_related_embed', 10, 2 );

function xyz_amp_add_related_embed( $embed_handler_classes, $post ) {
	require_once( dirname( __FILE__ ) . '/classes/class-amp-related-posts-embed.php' );
	$embed_handler_classes[ 'XYZ_AMP_Related_Posts_Embed' ] = array();
	return $embed_handler_classes;
}
```

### Custom Sanitizer

The name "sanitizer" is a bit of a misnomer. These are primarily used internally in the plugin to make your site's content compatible with the amp spec. This involves stripping unsupported tags and attributes and transforming media elements to their matching amp version (e.g. `img` => `amp-img`).

Sanitizers are pretty versatile and, unlike Embed Handlers -- which work with HTML content as a string -- they can be used to manipulate your post's AMP content using [PHP's `DOM` library](http://php.net/manual/en/book.dom.php). We've included an example that shows you how to use a custom sanitizer to inject ads into your content. You can, of course, do many other things such as add related content.

#### Step 1: Build the Sanitizer

Your sanitizer needs to extend the `AMP_Base_Sanitizer`. In `classes/class-ad-inject-sanitizer.php`:

```php
class XYZ_AMP_Ad_Injection_Sanitizer extends AMP_Base_Sanitizer {
	public function sanitize() {
		$body = $this->get_body_node();

		// Build our amp-ad tag
		$ad_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-ad', array(
			// Taken from example at https://github.com/ampproject/amphtml/blob/master/builtins/amp-ad.md
			'width' => 300,
			'height' => 250,
			'type' => 'a9',
			'data-aax_size' => '300x250',
			'data-aax_pubname' => 'test123',
			'data-aax_src' => '302',
		) );

		// Add a placeholder to show while loading
		$fallback_node = AMP_DOM_Utils::create_node( $this->dom, 'amp-img', array(
			'placeholder' => '',
			'layout' => 'fill',
			'src' => 'https://placehold.it/300X250',
		) );
		$ad_node->appendChild( $fallback_node );

		// If we have a lot of paragraphs, insert before the 4th one.
		// Otherwise, add it to the end.
		$p_nodes = $body->getElementsByTagName( 'p' );
		if ( $p_nodes->length > 6 ) {
			$p_nodes->item( 4 )->insertBefore( $ad_node );
		} else {
			$body->appendChild( $ad_node );
		}
	}
}
```

#### Step 2: Load the Sanitizer

```php
add_filter( 'amp_content_sanitizers', 'xyz_amp_add_ad_sanitizer', 10, 2 );

function xyz_amp_add_ad_sanitizer( $sanitizer_classes, $post ) {
	require_once( dirname( __FILE__ ) . '/classes/class-ad-inject-sanitizer.php' );
	$sanitizer_classes[ 'XYZ_AMP_Ad_Injection_Sanitizer' ] = array(); // the array can be used to pass args to your sanitizer and accessed within the class via `$this->args`
	return $sanitizer_classes;
}
```
