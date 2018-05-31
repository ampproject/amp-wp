<?php
/**
 * Class AMP_Content
 *
 * @package AMP
 */

/**
 * Class AMP_Content
 */
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
	private $amp_scripts = array();

	/**
	 * AMP styles.
	 *
	 * @var array
	 */
	private $amp_styles = array();

	/**
	 * Args.
	 *
	 * @var array
	 */
	private $args = array();

	/**
	 * Embed handler class names.
	 *
	 * @var string[]
	 */
	private $embed_handler_classes = array();

	/**
	 * Sanitizer class names.
	 *
	 * @var string[]
	 */
	private $sanitizer_classes = array();

	/**
	 * AMP_Content constructor.
	 *
	 * @param string   $content               Content.
	 * @param string[] $embed_handler_classes Embed handler class names.
	 * @param string[] $sanitizer_classes     Sanitizer class names.
	 * @param array    $args                  Args.
	 */
	public function __construct( $content, $embed_handler_classes, $sanitizer_classes, $args = array() ) {
		$this->content               = $content;
		$this->args                  = $args;
		$this->embed_handler_classes = $embed_handler_classes;
		$this->sanitizer_classes     = $sanitizer_classes;

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
	 * @return array
	 */
	public function get_amp_styles() {
		return $this->amp_styles;
	}

	/**
	 * Transform.
	 */
	private function transform() {
		$content = $this->content;

		// First, embeds + the_content filter.
		$embed_handlers = $this->register_embed_handlers();
		$content        = apply_filters( 'the_content', $content );
		$this->unregister_embed_handlers( $embed_handlers );

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
	 * Add styles.
	 *
	 * @param array $styles Styles.
	 */
	private function add_styles( $styles ) {
		$this->amp_styles = array_merge( $this->amp_styles, $styles );
	}

	/**
	 * Register embed handlers.
	 *
	 * @return array
	 */
	private function register_embed_handlers() {
		$embed_handlers = array();

		foreach ( $this->embed_handler_classes as $embed_handler_class => $args ) {
			$embed_handler = new $embed_handler_class( array_merge( $this->args, $args ) );

			if ( ! is_subclass_of( $embed_handler, 'AMP_Base_Embed_Handler' ) ) {
				/* translators: %s is embed handler */
				_doing_it_wrong( __METHOD__, esc_html( sprintf( __( 'Embed Handler (%s) must extend `AMP_Embed_Handler`', 'amp' ), $embed_handler_class ) ), '0.1' );
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
	 * @param array $embed_handlers Embed handlers.
	 */
	private function unregister_embed_handlers( $embed_handlers ) {
		foreach ( $embed_handlers as $embed_handler ) {
			$this->add_scripts( $embed_handler->get_scripts() );
			$embed_handler->unregister_embed();
		}
	}

	/**
	 * Sanitize.
	 *
	 * @see AMP_Content_Sanitizer::sanitize()
	 * @param string $content Content.
	 * @return array Sanitized content.
	 */
	private function sanitize( $content ) {
		list( $sanitized_content, $scripts, $styles ) = AMP_Content_Sanitizer::sanitize( $content, $this->sanitizer_classes, $this->args );

		$this->add_scripts( $scripts );
		$this->add_styles( $styles );

		return $sanitized_content;
	}
}
