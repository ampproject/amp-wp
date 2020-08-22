## Class `AmpProject\AmpWP\Instrumentation\ServerTiming`

Collect Server-Timing metrics.

### Methods
* `get_registration_action`

<details>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
* `__construct`

<details>

```php
public __construct( \AmpProject\AmpWP\Instrumentation\StopWatch $stopwatch, $verbose = false )
```

ServerTiming constructor.


</details>
* `register`

<details>

```php
public register()
```

Register the service.


</details>
* `start`

<details>

```php
public start( $event_name, $event_description = null, $properties = array(), $verbose_only = false )
```

Start recording an event.


</details>
* `stop`

<details>

```php
public stop( $event_name )
```

Stop recording an event.


</details>
* `log`

<details>

```php
public log( $event_name, $event_description = '', $properties = array(), $verbose_only = false )
```

Log an event that does not have a duration.


</details>
* `send`

<details>

```php
public send()
```

Send the server-timing header.


</details>
* `get_header_string`

<details>

```php
public get_header_string()
```

Get the server timing header string for all collected events.


</details>
