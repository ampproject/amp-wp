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
	$meta_parts[] = 'xyz-comment-count';
	return $meta_parts;
}

add_filter( 'amp_post_template_file', 'xyz_amp_set_comment_count_meta_path', 10, 3 );

function xyz_amp_set_comment_count_meta_path( $file, $type, $post ) {
	if ( 'xyz-comment-count' === $type ) {
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

- Create a folder in your theme called `amp` and add a file called `single-style.php` with your custom CSS.
- Specify a custom template using the `amp_post_template_file` filter for `'single-style' === $type`.

See the "Override" examples in the "Meta" section for examples.

#### Custom Template

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

* You must trigger the `amp_post_head` action in the `<head>` section:

```
do_action( 'amp_head', $this );
```

* You must trigger the `amp_post_footer` action right before the `</body>` tag:

```
do_action( 'amp_footer', $this );
```

* You must include [all required mark-up](https://www.ampproject.org/docs/get_started/create/basic_markup.html) that isn't already output via the `amp_post_head` action.
