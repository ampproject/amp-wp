## Class `AMP_Comment_Walker`

Class AMP_Comment_Walker

Walker to wrap comments in mustache tags for amp-template.

### Methods
<details>
<summary>`start_el`</summary>

```php
public start_el( $output, $comment, $depth, $args = array(), $id )
```

Starts the element output.


</details>
<details>
<summary>`paged_walk`</summary>

```php
public paged_walk( $elements, $max_depth, $page_num, $per_page, $args )
```

Output amp-list template code and place holder for comments.


</details>
<details>
<summary>`build_thread_latest_date`</summary>

```php
protected build_thread_latest_date( $elements, $time, $is_child = false )
```

Find the timestamp of the latest child comment of a thread to set the updated time.


</details>
