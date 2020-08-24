## Method `AMP_Comment_Walker::paged_walk()`

```php
public function paged_walk( $elements, $max_depth, $page_num, $per_page, $args );
```

Output amp-list template code and place holder for comments.

### Arguments

* `\WP_Comment[] $elements` - List of comment Elements.
* `int $max_depth` - The maximum hierarchical depth.
* `int $page_num` - The specific page number, beginning with 1.
* `int $per_page` - Per page counter.
* `mixed $args` - Optional additional arguments.

### Source

:link: [includes/class-amp-comment-walker.php:88](../../includes/class-amp-comment-walker.php#L88-L98)

<details>
<summary>Show Code</summary>

```php
public function paged_walk( $elements, $max_depth, $page_num, $per_page, ...$args ) {
	if ( empty( $elements ) || $max_depth < -1 ) {
		return '';
	}
	$this->build_thread_latest_date( $elements );
	$args = array_slice( func_get_args(), 4 );
	return parent::paged_walk( $elements, $max_depth, $page_num, $per_page, $args[0] );
}
```

</details>
