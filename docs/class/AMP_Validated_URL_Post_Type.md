## Class `AMP_Validated_URL_Post_Type`

Class AMP_Validated_URL_Post_Type

### Methods
* `register`

<details>

```php
static public register()
```

Registers the post type to store URLs with validation errors.


</details>
* `handle_plugin_update`

<details>

```php
static public handle_plugin_update( $old_version )
```

Handle update to plugin.


</details>
* `add_admin_hooks`

<details>

```php
static public add_admin_hooks()
```

Add admin hooks.


</details>
* `enqueue_post_list_screen_scripts`

<details>

```php
static public enqueue_post_list_screen_scripts()
```

Enqueue style.


</details>
* `render_link_to_error_index_screen`

<details>

```php
static public render_link_to_error_index_screen()
```

On the &#039;AMP Validated URLs&#039; screen, renders a link to the &#039;Error Index&#039; page.


</details>
* `add_admin_menu_new_invalid_url_count`

<details>

```php
static public add_admin_menu_new_invalid_url_count()
```

Add count of how many validation error posts there are to the admin menu.


</details>
* `get_validation_error_urls_count`

<details>

```php
static protected get_validation_error_urls_count()
```

Get the count of URLs that have new validation errors.


</details>
* `get_invalid_url_validation_errors`

<details>

```php
static public get_invalid_url_validation_errors( $url, $args = array() )
```

Gets validation errors for a given validated URL post.


</details>
* `display_invalid_url_validation_error_counts_summary`

<details>

```php
static public display_invalid_url_validation_error_counts_summary( $post )
```

Display summary of the validation error counts for a given post.


</details>
* `get_invalid_url_post`

<details>

```php
static public get_invalid_url_post( $url, $options = array() )
```

Gets the existing custom post that stores errors for the $url, if it exists.


</details>
* `get_url_from_post`

<details>

```php
static public get_url_from_post( $post )
```

Get the URL from a given amp_validated_url post.

The URL will be returned with the amp query var added to it if the site is not canonical. The post_title is always stored using the canonical AMP-less URL.


</details>
* `get_markup_status_preview_url`

<details>

```php
static protected get_markup_status_preview_url( $url )
```

Get the markup status preview URL.

Adds a _wpnonce query param for the markup status preview action.


</details>
* `normalize_url_for_storage`

<details>

```php
static protected normalize_url_for_storage( $url )
```

Normalize a URL for storage.

The AMP query param is removed to facilitate switching between standard and transitional. The URL scheme is also normalized to HTTPS to help with transition from HTTP to HTTPS.


</details>
* `store_validation_errors`

<details>

```php
static public store_validation_errors( $validation_errors, $url, $args = array() )
```

Stores the validation errors.

If there are no validation errors provided, then any existing amp_validated_url post is deleted.


</details>
* `delete_stylesheets_postmeta_batch`

<details>

```php
static public delete_stylesheets_postmeta_batch( $count, $before )
```

Delete batch of stylesheets postmeta.

Given that parsed CSS can be quite large (250KB+) and is not de-duplicated across each validated URL, it is important to not store the stylesheet data indefinitely in order to not excessively bloat the database. The reality is that keeping around the parsed stylesheet data is of little value given that it will quickly go stale as themes and plugins are updated.


</details>
* `get_recent_validation_errors_by_source`

<details>

```php
static public get_recent_validation_errors_by_source( $count = 100 )
```

Get recent validation errors by source.


</details>
* `get_validated_environment`

<details>

```php
static public get_validated_environment()
```

Get the environment properties which will likely effect whether validation results are stale.


</details>
* `get_post_staleness`

<details>

```php
static public get_post_staleness( $post )
```

Get the differences between the current themes, plugins, and relevant options when amp_validated_url post was last updated and now.


</details>
* `add_post_columns`

<details>

```php
static public add_post_columns( $columns )
```

Adds post columns to the UI for the validation errors.


</details>
* `add_single_post_columns`

<details>

```php
static public add_single_post_columns()
```

Adds post columns to the /wp-admin/post.php page for amp_validated_url.


</details>
* `output_custom_column`

<details>

```php
static public output_custom_column( $column_name, $post_id )
```

Outputs custom columns in the /wp-admin UI for the AMP validation errors.


</details>
* `render_sources_column`

<details>

```php
static public render_sources_column( $sources, $post_id )
```

Renders the sources column on the the single error URL page and the &#039;AMP Validated URLs&#039; page.


</details>
* `filter_bulk_actions`

<details>

```php
static public filter_bulk_actions( $actions )
```

Adds a &#039;Recheck&#039; bulk action to the edit.php page and modifies the &#039;Move to Trash&#039; text.

Ensure only delete action is present, not trash.


</details>
* `handle_bulk_action`

<details>

```php
static public handle_bulk_action( $redirect, $action, $items )
```

Handles the &#039;Recheck&#039; bulk action on the edit.php page.


</details>
* `print_admin_notice`

<details>

```php
static public print_admin_notice()
```

Outputs an admin notice after rechecking URL(s) on the custom post page.


</details>
* `render_php_fatal_error_admin_notice`

<details>

```php
static private render_php_fatal_error_admin_notice( \WP_Post $post )
```

Render PHP fatal error admin notice.


</details>
* `handle_validate_request`

<details>

```php
static public handle_validate_request()
```

Handles clicking &#039;recheck&#039; on the inline post actions and in the admin bar on the frontend.


</details>
* `recheck_post`

<details>

```php
static public recheck_post( $post )
```

Re-check validated URL post for whether it has blocking validation errors.


</details>
* `handle_validation_error_status_update`

<details>

```php
static public handle_validation_error_status_update()
```

Handle validation error status update.


</details>
* `enqueue_edit_post_screen_scripts`

<details>

```php
static public enqueue_edit_post_screen_scripts()
```

Enqueue scripts for the edit post screen.


</details>
* `add_meta_boxes`

<details>

```php
static public add_meta_boxes()
```

Adds the meta boxes to the CPT post.php page.


</details>
* `print_status_meta_box`

<details>

```php
static public print_status_meta_box( $post )
```

Outputs the markup of the side meta box in the CPT post.php page.

This is partially copied from meta-boxes.php. Adds &#039;Published on,&#039; and links to move to trash and recheck.


</details>
* `print_stylesheets_meta_box`

<details>

```php
static public print_stylesheets_meta_box( $post )
```

Renders stylesheet info for the validated URL.


</details>
* `render_single_url_list_table`

<details>

```php
static public render_single_url_list_table( $post )
```

Renders the single URL list table.

Mainly copied from edit-tags.php. This is output on the post.php page for amp_validated_url, where the editor normally would be. But it&#039;s really more similar to /wp-admin/edit-tags.php than a post.php page, as this outputs a WP_Terms_List_Table of amp_validation_error terms.


</details>
* `get_terms_per_page`

<details>

```php
static public get_terms_per_page( $terms_per_page )
```

Gets the number of amp_validation_error terms that should appear on the single amp_validated_url /wp-admin/post.php page.


</details>
* `add_taxonomy`

<details>

```php
static public add_taxonomy()
```

Adds the taxonomy to the $_REQUEST, so that it is available in WP_Screen and WP_Terms_List_Table.

It would be ideal to do this in render_single_url_list_table(), but set_current_screen() looks to run before that, and that needs access to the &#039;taxonomy&#039;.


</details>
* `print_url_as_title`

<details>

```php
static public print_url_as_title( $post )
```

Show URL at the top of the edit form in place of the title (since title support is not present).


</details>
* `filter_the_title_in_post_list_table`

<details>

```php
static public filter_the_title_in_post_list_table( $title, $id = null )
```

Strip host name from AMP validated URL being printed.


</details>
* `render_post_filters`

<details>

```php
static public render_post_filters( $post_type, $which )
```

Renders the filters on the validated URL post type edit.php page.


</details>
* `get_recheck_url`

<details>

```php
static public get_recheck_url( $url_or_post )
```

Gets the URL to recheck the post for AMP validity.

Appends a query var to $redirect_url. On clicking the link, it checks if errors still exist for $post.


</details>
* `filter_dashboard_glance_items`

<details>

```php
static public filter_dashboard_glance_items( $items )
```

Filter At a Glance items add AMP Validation Errors.


</details>
* `print_dashboard_glance_styles`

<details>

```php
static public print_dashboard_glance_styles()
```

Print styles for the At a Glance widget.


</details>
* `filter_admin_title`

<details>

```php
static public filter_admin_title( $admin_title )
```

Filters the document title on the single URL page at /wp-admin/post.php.


</details>
* `is_validated_url_admin_screen`

<details>

```php
static private is_validated_url_admin_screen()
```

Determines whether the current screen is for a validated URL.


</details>
* `get_validated_url_title`

<details>

```php
static public get_validated_url_title( $post = null )
```

Gets the title for validated URL, corresponding with the title for the queried object.


</details>
* `filter_post_row_actions`

<details>

```php
static public filter_post_row_actions( $actions, $post )
```

Filters post row actions.

Manages links for details, recheck, view, forget, and forget permanently.


</details>
* `filter_table_views`

<details>

```php
static public filter_table_views( $views )
```

Filters table views for the post type.


</details>
* `filter_bulk_post_updated_messages`

<details>

```php
static public filter_bulk_post_updated_messages( $messages, $bulk_counts )
```

Filters messages displayed after bulk updates.

Note that trashing is replaced with deletion whenever possible, so the trashed and untrashed messages will not be used in practice.


</details>
* `is_amp_enabled_on_post`

<details>

```php
static public is_amp_enabled_on_post( $post )
```

Is AMP Enabled on Post


</details>
* `count_invalid_url_validation_errors`

<details>

```php
static protected count_invalid_url_validation_errors( $validation_errors )
```

Count URL Validation Errors


</details>
