# AMP for WordPress

## Overview

This plugin adds support for the [Accelerated Mobile Pages](https://www.ampproject.org) (AMP) Project, which is an open source initiative that aims to provide mobile optimized content that can load instantly everywhere.

With the plugin active, all posts on your site will have dynamically generated AMP-compatible versions, accessible by appending `/amp/` to the end your post URLs. For example, if your post URL is `http://example.com/2016/01/01/amp-on/`, you can access the AMP version at `http://example.com/2016/01/01/amp-on/amp/`. If you do not have [pretty permalinks](https://codex.wordpress.org/Using_Permalinks#mod_rewrite:_.22Pretty_Permalinks.22) enabled, you can do the same thing by appending `?amp=1`, i.e. `http://example.com/2016/01/01/amp-on/?amp=1`

Note #1: that Pages and archives are not currently supported.

Note #2: this plugin only creates AMP content but does not automatically display it to your users when they visit from a mobile device. That is handled by AMP consumers such as Google Search. For more details, see the [AMP Project FAQ](https://www.ampproject.org/docs/support/faqs.html).

## Customization / Templating

The plugin ships with a default template that looks nice and clean and we tried to find a good balance between ease and extensibility when it comes to customization.

You can tweak small pieces of the template or the entire thing depending on your needs.

### Where Do I Put My Code?

The code snippets below and any other code-level customizations should happen in one of the following locations.

If you're using an off-the-shelf theme (like from the WordPress.org Theme Directory):

- A [child theme](https://developer.wordpress.org/themes/advanced-topics/child-themes/).
- A custom plugin that you activate via the Dashboard.
- A [mu-plugin](https://codex.wordpress.org/Must_Use_Plugins).

If you're using a custom theme:

- `functions.php` (or via a 'require' call to files that load from `functions.php`).
- Any of the options above.

### Theme Mods

The default template will attempt to draw from various theme mods, such as site icon, if supported by the active theme.

#### Site Icon

If you add a site icon, we will automatically replace the WordPress logo in the template.

If you'd prefer to do it via code:

```php
add_filter( 'amp_post_template_data', 'xyz_amp_set_site_icon_url' );

function xyz_amp_set_site_icon_url( $data ) {
	// Ideally a 32x32 image
	$data[ 'site_icon_url' ] = get_stylesheet_directory_uri() . '/images/amp-site-icon.png';
	return $data;
}
```

#### Logo Only

If you want to hide the site text and just show a logo, use the `amp_post_template_css` action. The following colours the title bar black, hides the site title, and replaces it with a centered logo:

```
add_action( 'amp_post_template_css', 'xyz_amp_additional_css_styles' );

function xyz_amp_additional_css_styles( $amp_template ) {
	// only CSS here please...
	?>
	nav.amp-wp-title-bar {
		padding: 12px 0;
		background: #000;
	}
	nav.amp-wp-title-bar a {
		background-image: url( 'https://example.com/path/to/logo.png' );
		background-repeat: no-repeat;
		background-size: contain;
		display: block;
		height: 28px;
		width: 94px;
		margin: 0 auto;
		text-indent: -9999px;
	}
	<?php
}
```

Note: you will need to adjust the colours and sizes based on your brand.

### Template Tweaks

You can tweak various parts of the template via code.

#### Featured Image

The default template does not display the featured image currently. There are many ways to add it, such as the snippet below:

```php
add_action( 'pre_amp_render_post', 'xyz_amp_add_custom_actions' );
function xyz_amp_add_custom_actions() {
	add_filter( 'the_content', 'xyz_amp_add_featured_image' );
}

function xyz_amp_add_featured_image( $content ) {
	if ( has_post_thumbnail() ) {
		// Just add the raw <img /> tag; our sanitizer will take care of it later.
		$image = sprintf( '<p class="xyz-featured-image">%s</p>', get_the_post_thumbnail() );
		$content = $image . $content;
	}
	return $content;
}
```

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

Note: The path must pass the default criteria set out by [`validate_file`](https://developer.wordpress.org/reference/functions/validate_file/) and must be somewhere in a subfolder of `WP_CONTENT_DIR`.

#### Schema.org (JSON) Metadata

The plugin adds some default metadata to enable ["Rich Snippet" support](https://developers.google.com/structured-data/rich-snippets/articles). You can modify this using the `amp_post_template_metadata` filter. The following changes the type annotation to `NewsArticle` (from the default `BlogPosting`) and overrides the default Publisher Logo.

```
add_filter( 'amp_post_template_metadata', 'xyz_amp_modify_json_metadata', 10, 2 );

function xyz_amp_modify_json_metadata( $metadata, $post ) {
	$metadata['@type'] = 'NewsArticle';

	$metadata['publisher']['logo'] = array(
		'@type' => 'ImageObject',
		'url' => get_template_directory_uri() . '/images/my-amp-metadata-logo.png',
		'height' => 60,
		'width' => 600,
	);

	return $metadata;
}
```

#### Template Meta (Author, Date, etc.)

For the meta section of the template (i.e. author, date, taxonomies, etc.), you can override templates for the existing sections, remove them, add new ones.

##### Example: Override Author Template from Theme

Create a folder in your theme called `amp` and add a file called `meta-author.php` with the following:

```php
<li class="xyz-byline">
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
<li class="xyz-tax-authors">
	<?php echo get_the_term_list( $this->get( 'post_id' ), 'xyz-author', '', ', ' ); ?>
</li>
```

##### Example: Remove Author from `header_meta`

This will completely remove the author section:

```php
add_filter( 'amp_post_article_header_meta', 'xyz_amp_remove_author_meta' );

function xyz_amp_remove_author_meta( $meta_parts ) {
	foreach ( array_keys( $meta_parts, 'meta-author', true ) as $key ) {
		unset( $meta_parts[ $key ] );
	}
	return $meta_parts;
}
```

##### Example: Add Comment Count to `footer_meta`

This adds a new section to display the comment count:

```php
add_filter( 'amp_post_article_footer_meta', 'xyz_amp_add_comment_count_meta' );

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

##### Rule Additions

If you want to append to the existing CSS rules (e.g. styles for a custom embed handler), you can use the `amp_post_template_css` action:

```php
add_action( 'amp_post_template_css', 'xyz_amp_my_additional_css_styles' );

function xyz_amp_my_additional_css_styles( $amp_template ) {
	// only CSS here please...
	?>
	.amp-wp-byline amp-img {
		border-radius: 0; /* we don't want round avatars! */
	}
	.my-custom-class {
		color: blue;
	}
	<?php
}
```

##### Completely Override CSS

If you'd prefer to use your own styles, you can either:

- Create a folder in your theme called `amp` and add a file called `style.php` with your custom CSS.
- Specify a custom template using the `amp_post_template_file` filter for `'style' === $type`. See the "Override" examples in the "Meta" section for examples.

Note: the file should only include CSS, not the `<style>` opening and closing tag.

#### Head and Footer

If you want to add stuff to the head or footer of the default AMP template, use the `amp_post_template_head` and `amp_post_template_footer` actions.

```php
add_action( 'amp_post_template_footer', 'xyz_amp_add_pixel' );

function xyz_amp_add_pixel( $amp_template ) {
	$post_id = $amp_template->get( 'post_id' );
	?>
	<amp-pixel src="https://example.com/hi.gif?x=RANDOM"></amp-pixel>
	<?php
}
```

#### AMP Endpoint

If you don't want to use the default `/amp` endpoint, use the `amp_query_var` filter to change it to anything else.

```php
add_filter( 'amp_query_var' , 'xyz_amp_change_endpoint' );

function xyz_amp_change_endpoint( $amp_endpoint ) {
	return 'foo';
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

* Within your `amp-custom` `style` tags, you must trigger the `amp_post_template_css` action:

```php
do_action( 'amp_post_template_css', $this );
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
			$p_nodes->item( 4 )->parentNode->insertBefore( $ad_node, $p_nodes->item( 4 ));
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

## Analytics

To output proper analytics tags, you can use the `amp_post_template_analytics` filter:

```
add_filter( 'amp_post_template_analytics', 'xyz_amp_add_custom_analytics' );
function xyz_amp_add_custom_analytics( $analytics ) {
	if ( ! is_array( $analytics ) ) {
		$analytics = array();
	}

	// https://developers.google.com/analytics/devguides/collection/amp-analytics/
	$analytics['xyz-googleanalytics'] = array(
		'type' => 'googleanalytics',
		'attributes' => array(
			// 'data-credentials' => 'include',
		),
		'config_data' => array(
			'vars' => array(
				'account' => "UA-XXXXX-Y"
			),
			'triggers' => array(
				'trackPageview' => array(
					'on' => 'visible',
					'request' => 'pageview',
				),
			),
		),
	);

	// https://www.parsely.com/docs/integration/tracking/google-amp.html
	$analytics['xyz-parsely'] = array(
		'type' => 'parsely',
		'attributes' => array(),
		'config_data' => array(
			'vars' => array(
				'apikey' => 'YOUR APIKEY GOES HERE',
			)
		),
	);

	return $analytics;
}
```

Each analytics entry must include a unique array key and the following attributes:

- `type`: `(string)` one of the [valid vendors](https://github.com/ampproject/amphtml/blob/master/extensions/amp-analytics/amp-analytics.md#analytics-vendors) for amp-analytics.
- `attributes`: `(array)` any [additional valid  attributes](https://github.com/ampproject/amphtml/blob/master/extensions/amp-analytics/amp-analytics.md#attributes) to add to the `amp-analytics` element.
- `config_data`: `(array)` the [config data](https://github.com/ampproject/amphtml/blob/master/extensions/amp-analytics/amp-analytics.md#configuration) to include in the `amp-analytics` script tag. This is `json_encode`-d on output.

## Custom Post Type Support

By default, the plugin only creates AMP content for posts. You can add support for other post_types using the post_type parameter used when registering the custom post type (assume our post_type is `xyz-review`):

```php
add_action( 'amp_init', 'xyz_amp_add_review_cpt' );
function xyz_amp_add_review_cpt() {
	add_post_type_support( 'xyz-review', AMP_QUERY_VAR );
}
```

You'll need to flush your rewrite rules after this.

If you want a custom template for your post type:

```
add_filter( 'amp_post_template_file', 'xyz_amp_set_review_template', 10, 3 );

function xyz_amp_set_review_template( $file, $type, $post ) {
	if ( 'single' === $type && 'xyz-review' === $post->post_type ) {
		$file = dirname( __FILE__ ) . '/templates/my-amp-review-template.php';
	}
	return $file;
}
```

We may provide better ways to handle this in the future.

## Plugin integrations

### Jetpack

Jetpack integration is baked in. More support for things like Related Posts to come.

### Parse.ly

[Parse.ly's WordPress plugin](https://wordpress.org/plugins/wp-parsely/) automatically tracks AMP pages when enabled along with this plugin.


### Yoast SEO

If you're using [Yoast SEO](https://wordpress.org/plugins/wordpress-seo/), check out the companion plugin here: https://github.com/Yoast/yoastseo-amp

## Compatibility Issues

The following plugins have been known to cause issues with this plugin:

- Cloudflare Rocket Loader (modifies the output of the AMP page, which breaks validation.)
