## Class `AMP_Comment_Walker`

Class AMP_Comment_Walker

Walker to wrap comments in mustache tags for amp-template.

### Methods
* `start_el`

	<details>

	```php
	public start_el( $output, $comment, $depth, $args = array(), $id )
	```

	Starts the element output.


	</details>
* `paged_walk`

	<details>

	```php
	public paged_walk( $elements, $max_depth, $page_num, $per_page, $args )
	```

	Output amp-list template code and place holder for comments.


	</details>
* `build_thread_latest_date`

	<details>

	```php
	protected build_thread_latest_date( $elements, $time, $is_child = false )
	```

	Find the timestamp of the latest child comment of a thread to set the updated time.


	</details>
