## Class `AmpProject\AmpWP\AmpWpPlugin`

The AmpWpPlugin class is the composition root of the plugin.

In here we assemble our infrastructure, configure it for the specific use case the plugin is meant to solve and then kick off the services so that they can hook themselves into the WordPress lifecycle.

### Methods
* `get_service_classes`

	<details>

	```php
	protected get_service_classes()
	```

	Get the list of services to register.

The services array contains a map of &lt;identifier&gt; =&gt; &lt;service class name&gt; associations.


	</details>
* `get_bindings`

	<details>

	```php
	protected get_bindings()
	```

	Get the bindings for the dependency injector.

The bindings array contains a map of &lt;interface&gt; =&gt; &lt;implementation&gt; mappings, both of which should be fully qualified class names (FQCNs).
 The &lt;interface&gt; does not need to be the actual PHP `interface` language construct, it can be a `class` as well.
 Whenever you ask the injector to &quot;make()&quot; an &lt;interface&gt;, it will resolve these mappings and return an instance of the final &lt;class&gt; it found.


	</details>
* `get_arguments`

	<details>

	```php
	protected get_arguments()
	```

	Get the argument bindings for the dependency injector.

The arguments array contains a map of &lt;class&gt; =&gt; &lt;associative array of arguments&gt; mappings.
 The array is provided in the form &lt;argument name&gt; =&gt; &lt;argument value&gt;.


	</details>
* `get_shared_instances`

	<details>

	```php
	protected get_shared_instances()
	```

	Get the shared instances for the dependency injector.

The shared instances array contains a list of FQCNs that are meant to be reused. For multiple &quot;make()&quot; requests, the injector will return the same instance reference for these, instead of always returning a new one.
 This effectively turns these FQCNs into a &quot;singleton&quot;, without incurring all the drawbacks of the Singleton design anti-pattern.


	</details>
* `get_delegations`

	<details>

	```php
	protected get_delegations()
	```

	Get the delegations for the dependency injector.

The delegations array contains a map of &lt;class&gt; =&gt; &lt;callable&gt; mappings.
 The &lt;callable&gt; is basically a factory to provide custom instantiation logic for the given &lt;class&gt;.


	</details>
