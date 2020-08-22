## Class `AMP_Core_Block_Handler`

Class AMP_Core_Block_Handler

### Methods
* `register_embed`

	<details>

	```php
	public register_embed()
	```

	Register embed.


	</details>
* `unregister_embed`

	<details>

	```php
	public unregister_embed()
	```

	Unregister embed.


	</details>
* `filter_rendered_block`

	<details>

	```php
	public filter_rendered_block( $block_content, $block )
	```

	Filters the content of a single block to make it AMP valid.


	</details>
* `ampify_categories_block`

	<details>

	```php
	public ampify_categories_block( $block_content )
	```

	Fix rendering of categories block when displayAsDropdown.

This excludes the disallowed JS scrips, adds &lt;form&gt; tags, and uses on:change for &lt;select&gt;.


	</details>
* `ampify_archives_block`

	<details>

	```php
	public ampify_archives_block( $block_content )
	```

	Fix rendering of archives block when displayAsDropdown.

This replaces disallowed script with the use of on:change for &lt;select&gt;.


	</details>
* `ampify_video_block`

	<details>

	```php
	public ampify_video_block( $block_content, $block )
	```

	Ampify video block.

Inject the video attachment&#039;s dimensions if available. This prevents having to try to look up the attachment post by the video URL in `\AMP_Video_Sanitizer::filter_video_dimensions()`.


	</details>
* `ampify_cover_block`

	<details>

	```php
	public ampify_cover_block( $block_content, $block )
	```

	Ampify cover block.

This specifically fixes the layout of the block when a background video is assigned.


	</details>
* `sanitize_raw_embeds`

	<details>

	```php
	public sanitize_raw_embeds( Document $dom, $args = array() )
	```

	Sanitize widgets that are not added via Gutenberg.


	</details>
* `process_categories_widgets`

	<details>

	```php
	private process_categories_widgets( Document $dom )
	```

	Process &quot;Categories&quot; widgets.


	</details>
* `process_archives_widgets`

	<details>

	```php
	private process_archives_widgets( Document $dom, $args = array() )
	```

	Process &quot;Archives&quot; widgets.


	</details>
* `preserve_widget_text_element_dimensions`

	<details>

	```php
	public preserve_widget_text_element_dimensions( $content )
	```

	Preserve dimensions of elements in a Text widget to later restore to circumvent WordPress core stripping them out.

Core strips out the dimensions to prevent the element being made too wide for the sidebar. This is not a concern in AMP because of responsive sizing. So this logic is here to undo what core is doing.


	</details>
* `process_text_widgets`

	<details>

	```php
	private process_text_widgets( Document $dom )
	```

	Process &quot;Text&quot; widgets.


	</details>
