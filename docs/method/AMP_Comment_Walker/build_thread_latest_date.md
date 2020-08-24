## Method `AMP_Comment_Walker::build_thread_latest_date()`

```php
protected function build_thread_latest_date( $elements, $time, $is_child = false );
```

Find the timestamp of the latest child comment of a thread to set the updated time.

### Arguments

* `\WP_Comment[] $elements` - The list of comments to get thread times for.
* `int $time` - $the timestamp to check against.
* `bool $is_child` - Flag used to set the the value or return the time.

### Source

:link: [includes/class-amp-comment-walker.php:110](../../includes/class-amp-comment-walker.php#L110-L128)

<details>
<summary>Show Code</summary>

```php
protected function build_thread_latest_date( $elements, $time = 0, $is_child = false ) {
	foreach ( $elements as $element ) {
		$children  = $element->get_children();
		$this_time = strtotime( $element->comment_date );
		if ( ! empty( $children ) ) {
			$this_time = $this->build_thread_latest_date( $children, $this_time, true );
		}
		if ( $this_time > $time ) {
			$time = $this_time;
		}
		if ( false === $is_child ) {
			$this->comment_thread_age[ $element->comment_ID ] = $time;
		}
	}
	return $time;
}
```

</details>
