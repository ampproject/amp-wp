<?php
/**
 * Class PluginSuppression.
 *
 * @package AmpProject\AmpWP
 */

namespace AmpProject\AmpWP;

use AMP_Options_Manager;
use AMP_Validation_Error_Taxonomy;
use AMP_Validation_Manager;
use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use WP_Block_Type_Registry;
use WP_Hook;
use WP_Term;
use WP_User;

/**
 * Suppress plugins from running by removing their hooks and nullifying their shortcodes, widgets, and blocks.
 *
 * @package AmpProject\AmpWP
 */
final class PluginSuppression implements Service, Registerable {

	/**
	 * Plugin registry to use.
	 *
	 * @var PluginRegistry
	 */
	private $plugin_registry;

	/**
	 * Instantiate the plugin suppression service.
	 *
	 * @param PluginRegistry $plugin_registry Plugin registry to use.
	 */
	public function __construct( PluginRegistry $plugin_registry ) {
		$this->plugin_registry = $plugin_registry;
	}

	/**
	 * Register the service with the system.
	 *
	 * @return void
	 */
	public function register() {
		$priority = defined( 'PHP_INT_MIN' ) ? PHP_INT_MIN : ~PHP_INT_MAX; // phpcs:ignore PHPCompatibility.Constants.NewConstants.php_int_minFound
		add_action( 'wp', [ $this, 'suppress_plugins' ], $priority );
		add_action( 'amp_options_menu_items', [ $this, 'add_settings_field' ] );
	}

	/**
	 * Suppress plugins.
	 */
	public function suppress_plugins() {
		if ( ! is_amp_endpoint() ) {
			return;
		}

		$suppressed = AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS );
		if ( empty( $suppressed ) ) {
			return;
		}

		$suppressed_plugin_slugs = array_keys( $suppressed );

		$this->suppress_hooks( $suppressed_plugin_slugs );
		$this->suppress_shortcodes( $suppressed_plugin_slugs );
		$this->suppress_blocks( $suppressed_plugin_slugs );
		$this->suppress_widgets( $suppressed_plugin_slugs );
	}

	/**
	 * Add settings field.
	 */
	public function add_settings_field() {
		if ( count( $this->get_suppressible_plugins() ) > 0 ) {
			add_settings_field(
				Option::SUPPRESSED_PLUGINS,
				__( 'Plugin Suppression', 'amp' ),
				[ $this, 'render_suppressed_plugins' ],
				AMP_Options_Manager::OPTION_NAME,
				'general',
				[
					'class' => 'amp-suppressed-plugins',
				]
			);
		}
	}

	/**
	 * Render suppressed plugins.
	 */
	public function render_suppressed_plugins() {
		?>
		<fieldset>
			<h4 class="title hidden"><?php esc_html_e( 'Suppressed Plugins', 'amp' ); ?></h4>
			<p>
				<?php esc_html_e( 'When a plugin emits invalid markup that causes an AMP validation error, one option is to review the invalid markup and allow it to be removed. Another option is to suppress the plugin from doing anything when rendering AMP pages. What follows is the list of active plugins with any causing validation errors being highlighted. If a plugin is emitting invalid markup that is causing validation errors and this plugin is not necessary on the AMP version of the page, it can be suppressed.', 'amp' ); ?>
			</p>

			<style>
				#suppressed-plugins-table {
					margin-top: 20px;
				}

				#suppressed-plugins-table th {
					font-weight: 400;
				}

				#suppressed-plugins-table th,
				#suppressed-plugins-table td {
					padding: 8px 10px;
				}

				#suppressed-plugins-table .column-status {
					width: 120px;
				}

				#suppressed-plugins-table .column-status > select {
					width: 100%;
				}

				#suppressed-plugins-table .column-plugin {
					width: 45%;
				}

				#suppressed-plugins-table .column-plugin .plugin-author-uri {
					margin-top: 0;
				}

				#suppressed-plugins-table .column-details {
					width: 50%;
				}

				#suppressed-plugins-table tbody th,
				#suppressed-plugins-table tbody td {
					vertical-align: top;
				}

				#suppressed-plugins-table details > ul {
					margin-left: 30px;
					margin-top: 0.5em;
					margin-bottom: 1em;
					list-style-type: disc;
				}

				#suppressed-plugins-table summary {
					user-select: none;
					cursor: pointer;
					line-height: 30px; /* To match .wp-core-ui select */
				}

				li.error-removed {
					color: <?php echo esc_html( Icon::valid()->get_color() ); ?>;
				}

				li.error-kept {
					color: <?php echo esc_html( Icon::invalid()->get_color() ); ?>;
				}

				li.error-unreviewed > a {
					font-weight: bold;
				}

				@media screen and (max-width: 782px) {

					#suppressed-plugins-table summary {
						line-height: 40px; /* To match .wp-core-ui select */
					}

					#suppressed-plugins-table {
						display: table;
					}

					#suppressed-plugins-table th,
					#suppressed-plugins-table td {
						display: table-cell;
					}
				}
			</style>

			<?php
			$suppressed_plugins = AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS );
			$plugins            = array_intersect_key( // Note that wp_array_slice_assoc() doesn't preserve sort order.
				$this->plugin_registry->get_plugins( true ),
				array_fill_keys( $this->get_suppressible_plugins(), true )
			);

			$errors_by_sources = AMP_Validated_URL_Post_Type::get_recent_validation_errors_by_source();
			$select_options    = [
				'0' => __( 'Active', 'amp' ),
				'1' => __( 'Suppressed', 'amp' ),
			];
			?>
			<table id="suppressed-plugins-table" class="wp-list-table widefat fixed striped">
				<thead>
					<tr>
						<th class="column-status" scope="col"><?php esc_html_e( 'Status', 'amp' ); ?></th>
						<th class="column-plugin" scope="col"><?php esc_html_e( 'Plugin', 'amp' ); ?></th>
						<th class="column-details" scope="col"><?php esc_html_e( 'Details', 'amp' ); ?></th>
					</tr>
				</thead>
				<tbody>
				<?php foreach ( $plugins as $plugin_slug => $plugin ) : ?>
					<?php
					$is_suppressed = array_key_exists( $plugin_slug, $suppressed_plugins );
					$select_name   = sprintf( '%s[%s][%s]', AMP_Options_Manager::OPTION_NAME, Option::SUPPRESSED_PLUGINS, $plugin_slug );
					?>
					<tr>
						<th class="column-status" scope="row">
							<label for="<?php echo esc_attr( $select_name ); ?>" class="screen-reader-text">
								<?php esc_html_e( 'Plugin status:', 'amp' ); ?>
							</label>
							<select id="<?php echo esc_attr( $select_name ); ?>" name="<?php echo esc_attr( $select_name ); ?>">
								<?php foreach ( $select_options as $value => $text ) : ?>
									<option value="<?php echo esc_attr( $value ); ?>" <?php selected( (string) $is_suppressed, $value ); ?>>
										<?php echo esc_html( $text ); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</th>
						<td class="column-plugin">
							<?php $needs_details = ( ! empty( $plugin['Description'] ) || ! empty( $plugin['Author'] ) ); ?>

							<?php if ( $needs_details ) : ?>
							<details><summary>
									<?php endif; ?>

									<?php if ( ! empty( $plugin['PluginURI'] ) ) : ?>
									<a href="<?php echo esc_url( $plugin['PluginURI'] ); ?>" target="_blank">
										<?php endif; ?>
										<strong><?php echo esc_html( $plugin['Name'] ); ?></strong>
										<?php if ( ! empty( $plugin['PluginURI'] ) ) : ?>
									</a>
								<?php endif; ?>

									<?php if ( $needs_details ) : ?>
								</summary>
								<?php endif; ?>

								<?php if ( ! empty( $plugin['Author'] ) ) : ?>
									<p class="plugin-author-uri">
										<small>
											<?php
											if ( ! empty( $plugin['AuthorURI'] ) ) {
												$author = sprintf( '<a href="%s" target="_blank">%s</a>', esc_url( $plugin['AuthorURI'] ), esc_html( $plugin['Author'] ) );
											} else {
												$author = esc_html( $plugin['Author'] );
											}
											/* translators: %s is author name */
											echo wp_kses_post( sprintf( __( 'By %s', 'amp' ), $author ) );
											?>
										</small>
									</p>
								<?php endif; ?>

								<?php if ( ! empty( $plugin['Description'] ) ) : ?>
									<div class="plugin-description">
										<?php echo wp_kses_post( wpautop( $plugin['Description'] ) ); ?>
									</div>
								<?php endif; ?>

								<?php if ( $needs_details ) : ?>
							</details>
						<?php endif; ?>
						</td>
						<td class="column-details">
							<?php if ( $is_suppressed ) : ?>
								<p>
									<?php if ( isset( $suppressed_plugins[ $plugin_slug ][ Option::SUPPRESSED_PLUGINS_TIMESTAMP ] ) ) : ?>
										<?php
										printf(
										/* translators: %s is the date at which suppression occurred */
											esc_html__( 'Since %s.', 'amp' ),
											sprintf(
												'<time datetime="%s">%s</time>',
												esc_attr( gmdate( 'c', $suppressed_plugins[ $plugin_slug ][ Option::SUPPRESSED_PLUGINS_TIMESTAMP ] ) ),
												esc_html( date_i18n( get_option( 'date_format' ), $suppressed_plugins[ $plugin_slug ][ Option::SUPPRESSED_PLUGINS_TIMESTAMP ] ) )
											)
										);
										?>
									<?php endif; ?>

									<?php if ( isset( $suppressed_plugins[ $plugin_slug ][ Option::SUPPRESSED_PLUGINS_USERNAME ] ) ) : ?>
										<?php
										$user = get_user_by( 'slug', $suppressed_plugins[ $plugin_slug ][ Option::SUPPRESSED_PLUGINS_USERNAME ] );
										if ( $user instanceof WP_User ) {
											if ( wp_get_current_user()->user_nicename === $user->user_nicename ) {
												esc_html_e( 'Done by you.', 'amp' );
											} else {
												/* translators: %s is a user */
												echo esc_html( sprintf( __( 'Done by %s.', 'amp' ), $user->display_name ) );
											}
										} else {
											/* translators: %s is a user */
											echo esc_html( sprintf( __( 'Done by %s.', 'amp' ), $suppressed_plugins[ $plugin_slug ][ Option::SUPPRESSED_PLUGINS_USERNAME ] ) );
										}
										?>
									<?php endif; ?>

									<?php if ( version_compare( $suppressed_plugins[ $plugin_slug ][ Option::SUPPRESSED_PLUGINS_LAST_VERSION ], $plugin['Version'], '!=' ) ) : ?>
										<?php if ( $plugin['Version'] ) : ?>
											<?php
											echo esc_html(
												sprintf(
												/* translators: %1: version at which suppressed, %2: current version */
													__( 'Now updated to version %1$s since suppressed at %2$s.', 'amp' ),
													$plugin['Version'],
													$suppressed_plugins[ $plugin_slug ][ Option::SUPPRESSED_PLUGINS_LAST_VERSION ]
												)
											);
											?>
										<?php else : ?>
											<?php esc_html_e( 'Plugin updated since last suppressed.', 'amp' ); ?>
										<?php endif; ?>
									<?php endif; ?>
								</p>
							<?php elseif ( ! $is_suppressed && ! empty( $errors_by_sources['plugin'][ $plugin_slug ] ) ) : ?>
								<?php $this->render_validation_error_details( $errors_by_sources['plugin'][ $plugin_slug ] ); ?>
							<?php endif ?>
						</td>
					</tr>
				<?php endforeach; ?>
				</tbody>
			</table>
		</fieldset>
		<?php
	}

	/**
	 * Render validation errors into <details> element.
	 *
	 * @param array $validation_errors Validation errors.
	 */
	private function render_validation_error_details( $validation_errors ) {
		?>
		<details>
			<summary>
				<?php
				echo esc_html(
					sprintf(
						/* translators: %s is the error count */
						_n(
							'%s validation error',
							'%s validation errors',
							count( $validation_errors ),
							'amp'
						),
						number_format_i18n( count( $validation_errors ) )
					)
				);
				?>
			</summary>
			<ul>
				<?php foreach ( $validation_errors as $validation_error ) : ?>
					<?php
					/** @var WP_Term */
					$term = $validation_error['term'];

					$edit_term_url = admin_url(
						add_query_arg(
							[
								AMP_Validation_Error_Taxonomy::TAXONOMY_SLUG => $term->name,
								'post_type' => AMP_Validated_URL_Post_Type::POST_TYPE_SLUG,
							],
							'edit.php'
						)
					);

					$is_removed  = ( (int) $term->term_group & AMP_Validation_Error_Taxonomy::ACCEPTED_VALIDATION_ERROR_BIT_MASK );
					$is_reviewed = ( (int) $term->term_group & AMP_Validation_Error_Taxonomy::ACKNOWLEDGED_VALIDATION_ERROR_BIT_MASK );
					$tooltip     = sprintf(
						/* translators: %1 is whether validation error is 'removed' or 'kept', %2 is whether validation error is 'reviewed' or 'unreviewed' */
						__( 'Invalid markup causing the validation error is %1$s and %2$s. See all validated URL(s) with this validation error.', 'amp' ),
						$is_removed ? __( 'removed', 'amp' ) : __( 'kept', 'amp' ),
						$is_reviewed ? __( 'reviewed', 'amp' ) : __( 'unreviewed', 'amp' )
					);
					?>
					<li class="<?php echo esc_attr( sprintf( 'error-%s error-%s', $is_removed ? 'removed' : 'kept', $is_reviewed ? 'reviewed' : 'unreviewed' ) ); ?>">
						<a href="<?php echo esc_url( $edit_term_url ); ?>" target="_blank" title="<?php echo esc_attr( $tooltip ); ?>">
							<?php echo wp_kses_post( AMP_Validation_Error_Taxonomy::get_error_title_from_code( $validation_error['data'] ) ); ?>
						</a>
					</li>
				<?php endforeach; ?>
			</ul>
		</details>
		<?php
	}

	/**
	 * Get suppressible plugin slugs.
	 *
	 * @return string[] Plugin slugs which are suppressible.
	 */
	private function get_suppressible_plugins() {
		$errors_by_source        = AMP_Validated_URL_Post_Type::get_recent_validation_errors_by_source();
		$erroring_plugin_slugs   = isset( $errors_by_source['plugin'] ) ? array_keys( $errors_by_source['plugin'] ) : [];
		$suppressed_plugin_slugs = array_keys( AMP_Options_Manager::get_option( Option::SUPPRESSED_PLUGINS ) );
		$active_plugin_slugs     = array_keys( $this->plugin_registry->get_plugins( true ) );

		// The suppressible plugins are the set of plugins which are erroring and/or suppressed, which are also active.
		return array_unique(
			array_intersect(
				array_merge( $erroring_plugin_slugs, $suppressed_plugin_slugs ),
				$active_plugin_slugs
			)
		);
	}

	/**
	 * Suppress plugin hooks.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global WP_Hook[] $wp_filter
	 */
	private function suppress_hooks( $suppressed_plugins ) {
		global $wp_filter;
		foreach ( $wp_filter as $tag => $filter ) {
			foreach ( $filter->callbacks as $priority => $prioritized_callbacks ) {
				foreach ( $prioritized_callbacks as $callback ) {
					if ( $this->is_callback_plugin_suppressed( $callback['function'], $suppressed_plugins ) ) {
						$filter->remove_filter( $tag, $callback['function'], $priority );
					}
				}
			}
		}
	}

	/**
	 * Suppress plugin shortcodes.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global array $shortcode_tags
	 */
	private function suppress_shortcodes( $suppressed_plugins ) {
		global $shortcode_tags;

		foreach ( array_keys( $shortcode_tags ) as $tag ) {
			if ( $this->is_callback_plugin_suppressed( $shortcode_tags[ $tag ], $suppressed_plugins ) ) {
				add_shortcode( $tag, '__return_empty_string' );
			}
		}
	}

	/**
	 * Suppress plugin blocks.
	 *
	 * @todo What about static blocks added?
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 */
	private function suppress_blocks( $suppressed_plugins ) {
		$registry = WP_Block_Type_Registry::get_instance();

		foreach ( $registry->get_all_registered() as $block_type ) {
			if ( ! $block_type->is_dynamic() || ! $this->is_callback_plugin_suppressed( $block_type->render_callback, $suppressed_plugins ) ) {
				continue;
			}
			unset( $block_type->script, $block_type->style );
			$block_type->render_callback = '__return_empty_string';
		}
	}

	/**
	 * Suppress plugin widgets.
	 *
	 * @see \AMP_Validation_Manager::wrap_widget_callbacks() Which needs to run after this.
	 *
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @global array $wp_registered_widgets
	 */
	private function suppress_widgets( $suppressed_plugins ) {
		global $wp_registered_widgets;
		foreach ( $wp_registered_widgets as &$registered_widget ) {
			if ( $this->is_callback_plugin_suppressed( $registered_widget['callback'], $suppressed_plugins ) ) {
				$registered_widget['callback'] = '__return_null';
			}
		}
	}

	/**
	 * Determine whether callback is from a suppressed plugin.
	 *
	 * @param callable $callback           Callback.
	 * @param string[] $suppressed_plugins Suppressed plugins.
	 * @return bool Whether from suppressed plugin.
	 */
	private function is_callback_plugin_suppressed( $callback, $suppressed_plugins ) {
		$source = AMP_Validation_Manager::get_source( $callback );
		return (
			isset( $source['type'], $source['name'] ) &&
			'plugin' === $source['type'] &&
			in_array( $source['name'], $suppressed_plugins, true )
		);
	}
}
