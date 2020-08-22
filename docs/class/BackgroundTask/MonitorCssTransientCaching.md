## Class `AmpProject\AmpWP\BackgroundTask\MonitorCssTransientCaching`

Monitor the CSS transient caching to detect and remedy issues.

This checks whether there&#039;s excessive cycling of CSS cached stylesheets and disables transient caching if so.

### Methods
* `register`

	<details>

	```php
	public register()
	```

	Register the service with the system.


	</details>
* `get_interval`

	<details>

	```php
	protected get_interval()
	```

	Get the interval to use for the event.


	</details>
* `get_event_name`

	<details>

	```php
	protected get_event_name()
	```

	Get the event name.

This is the &quot;slug&quot; of the event, not the display name.
 Note: the event name should be prefixed to prevent naming collisions.


	</details>
* `process`

	<details>

	```php
	public process( DateTimeInterface $date = null, $transient_count = null )
	```

	Process a single cron tick.


	</details>
* `is_css_transient_caching_disabled`

	<details>

	```php
	private is_css_transient_caching_disabled()
	```

	Check whether transient caching of stylesheets is disabled.


	</details>
* `disable_css_transient_caching`

	<details>

	```php
	private disable_css_transient_caching()
	```

	Disable transient caching of stylesheets.


	</details>
* `query_css_transient_count`

	<details>

	```php
	public query_css_transient_count()
	```

	Query the number of transients containing cache stylesheets.


	</details>
* `handle_plugin_update`

	<details>

	```php
	public handle_plugin_update( $old_version )
	```

	Handle update to plugin.


	</details>
* `get_time_series`

	<details>

	```php
	public get_time_series()
	```

	Get the time series stored in the WordPress options table.


	</details>
* `get_default_threshold`

	<details>

	```php
	public get_default_threshold()
	```

	Get the default threshold to use.


	</details>
* `get_default_sampling_range`

	<details>

	```php
	public get_default_sampling_range()
	```

	Get the default sampling range to use.


	</details>
* `persist_time_series`

	<details>

	```php
	private persist_time_series( $time_series )
	```

	Persist the time series in the database.


	</details>
* `calculate_average`

	<details>

	```php
	private calculate_average( $time_series )
	```

	Calculate the average for the provided time series.

Note: The single highest value is discarded to calculate the average, so as to avoid a single outlier causing the threshold to be reached.


	</details>
* `get_threshold`

	<details>

	```php
	private get_threshold()
	```

	Get the threshold to check the moving average against.

This can be filtered via the &#039;amp_css_transient_monitoring_threshold&#039; filter.


	</details>
* `get_sampling_range`

	<details>

	```php
	private get_sampling_range()
	```

	Get the sampling range to limit the time series to for calculating the moving average.

This can be filtered via the &#039;amp_css_transient_monitoring_sampling_range&#039; filter.


	</details>
