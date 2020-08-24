## Class `AmpProject\AmpWP\Admin\AnalyticsOptionsSubmenu`

Class AnalyticsOptionsSubmenu

### Methods

* [`__construct`](../method/Admin/AnalyticsOptionsSubmenu/__construct.md) - Class constructor.
* [`get_registration_action`](../method/Admin/AnalyticsOptionsSubmenu/get_registration_action.md) - Get the action to use for registering the service.
* [`register`](../method/Admin/AnalyticsOptionsSubmenu/register.md) - Adds hooks.
* [`add_submenu_link`](../method/Admin/AnalyticsOptionsSubmenu/add_submenu_link.md) - Adds a submenu link to the AMP options submenu.
### Source

:link: [src/Admin/AnalyticsOptionsSubmenu.php:16](https://github.com/ampproject/amp-wp/blob/develop/src/Admin/AnalyticsOptionsSubmenu.php#L16-L64)

<details>
<summary>Show Code</summary>

```php
final class AnalyticsOptionsSubmenu implements Service, Registerable {

	/**
	 * The parent menu slug.
	 *
	 * @var string
	 */
	private $parent_menu_slug;

	/**
	 * Class constructor.
	 *
	 * @param OptionsMenu $options_menu An instance of the class handling the parent menu.
	 */
	public function __construct( OptionsMenu $options_menu ) {
		$this->parent_menu_slug = $options_menu->get_menu_slug();
	}

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {
		return 'admin_init';
	}

	/**
	 * Adds hooks.
	 */
	public function register() {
		add_action( 'admin_menu', [ $this, 'add_submenu_link' ], 99 );
	}

	/**
	 * Adds a submenu link to the AMP options submenu.
	 */
	public function add_submenu_link() {
		add_submenu_page(
			$this->parent_menu_slug,
			__( 'Analytics', 'amp' ),
			__( 'Analytics', 'amp' ),
			'manage_options',
			$this->parent_menu_slug . '#analytics-options',
			'__return_false',
			1
		);
	}
}
```

</details>
