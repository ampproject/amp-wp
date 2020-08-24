## Class `AMP_Content`

> :warning: This function is deprecated: 1.5

Class AMP_Content

### Methods

* [`__construct`](../method/AMP_Content/__construct.md) - AMP_Content constructor.
* [`get_amp_content`](../method/AMP_Content/get_amp_content.md) - Get AMP content.
* [`get_amp_scripts`](../method/AMP_Content/get_amp_scripts.md) - Get AMP scripts.
* ~~[`get_amp_styles`](../method/AMP_Content/get_amp_styles.md) - Get AMP styles.~~
* [`get_amp_stylesheets`](../method/AMP_Content/get_amp_stylesheets.md) - Get AMP styles.
* [`transform`](../method/AMP_Content/transform.md) - Transform.
* [`add_scripts`](../method/AMP_Content/add_scripts.md) - Add scripts.
* [`add_stylesheets`](../method/AMP_Content/add_stylesheets.md) - Add stylesheets.
* [`register_embed_handlers`](../method/AMP_Content/register_embed_handlers.md) - Register embed handlers.
* [`unregister_embed_handlers`](../method/AMP_Content/unregister_embed_handlers.md) - Unregister embed handlers.
* [`sanitize`](../method/AMP_Content/sanitize.md) - Sanitize.
### Source

:link: [includes/templates/class-amp-content.php:14](../../includes/templates/class-amp-content.php#L14-L224)

<details>
<summary>Show Code</summary>

```php
class AMP_Content {

	/**
	 * Content.
	 *
	 * @var string
	 */
	private $content;

	/**
	 * AMP content.
	 *
	 * @var string
	 */
	private $amp_content = '';

	/**
	 * AMP scripts.
	 *
	 * @var array
	 */
	private $amp_scripts = [];

	/**
	 * AMP stylesheets.
	 *
	 * @since 1.0
	 * @var array
	 */
	private $amp_stylesheets = [];

	/**
	 * Args.
	 *
	 * @var array
	 */
	private $args;

	/**
	 * Embed handlers.
	 *
	 * @var AMP_Base_Embed_Handler[]
	 */
	private $embed_handlers;

	/**
	 * Sanitizers, with keys as class names and values as arguments.
	 *
	 * @var array[]
	 */
	private $sanitizer_classes;

	/**
	 * AMP_Content constructor.
	 *
	 * @param string  $content               Content.
	 * @param array[] $embed_handler_classes Embed handlers, with keys as class names and values as arguments.
	 * @param array[] $sanitizer_classes     Sanitizers, with keys as class names and values as arguments.
	 * @param array   $args                  Args.
	 */
	public function __construct( $content, $embed_handler_classes, $sanitizer_classes, $args = [] ) {
		$this->content           = $content;
		$this->args              = $args;
		$this->embed_handlers    = $this->register_embed_handlers( $embed_handler_classes );
		$this->sanitizer_classes = $sanitizer_classes;

		$this->sanitizer_classes['AMP_Embed_Sanitizer']['embed_handlers'] = $this->embed_handlers;

		$this->transform();
	}

	/**
	 * Get AMP content.
	 *
	 * @return string
	 */
	public function get_amp_content() {
		return $this->amp_content;
	}

	/**
	 * Get AMP scripts.
	 *
	 * @return array
	 */
	public function get_amp_scripts() {
		return $this->amp_scripts;
	}

	/**
	 * Get AMP styles.
	 *
	 * @deprecated Since 1.0 in favor of the get_amp_stylesheets method.
	 * @return array Empty list.
	 */
	public function get_amp_styles() {
		_deprecated_function( __METHOD__, '1.0', __CLASS__ . '::get_amp_stylesheets' );
		return [];
	}

	/**
	 * Get AMP styles.
	 *
	 * @since 1.0
	 * @return array
	 */
	public function get_amp_stylesheets() {
		return $this->amp_stylesheets;
	}

	/**
	 * Transform.
	 */
	private function transform() {
		$content = $this->content;

		// First, embeds + the_content filter.
		/** This filter is documented in wp-includes/post-template.php */
		$content = apply_filters( 'the_content', $content );
		$this->unregister_embed_handlers( $this->embed_handlers );

		// Then, sanitize to strip and/or convert non-amp content.
		$content = $this->sanitize( $content );

		$this->amp_content = $content;
	}

	/**
	 * Add scripts.
	 *
	 * @param array $scripts Scripts.
	 */
	private function add_scripts( $scripts ) {
		$this->amp_scripts = array_merge( $this->amp_scripts, $scripts );
	}

	/**
	 * Add stylesheets.
	 *
	 * @since 1.0
	 * @param array $stylesheets Styles.
	 */
	private function add_stylesheets( $stylesheets ) {
		$this->amp_stylesheets = array_merge( $this->amp_stylesheets, $stylesheets );
	}

	/**
	 * Register embed handlers.
	 *
	 * @param array[] $embed_handler_classes Embed handlers, with keys as class names and values as arguments.
	 * @return AMP_Base_Embed_Handler[] Registered embed handlers.
	 */
	private function register_embed_handlers( $embed_handler_classes ) {
		$embed_handlers = [];

		foreach ( $embed_handler_classes as $embed_handler_class => $args ) {
			$embed_handler = new $embed_handler_class( array_merge( $this->args, $args ) );

			if ( ! $embed_handler instanceof AMP_Base_Embed_Handler ) {
				_doing_it_wrong(
					__METHOD__,
					esc_html(
						sprintf(
							/* translators: 1: embed handler. 2: AMP_Embed_Handler */
							__( 'Embed Handler (%1$s) must extend `%2$s`', 'amp' ),
							esc_html( $embed_handler_class ),
							'AMP_Embed_Handler'
						)
					),
					'0.1'
				);
				continue;
			}

			$embed_handler->register_embed();
			$embed_handlers[] = $embed_handler;
		}

		return $embed_handlers;
	}

	/**
	 * Unregister embed handlers.
	 *
	 * @param AMP_Base_Embed_Handler[] $embed_handlers Embed handlers.
	 */
	private function unregister_embed_handlers( $embed_handlers ) {
		foreach ( $embed_handlers as $embed_handler ) {
			$this->add_scripts( $embed_handler->get_scripts() ); // @todo Why add_scripts here? Shouldn't it be array_diff()?
			$embed_handler->unregister_embed();
		}
	}

	/**
	 * Sanitize.
	 *
	 * @see AMP_Content_Sanitizer::sanitize()
	 * @param string $content Content.
	 * @return string Sanitized content.
	 */
	private function sanitize( $content ) {
		$dom = AMP_DOM_Utils::get_dom_from_content( $content );

		$results = AMP_Content_Sanitizer::sanitize_document( $dom, $this->sanitizer_classes, $this->args );

		$this->add_scripts( $results['scripts'] );
		$this->add_stylesheets( $results['stylesheets'] );

		return AMP_DOM_Utils::get_content_from_dom( $dom );
	}
}
```

</details>
