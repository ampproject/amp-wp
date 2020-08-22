## Class `AmpProject\AmpWP\BackgroundTask\ValidatedUrlStylesheetDataGarbageCollection`

Delete stylesheet data from amp_validated_url posts which have not been validated in a week.

This background task will update the oldest 100 amp_validated_url posts each time it runs, excluding URLs that have been validated within the past week. The batch size of 100 follows the lead of `_wp_batch_update_comment_type()` in WordPress 5.5. Deleting data from posts older than 1 week follows the lead of `wp_delete_auto_drafts()`.

### Methods
<details>
<summary><code>get_interval</code></summary>

```php
protected get_interval()
```

Get the interval to use for the event.


</details>
<details>
<summary><code>get_event_name</code></summary>

```php
protected get_event_name()
```

Get the event name.

This is the &quot;slug&quot; of the event, not the display name.
 Note: the event name should be prefixed to prevent naming collisions.


</details>
<details>
<summary><code>process</code></summary>

```php
public process()
```

Process a single cron tick.


</details>
