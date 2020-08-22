## Class `AmpProject\AmpWP\Instrumentation\ServerTiming`

Collect Server-Timing metrics.

### Methods
<details>
<summary>`get_registration_action`</summary>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
<details>
<summary>`__construct`</summary>

```php
public __construct( \AmpProject\AmpWP\Instrumentation\StopWatch $stopwatch, $verbose = false )
```

ServerTiming constructor.


</details>
<details>
<summary>`register`</summary>

```php
public register()
```

Register the service.


</details>
<details>
<summary>`start`</summary>

```php
public start( $event_name, $event_description = null, $properties = array(), $verbose_only = false )
```

Start recording an event.


</details>
<details>
<summary>`stop`</summary>

```php
public stop( $event_name )
```

Stop recording an event.


</details>
<details>
<summary>`log`</summary>

```php
public log( $event_name, $event_description = '', $properties = array(), $verbose_only = false )
```

Log an event that does not have a duration.


</details>
<details>
<summary>`send`</summary>

```php
public send()
```

Send the server-timing header.


</details>
<details>
<summary>`get_header_string`</summary>

```php
public get_header_string()
```

Get the server timing header string for all collected events.


</details>
