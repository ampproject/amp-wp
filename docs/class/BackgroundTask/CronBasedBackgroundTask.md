## Class `AmpProject\AmpWP\BackgroundTask\CronBasedBackgroundTask`

Abstract base class for using cron to execute a background task.

### Methods
* `is_needed`

<details>

```php
static public is_needed()
```

Check whether the conditional object is currently needed.


</details>
* `register`

<details>

```php
public register()
```

Register the service with the system.


</details>
* `get_warning_icon`

<details>

```php
private get_warning_icon()
```

Get warning icon markup.


</details>
* `schedule_event`

<details>

```php
public schedule_event()
```

Schedule the event.

This does nothing if the event is already scheduled.


</details>
* `deactivate`

<details>

```php
public deactivate( $network_wide )
```

Run deactivation logic.

This should be hooked up to the WordPress deactivation hook.


</details>
* `add_warning_sign_to_network_deactivate_action`

<details>

```php
public add_warning_sign_to_network_deactivate_action( $actions )
```

Add a warning sign to the network deactivate action on the network plugins screen.


</details>
* `add_warning_to_plugin_meta`

<details>

```php
public add_warning_to_plugin_meta( $plugin_meta, $plugin_file )
```

Add a warning to the plugin meta row on the network plugins screen.


</details>
* `get_interval`

<details>

```php
abstract protected get_interval()
```

Get the interval to use for the event.


</details>
* `get_event_name`

<details>

```php
abstract protected get_event_name()
```

Get the event name.

This is the &quot;slug&quot; of the event, not the display name.
 Note: the event name should be prefixed to prevent naming collisions.


</details>
* `process`

<details>

```php
abstract public process()
```

Process a single cron tick.


</details>
