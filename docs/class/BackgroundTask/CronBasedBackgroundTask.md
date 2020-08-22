## Class `AmpProject\AmpWP\BackgroundTask\CronBasedBackgroundTask`

Abstract base class for using cron to execute a background task.

### Methods
<details>
<summary>`is_needed`</summary>

```php
static public is_needed()
```

Check whether the conditional object is currently needed.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Register the service with the system.


</details>
<details>
<summary>`get_warning_icon`</summary>

```php
private get_warning_icon()
```

Get warning icon markup.


</details>
<details>
<summary>`schedule_event`</summary>

```php
public schedule_event()
```

Schedule the event.

This does nothing if the event is already scheduled.


</details>
<details>
<summary>`deactivate`</summary>

```php
public deactivate( $network_wide )
```

Run deactivation logic.

This should be hooked up to the WordPress deactivation hook.


</details>
<details>
<summary>`add_warning_sign_to_network_deactivate_action`</summary>

```php
public add_warning_sign_to_network_deactivate_action( $actions )
```

Add a warning sign to the network deactivate action on the network plugins screen.


</details>
<details>
<summary>`add_warning_to_plugin_meta`</summary>

```php
public add_warning_to_plugin_meta( $plugin_meta, $plugin_file )
```

Add a warning to the plugin meta row on the network plugins screen.


</details>
<details>
<summary>`get_interval`</summary>

```php
abstract protected get_interval()
```

Get the interval to use for the event.


</details>
<details>
<summary>`get_event_name`</summary>

```php
abstract protected get_event_name()
```

Get the event name.

This is the &quot;slug&quot; of the event, not the display name.
 Note: the event name should be prefixed to prevent naming collisions.


</details>
<details>
<summary>`process`</summary>

```php
abstract public process()
```

Process a single cron tick.


</details>
