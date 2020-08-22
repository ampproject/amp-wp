## Class `AMP_Validation_Error_Taxonomy`

Class AMP_Validation_Error_Taxonomy

### Methods
* `register`

	<details>

	```php
	static public register()
	```

	Registers the taxonomy to store the validation errors.


	</details>
* `get_term`

	<details>

	```php
	static public get_term( $error )
	```

	Get amp_validation_error taxonomy term by slug or error properties.


	</details>
* `delete_empty_terms`

	<details>

	```php
	static public delete_empty_terms()
	```

	Delete all amp_validation_error terms that have zero counts (no amp_validated_url posts associated with them).


	</details>
* `delete_empty_term`

	<details>

	```php
	static public delete_empty_term( $term_id )
	```

	Delete an amp_validation_error term if it has no amp_validated_url posts associated with it.


	</details>
* `sanitize_term_status`

	<details>

	```php
	static public sanitize_term_status( $status, $options = array() )
	```

	Sanitize term status(es).


	</details>
* `prepare_term_group_in_sql`

	<details>

	```php
	static public prepare_term_group_in_sql( $groups )
	```

	Prepare term_group IN condition for SQL WHERE clause.


	</details>
* `prepare_validation_error_taxonomy_term`

	<details>

	```php
	static public prepare_validation_error_taxonomy_term( $error )
	```

	Prepare a validation error for lookup or insertion as taxonomy term.


	</details>
* `is_validation_error_sanitized`

	<details>

	```php
	static public is_validation_error_sanitized( $error )
	```

	Determine whether a validation error should be sanitized.


	</details>
* `get_validation_error_sanitization`

	<details>

	```php
	static public get_validation_error_sanitization( $error )
	```

	Get the validation error sanitization.


	</details>
* `accept_validation_errors`

	<details>

	```php
	static public accept_validation_errors( $acceptable_errors )
	```

	Automatically (forcibly) accept validation errors that arise (that is, remove the invalid markup causing the validation errors).


	</details>
* `is_array_subset`

	<details>

	```php
	static public is_array_subset( $superset, $subset )
	```

	Check if one array is a sparse subset of another array.


	</details>
* `get_validation_error_count`

	<details>

	```php
	static public get_validation_error_count( $args = array() )
	```

	Get the count of validation error terms, optionally restricted by term group (e.g. accepted or rejected).


	</details>
* `filter_posts_where_for_validation_error_status`

	<details>

	```php
	static public filter_posts_where_for_validation_error_status( $where, \WP_Query $query )
	```

	Add support for querying posts by amp_validation_error_status and by error type.

Add recognition of amp_validation_error_status query var for amp_validated_url post queries. Also, conditionally filter for error type, like js_error or css_error.


	</details>
* `summarize_validation_errors`

	<details>

	```php
	static public summarize_validation_errors( $validation_errors )
	```

	Gets the AMP validation response.

Returns the current validation errors the sanitizers found in rendering the page.


	</details>
* `summarize_sources`

	<details>

	```php
	static public summarize_sources( $sources )
	```

	Summarize sources.


	</details>
* `add_admin_hooks`

	<details>

	```php
	static public add_admin_hooks()
	```

	Add admin hooks.


	</details>
* `add_term_filter_query_var`

	<details>

	```php
	static public add_term_filter_query_var( $url, $tax )
	```

	Filter the term redirect URL, to possibly add query vars to filter by term status or type.

On clicking the &#039;Filter&#039; button on the &#039;AMP Validation Errors&#039; taxonomy page, edit-tags.php processes the POST request that this submits. Then, it redirects to a URL to display the page again. This filter callback looks for a value for VALIDATION_ERROR_TYPE_QUERY_VAR in the $_POST request. That means that the user filtered by error type, like &#039;js_error&#039;. It then passes that value to the redirect URL as a query var, So that the taxonomy page will be filtered for that error type.


	</details>
* `add_group_terms_clauses_filter`

	<details>

	```php
	static public add_group_terms_clauses_filter()
	```

	Filter amp_validation_error term query by term group when requested.


	</details>
* `add_error_type_clauses_filter`

	<details>

	```php
	static public add_error_type_clauses_filter()
	```

	Adds filter for amp_validation_error term query by type, like in the &#039;AMP Validation Errors&#039; taxonomy page.

Filters &#039;load-edit-tags.php&#039; and &#039;load-post.php&#039;, as the post.php page is like an edit-tags.php page, in that it has a WP_Terms_List_Table of validation error terms. Allows viewing only a certain type at a time, like only JS errors.


	</details>
* `add_order_clauses_from_description_json`

	<details>

	```php
	static public add_order_clauses_from_description_json()
	```

	If ordering the list by a field in the description JSON, locate the best spot in the JSON string by which to sort alphabetically.

This is used both on the taxonomy edit-tags.php page and the single URL post.php page, as that page also has a list table of terms.


	</details>
* `render_taxonomy_filters`

	<details>

	```php
	static public render_taxonomy_filters( $taxonomy_name )
	```

	Outputs the taxonomy filter UI for this taxonomy type.

Similar to what appears on /wp-admin/edit.php for posts and pages, this outputs &lt;select&gt; elements to choose the error status and type, and a &#039;Filter&#039; submit button that filters for them.


	</details>
* `render_link_to_invalid_urls_screen`

	<details>

	```php
	static public render_link_to_invalid_urls_screen( $taxonomy_name )
	```

	On the &#039;Error Index&#039; screen, renders a link to the &#039;AMP Validated URLs&#039; page.


	</details>
* `render_error_status_filter`

	<details>

	```php
	static public render_error_status_filter()
	```

	Renders the error status filter &lt;select&gt; element.

There is a difference how the errors are counted, depending on which screen this is on. For example: Removed Markup (10). This status filter &lt;select&gt; element is rendered on the validation error post page (Errors by URL), and the validation error taxonomy page (Error Index). On the taxonomy page, this simply needs to count the number of terms with a given type. On the post page, this needs to count the number of posts that have at least one error of a given type.


	</details>
* `output_error_status_filter_option_markup`

	<details>

	```php
	static private output_error_status_filter_option_markup( $option_text, $option_value, $error_count, $selected_value )
	```

	Output the option markup for a error status filter.


	</details>
* `get_error_types`

	<details>

	```php
	static public get_error_types()
	```

	Gets all of the possible error types.


	</details>
* `render_error_type_filter`

	<details>

	```php
	static public render_error_type_filter()
	```

	Renders the filter for error type.

This type filter &lt;select&gt; element is rendered on the validation error post page (Errors by URL), and the validation error taxonomy page (Error Index).


	</details>
* `render_clear_empty_button`

	<details>

	```php
	static public render_clear_empty_button()
	```

	Render the button for clearing empty taxonomy terms.

If there are no terms with a 0 count then this outputs nothing.


	</details>
* `filter_terms_clauses_for_description_search`

	<details>

	```php
	static public filter_terms_clauses_for_description_search( $clauses, $taxonomies, $args )
	```

	Include searching taxonomy term descriptions and sources term meta.


	</details>
* `add_admin_notices`

	<details>

	```php
	static public add_admin_notices()
	```

	Show notices for changes to amp_validation_error terms.


	</details>
* `filter_tag_row_actions`

	<details>

	```php
	static public filter_tag_row_actions( $actions, \WP_Term $tag )
	```

	Add row actions.


	</details>
* `add_admin_menu_validation_error_item`

	<details>

	```php
	static public add_admin_menu_validation_error_item()
	```

	Show AMP validation errors under AMP admin menu.


	</details>
* `get_reader_friendly_error_type_text`

	<details>

	```php
	static public get_reader_friendly_error_type_text( $error_type )
	```

	Provides a reader-friendly string for a term&#039;s error type.


	</details>
* `get_details_summary_label`

	<details>

	```php
	static public get_details_summary_label( $validation_error )
	```

	Provides the label for the details summary element.


	</details>
* `filter_manage_custom_columns`

	<details>

	```php
	static public filter_manage_custom_columns( $content, $column_name, $term_id )
	```

	Supply the content for the custom columns.


	</details>
* `add_single_post_sortable_columns`

	<details>

	```php
	static public add_single_post_sortable_columns( $sortable_columns )
	```

	Adds post columns to the /wp-admin/post.php page for amp_validated_url.


	</details>
* `render_single_url_error_details`

	<details>

	```php
	static public render_single_url_error_details( $validation_error, $term, $wrap_with_details = true, $with_summary = true )
	```

	Renders error details when viewing a single URL page.


	</details>
* `get_file_editor_url`

	<details>

	```php
	static private get_file_editor_url( $source )
	```

	Get the URL for opening the file for a AMP validation error in an external editor.


	</details>
* `render_source_name`

	<details>

	```php
	static private render_source_name( $name, $type )
	```

	Render source name.


	</details>
* `render_sources`

	<details>

	```php
	static public render_sources( $sources )
	```

	Render sources.


	</details>
* `render_code_details`

	<details>

	```php
	static private render_code_details( $text )
	```

	Render code details.


	</details>
* `get_block_title`

	<details>

	```php
	static public get_block_title( $block_name )
	```

	Get block name for a given block slug.


	</details>
* `get_translated_type_name`

	<details>

	```php
	static public get_translated_type_name( $validation_error )
	```

	Gets the translated error type name from the given the validation error.


	</details>
* `handle_inline_edit_request`

	<details>

	```php
	static public handle_inline_edit_request()
	```

	Handle inline edit links.


	</details>
* `handle_single_url_page_bulk_and_inline_actions`

	<details>

	```php
	static public handle_single_url_page_bulk_and_inline_actions( $post_id )
	```

	On the single URL page, handles the bulk actions of &#039;Remove&#039; (formerly &#039;Accept&#039;) and &#039;Keep&#039; (formerly &#039;Reject&#039;).

On /wp-admin/post.php, this handles these bulk actions. This page is more like an edit-tags.php page, in that it has a WP_Terms_List_Table of amp_validation_error terms. So this reuses handle_validation_error_update(), which the edit-tags.php page uses.


	</details>
* `handle_validation_error_update`

	<details>

	```php
	static public handle_validation_error_update( $redirect_to, $action, $term_ids )
	```

	Handle bulk and inline edits to amp_validation_error terms.


	</details>
* `handle_clear_empty_terms_request`

	<details>

	```php
	static public handle_clear_empty_terms_request()
	```

	Handle request to delete empty terms.


	</details>
* `is_validation_error_for_js_script_element`

	<details>

	```php
	static private is_validation_error_for_js_script_element( $validation_error )
	```

	Determine whether a validation error is for a JS script element.


	</details>
* `get_error_title_from_code`

	<details>

	```php
	static public get_error_title_from_code( $validation_error )
	```

	Get Error Title from Code


	</details>
* `get_source_key_label`

	<details>

	```php
	static public get_source_key_label( $key, $validation_error )
	```

	Get label for object key in validation error source.


	</details>
* `get_status_text_with_icon`

	<details>

	```php
	static public get_status_text_with_icon( $sanitization, $include_reviewed = false )
	```

	Get Status Text with Icon


	</details>
