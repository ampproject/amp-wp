## Class `AmpProject\AmpWP\PluginRegistry`

Suppress plugins from running by removing their hooks and nullifying their shortcodes, widgets, and blocks.

### Methods
<details>
<summary>`get_plugin_dir`</summary>

```php
public get_plugin_dir()
```

Get absolute path to plugin directory.


</details>
<details>
<summary>`get_plugin_slug_from_file`</summary>

```php
public get_plugin_slug_from_file( $plugin_file )
```

Get plugin slug from file.

If the plugin file is in a directory, then the slug is just the directory name. Otherwise, if the file is not inside of a directory and is just a single-file plugin, then the slug is the filename of the PHP file.


</details>
<details>
<summary>`get_plugins`</summary>

```php
public get_plugins( $active_only = false, $omit_core = true )
```

Get array of installed plugins, keyed by slug.


</details>
<details>
<summary>`get_plugin_from_slug`</summary>

```php
public get_plugin_from_slug( $plugin_slug )
```

Find a plugin from a slug.

A slug is a plugin directory name like &#039;amp&#039; or if the plugin is just a single file, then the PHP file in the plugins directory.


</details>
<details>
<summary>`get_plugins_data`</summary>

```php
private get_plugins_data()
```

Get the plugins data from WordPress.


</details>
