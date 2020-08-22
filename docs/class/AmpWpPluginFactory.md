## Class `AmpProject\AmpWP\AmpWpPluginFactory`

The plugin factory is responsible for instantiating the plugin and returning that instance.

It can decide whether to return a shared or a fresh instance as needed.
 To read more about why this is preferable to a Singleton,

### Methods
<details>
<summary>`create`</summary>

```php
static public create()
```

Create and return an instance of the plugin.

This always returns a shared instance. This way, outside code can always get access to the object instance of the plugin.


</details>
