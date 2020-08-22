## Class `AmpProject\AmpWP\Instrumentation\ServerTiming`

Collect Server-Timing metrics.

### Methods
<details>
<summary><code>get_registration_action</code></summary>

```php
static public get_registration_action()
```

Get the action to use for registering the service.


</details>
<details>
<summary><code>__construct</code></summary>

```php
public __construct( \AmpProject\AmpWP\Instrumentation\StopWatch $stopwatch, $verbose = false )
```

ServerTiming constructor.


</details>
<details>
<summary><code>register</code></summary>

```php
public register()
```

Register the service.


</details>
<details>
<summary><code>start</code></summary>

```php
public start( $event_name, $event_description = null, $properties = array(), $verbose_only = false )
```

Start recording an event.


</details>
<details>
<summary><code>stop</code></summary>

```php
public stop( $event_name )
```

Stop recording an event.


</details>
<details>
<summary><code>log</code></summary>

```php
public log( $event_name, $event_description = '', $properties = array(), $verbose_only = false )
```

Log an event that does not have a duration.


</details>
<details>
<summary><code>send</code></summary>

```php
public send()
```

Send the server-timing header.


</details>
<details>
<summary><code>get_header_string</code></summary>

```php
public get_header_string()
```

Get the server timing header string for all collected events.


</details>
