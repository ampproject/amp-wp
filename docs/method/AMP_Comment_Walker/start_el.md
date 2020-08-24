## Method `AMP_Comment_Walker::start_el()`

```php
public function start_el( $output, $comment, $depth, $args = array(), $id );
```

Starts the element output.

### Arguments

* `string $output` - Used to append additional content. Passed by reference.
* `\WP_Comment $comment` - Comment data object.
* `int $depth` - Optional. Depth of the current comment in reference to parents. Default 0.
* `array $args` - Optional. An array of arguments. Default empty array.
* `int $id` - Optional. ID of the current comment. Default 0 (unused).

### Source

:link: [includes/class-amp-comment-walker.php:54](../../includes/class-amp-comment-walker.php#L54-L72)

<details>
<summary>Show Code</summary>

```php
public function start_el( &$output, $comment, $depth = 0, $args = [], $id = 0 ) {
	$new_out = '';
	parent::start_el( $new_out, $comment, $depth, $args, $id );
	if ( 'div' === $args['style'] ) {
		$tag = '<div';
	} else {
		$tag = '<li';
	}
	$new_tag = $tag . ' data-sort-time="' . esc_attr( strtotime( $comment->comment_date ) ) . '"';
	if ( ! empty( $this->comment_thread_age[ $comment->comment_ID ] ) ) {
		$new_tag .= ' data-update-time="' . esc_attr( $this->comment_thread_age[ $comment->comment_ID ] ) . '"';
	}
	$output .= $new_tag . substr( ltrim( $new_out ), strlen( $tag ) );
}
```

</details>
