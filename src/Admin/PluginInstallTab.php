<?php
/**
 * Add new tab (AMP) in plugin install screen in WordPress admin.
 *
 * @package Ampproject\Ampwp
 */

namespace AmpProject\AmpWP\Admin;

use AmpProject\AmpWP\Infrastructure\Conditional;
use AmpProject\AmpWP\Infrastructure\Delayed;
use AmpProject\AmpWP\Infrastructure\Registerable;
use AmpProject\AmpWP\Infrastructure\Service;
use stdClass;
use function get_current_screen;

/**
 * Add new tab (AMP) in plugin install screen in WordPress admin.
 *
 * @since 2.2
 * @internal
 */
class PluginInstallTab implements Conditional, Delayed, Service, Registerable {

	/**
	 * @var array List AMP plugins.
	 */
	protected $plugins = [];

	/**
	 * Get the action to use for registering the service.
	 *
	 * @return string Registration action to use.
	 */
	public static function get_registration_action() {

		return 'current_screen';
	}

	/**
	 * Check whether the conditional object is currently needed.
	 *
	 * @return bool Whether the conditional object is needed.
	 */
	public static function is_needed() {

		if ( wp_doing_ajax() || ! is_admin() ) {
			return false;
		}

		$screen = get_current_screen();

		if ( ! $screen instanceof \WP_Screen || 'plugin-install' !== $screen->id ) {
			return false;
		}

		return true;
	}

	/**
	 * Fetch AMP plugin data.
	 *
	 * @return void
	 */
	protected function set_plugins() {

		$plugin_json   = AMP__DIR__ . '/data/plugins.json';
		$json_data     = file_get_contents( $plugin_json );
		$this->plugins = json_decode( $json_data, true );
	}

	/**
	 * Adds hooks.
	 *
	 * @return void
	 */
	public function register() {

		$this->set_plugins();

		add_filter( 'install_plugins_tabs', [ $this, 'add_tab' ] );
		add_filter( 'install_plugins_table_api_args_px_enhancing', [ $this, 'tab_args' ] );
		add_filter( 'plugins_api', [ $this, 'plugins_api' ], 10, 3 );
		add_filter( 'plugin_install_action_links', [ $this, 'action_links' ], 10, 2 );

		add_action( 'install_plugins_px_enhancing', [ $this, 'install_plugin_amp' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}

	/**
	 * Enqueue style for plugin install page.
	 *
	 * @return void
	 */
	public function enqueue_scripts() {

		wp_enqueue_style(
			'amp-admin',
			amp_get_asset_url( 'css/amp-admin.css' ),
			[],
			AMP__VERSION
		);

	}

	/**
	 * Add extra tab in plugin install screen.
	 *
	 * @param array $tabs List of tab in plugin install screen.
	 *
	 * @return array List of tab in plugin install screen.
	 */
	public function add_tab( $tabs ) {

		return array_merge(
			[
				'px_enhancing' => '<span class="amp-logo-icon"></span> ' . esc_html__( 'PX Enhancing', 'amp' ),
			],
			$tabs
		);
	}

	/**
	 * To modify args for AMP tab in plugin install screen.
	 *
	 * @return array
	 */
	public function tab_args() {

		return [
			'px_enhancing' => true,
			'per_page'     => count( $this->plugins ),
		];
	}

	/**
	 * Filter the response of API call to wordpress.org for plugin data.
	 *
	 * @param bool|array $response List of AMP compatible plugins.
	 * @param string     $action   API Action.
	 * @param array      $args     Args for plugin list.
	 *
	 * @return stdClass|array List of AMP compatible plugins.
	 */
	public function plugins_api( $response, $action, $args ) {

		$args = (array) $args;
		if ( ! isset( $args['px_enhancing'] ) ) {
			return $response;
		}

		$response          = new stdClass();
		$response->plugins = $this->plugins;
		$response->info    = [
			'page'    => 1,
			'pages'   => 1,
			'results' => count( $response->plugins ),
		];

		return $response;
	}

	/**
	 * Update action links for plugin card in plugin install screen.
	 *
	 * @param array $actions List of action button's markup for plugin card.
	 * @param array $plugin  Plugin detail.
	 *
	 * @return array List of action button's markup for plugin card.
	 */
	public function action_links( $actions, $plugin ) {

		if ( isset( $plugin['wporg'] ) && true !== $plugin['wporg'] ) {
			$actions = [];

			if ( ! empty( $plugin['homepage'] ) ) {
				$actions[] = sprintf(
					'<a href="%s" target="_blank" aria-label="Site link for %s">%s</a>',
					esc_url( $plugin['homepage'] ),
					esc_html( $plugin['name'] ),
					esc_html__( 'Visit site', 'amp' )
				);
			}
		}

		return $actions;
	}

	/**
	 * Content for AMP tab in plugin install screen.
	 *
	 * @return void
	 */
	public function install_plugin_amp() {

		?>
		<form id="plugin-filter" method="post">
			<?php $this->display(); ?>
		</form>
		<?php
	}

	/**
	 * Displays the plugin install table.
	 * Overrides the parent display() method to provide a different container.
	 *
	 * @reference
	 */
	public function display() {

		global $wp_list_table;

		$wp_list_table->display_tablenav( 'top' );

		?>
		<div class="wp-list-table <?php echo esc_attr( implode( ' ', $wp_list_table->get_table_classes() ) ); ?>">
			<?php $wp_list_table->screen->render_screen_reader_content( 'heading_list' ); ?>
			<div id="the-list">
				<?php $this->display_rows_or_placeholder(); ?>
			</div>
		</div>
		<?php
		$wp_list_table->display_tablenav( 'bottom' );
	}

	/**
	 * Generates the tbody element for the list table.
	 *
	 * @reference \WP_Plugin_Install_List_Table::display_rows_or_placeholder()
	 *
	 * @return void
	 */
	public function display_rows_or_placeholder() {

		global $wp_list_table;

		if ( $wp_list_table->has_items() ) {
			$this->display_rows();
		} else {
			echo '<tr class="no-items"><td class="colspanchange" colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '">';
			$wp_list_table->no_items();
			echo '</td></tr>';
		}
	}

	/**
	 * Generate rows for plugins for install plugin screen.
	 * overwrite \WP_Plugin_Install_List_Table::display_rows()
	 *
	 * @reference \WP_Plugin_Install_List_Table::display_rows()
	 *
	 * @return void
	 */
	public function display_rows() {

		global $wp_list_table;
		$plugins_allowedtags = [
			'a'       => [
				'href'   => [],
				'title'  => [],
				'target' => [],
			],
			'abbr'    => [ 'title' => [] ],
			'acronym' => [ 'title' => [] ],
			'code'    => [],
			'pre'     => [],
			'em'      => [],
			'strong'  => [],
			'ul'      => [],
			'ol'      => [],
			'li'      => [],
			'p'       => [],
			'br'      => [],
		];

		$plugins_group_titles = [
			'Performance' => _x( 'Performance', 'Plugin installer group title', 'amp' ),
			'Social'      => _x( 'Social', 'Plugin installer group title', 'amp' ),
			'Tools'       => _x( 'Tools', 'Plugin installer group title', 'amp' ),
		];

		$group = null;

		foreach ( (array) $wp_list_table->items as $plugin ) {
			if ( is_object( $plugin ) ) {
				$plugin = (array) $plugin;
			}

			// Display the group heading if there is one.
			if ( isset( $plugin['group'] ) && $plugin['group'] !== $group ) {
				if ( isset( $wp_list_table->groups[ $plugin['group'] ] ) ) {
					$group_name = $wp_list_table->groups[ $plugin['group'] ];
					if ( isset( $plugins_group_titles[ $group_name ] ) ) {
						$group_name = $plugins_group_titles[ $group_name ];
					}
				} else {
					$group_name = $plugin['group'];
				}

				// Starting a new group, close off the divs of the last one.
				if ( ! empty( $group ) ) {
					echo '</div></div>';
				}

				echo '<div class="plugin-group"><h3>' . esc_html( $group_name ) . '</h3>';
				// Needs an extra wrapping div for nth-child selectors to work.
				echo '<div class="plugin-items">';

				$group = $plugin['group'];
			}

			$title = wp_kses( $plugin['name'], $plugins_allowedtags );

			// Remove any HTML from the description.
			$description = wp_strip_all_tags( $plugin['short_description'] );
			$version     = wp_kses( $plugin['version'], $plugins_allowedtags );

			$name = strip_tags( $title . ' ' . $version ); // phpcs:ignore WordPressVIPMinimum.Functions.StripTags.StripTagsOneParameter

			$author = wp_kses( $plugin['author'], $plugins_allowedtags );
			if ( ! empty( $author ) ) {
				/* translators: %s: Plugin author. */
				$author = ' <cite>' . sprintf( __( 'By %s', 'amp' ), $author ) . '</cite>';
			}

			$requires_php = isset( $plugin['requires_php'] ) ? $plugin['requires_php'] : null;
			$requires_wp  = isset( $plugin['requires'] ) ? $plugin['requires'] : null;

			$compatible_php = is_php_version_compatible( $requires_php );
			$compatible_wp  = is_wp_version_compatible( $requires_wp );
			$tested_wp      = ( empty( $plugin['tested'] ) || version_compare( get_bloginfo( 'version' ), $plugin['tested'], '<=' ) );

			$action_links = [];

			if ( current_user_can( 'install_plugins' ) || current_user_can( 'update_plugins' ) ) {
				$status = install_plugin_install_status( $plugin );

				switch ( $status['status'] ) {
					case 'install':
						if ( $status['url'] ) {
							if ( $compatible_php && $compatible_wp ) {
								$action_links[] = sprintf(
									'<a class="install-now button" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
									esc_attr( $plugin['slug'] ),
									esc_url( $status['url'] ),
									/* translators: %s: Plugin name and version. */
									esc_attr( sprintf( _x( 'Install %s now', 'plugin', 'amp' ), $name ) ),
									esc_attr( $name ),
									__( 'Install Now', 'amp' )
								);
							} else {
								$action_links[] = sprintf(
									'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
									_x( 'Cannot Install', 'plugin', 'amp' )
								);
							}
						}
						break;

					case 'update_available':
						if ( $status['url'] ) {
							if ( $compatible_php && $compatible_wp ) {
								$action_links[] = sprintf(
									'<a class="update-now button aria-button-if-js" data-plugin="%s" data-slug="%s" href="%s" aria-label="%s" data-name="%s">%s</a>',
									esc_attr( $status['file'] ),
									esc_attr( $plugin['slug'] ),
									esc_url( $status['url'] ),
									/* translators: %s: Plugin name and version. */
									esc_attr( sprintf( _x( 'Update %s now', 'plugin', 'amp' ), $name ) ),
									esc_attr( $name ),
									__( 'Update Now', 'amp' )
								);
							} else {
								$action_links[] = sprintf(
									'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
									_x( 'Cannot Update', 'plugin', 'amp' )
								);
							}
						}
						break;

					case 'latest_installed':
					case 'newer_installed':
						if ( is_plugin_active( $status['file'] ) ) {
							$action_links[] = sprintf(
								'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
								_x( 'Active', 'plugin', 'amp' )
							);
						} elseif ( current_user_can( 'activate_plugin', $status['file'] ) ) {
							$button_text = __( 'Activate', 'amp' );
							/* translators: %s: Plugin name. */
							$button_label = _x( 'Activate %s', 'plugin', 'amp' );
							$activate_url = add_query_arg(
								[
									'_wpnonce' => wp_create_nonce( 'activate-plugin_' . $status['file'] ),
									'action'   => 'activate',
									'plugin'   => $status['file'],
								],
								network_admin_url( 'plugins.php' )
							);

							if ( is_network_admin() ) {
								$button_text = __( 'Network Activate', 'amp' );
								/* translators: %s: Plugin name. */
								$button_label = _x( 'Network Activate %s', 'plugin', 'amp' );
								$activate_url = add_query_arg( [ 'networkwide' => 1 ], $activate_url );
							}

							$action_links[] = sprintf(
								'<a href="%1$s" class="button activate-now" aria-label="%2$s">%3$s</a>',
								esc_url( $activate_url ),
								esc_attr( sprintf( $button_label, $plugin['name'] ) ),
								$button_text
							);
						} else {
							$action_links[] = sprintf(
								'<button type="button" class="button button-disabled" disabled="disabled">%s</button>',
								_x( 'Installed', 'plugin', 'amp' )
							);
						}
						break;
				}
			}

			$details_link = self_admin_url(
				'plugin-install.php?tab=plugin-information&amp;plugin=' . $plugin['slug'] .
				'&amp;TB_iframe=true&amp;width=600&amp;height=550'
			);

			$action_links[] = sprintf(
				'<a href="%s" class="thickbox open-plugin-details-modal" aria-label="%s" data-title="%s">%s</a>',
				esc_url( $details_link ),
				/* translators: %s: Plugin name and version. */
				esc_attr( sprintf( __( 'More information about %s', 'amp' ), $name ) ),
				esc_attr( $name ),
				__( 'More Details', 'amp' )
			);

			if ( ! empty( $plugin['icons']['svg'] ) ) {
				$plugin_icon_url = $plugin['icons']['svg'];
			} elseif ( ! empty( $plugin['icons']['2x'] ) ) {
				$plugin_icon_url = $plugin['icons']['2x'];
			} elseif ( ! empty( $plugin['icons']['1x'] ) ) {
				$plugin_icon_url = $plugin['icons']['1x'];
			} else {
				$plugin_icon_url = $plugin['icons']['default'];
			}

			/**
			 * Filters the install action links for a plugin.
			 *
			 * @param string[] $action_links An array of plugin action links. Defaults are links to Details and Install Now.
			 * @param array    $plugin       The plugin currently being listed.
			 */
			$action_links = apply_filters( 'plugin_install_action_links', $action_links, $plugin );

			$last_updated_timestamp = strtotime( $plugin['last_updated'] );
			?>
			<div class="plugin-card plugin-card-<?php echo sanitize_html_class( $plugin['slug'] ); ?>">
				<?php
				if ( ! $compatible_php || ! $compatible_wp ) {
					echo '<div class="notice inline notice-error notice-alt"><p>';
					if ( ! $compatible_php && ! $compatible_wp ) {
						esc_html_e( 'This plugin doesn&#8217;t work with your versions of WordPress and PHP.', 'amp' );
						if ( current_user_can( 'update_core' ) && current_user_can( 'update_php' ) ) {
							echo wp_kses_post(
								sprintf(
								/* translators: 1: URL to WordPress Updates screen, 2: URL to Update PHP page. */
									' ' . __( '<a href="%1$s">Please update WordPress</a>, and then <a href="%2$s">learn more about updating PHP</a>.', 'amp' ),
									self_admin_url( 'update-core.php' ),
									esc_url( wp_get_update_php_url() )
								)
							);
							wp_update_php_annotation( '</p><p><em>', '</em>' );
						} elseif ( current_user_can( 'update_core' ) ) {
							echo wp_kses_post(
								sprintf(
								/* translators: %s: URL to WordPress Updates screen. */
									' ' . __( '<a href="%s">Please update WordPress</a>.', 'amp' ),
									self_admin_url( 'update-core.php' )
								)
							);
						} elseif ( current_user_can( 'update_php' ) ) {
							echo wp_kses_post(
								sprintf(
								/* translators: %s: URL to Update PHP page. */
									' ' . __( '<a href="%s">Learn more about updating PHP</a>.', 'amp' ),
									esc_url( wp_get_update_php_url() )
								)
							);
							wp_update_php_annotation( '</p><p><em>', '</em>' );
						}
					} elseif ( ! $compatible_wp ) {
						_e( 'This plugin doesn&#8217;t work with your version of WordPress.', 'amp' );
						if ( current_user_can( 'update_core' ) ) {
							echo wp_kses_post(
								sprintf(
								/* translators: %s: URL to WordPress Updates screen. */
									' ' . __( '<a href="%s">Please update WordPress</a>.', 'amp' ),
									self_admin_url( 'update-core.php' )
								)
							);
						}
					} elseif ( ! $compatible_php ) {
						_e( 'This plugin doesn&#8217;t work with your version of PHP.', 'amp' );
						if ( current_user_can( 'update_php' ) ) {
							echo wp_kses_post(
								sprintf(
								/* translators: %s: URL to Update PHP page. */
									' ' . __( '<a href="%s">Learn more about updating PHP</a>.', 'amp' ),
									esc_url( wp_get_update_php_url() )
								)
							);
							wp_update_php_annotation( '</p><p><em>', '</em>' );
						}
					}
					echo '</p></div>';
				}
				?>
				<div class="plugin-card-top">
					<div class="name column-name">
						<h3>
							<a href="<?php echo esc_url( $details_link ); ?>"
								class="thickbox open-plugin-details-modal">
								<?php echo wp_kses_post( $title ); ?>
								<img src="<?php echo esc_url( $plugin_icon_url ); ?>" class="plugin-icon" alt=""/>
							</a>
						</h3>
					</div>
					<div class="action-links">
						<?php
						if ( $action_links ) {
							echo wp_kses_post(
								'<ul class="plugin-action-buttons"><li>' . implode( '</li><li>', $action_links ) . '</li></ul>'
							);
						}
						?>
					</div>
					<div class="desc column-description">
						<p><?php echo wp_kses_post( $description ); ?></p>
						<p class="authors"><?php echo wp_kses_post( $author ); ?></p>
					</div>
				</div>
				<div class="plugin-card-bottom">
					<div class="vers column-rating">
						<?php
						wp_star_rating(
							[
								'rating' => $plugin['rating'],
								'type'   => 'percent',
								'number' => $plugin['num_ratings'],
							]
						);
						?>
						<span class="num-ratings" aria-hidden="true">
							(<?php echo esc_html( number_format_i18n( $plugin['num_ratings'] ) ); ?>)
						</span>
					</div>
					<div class="column-updated">
						<strong><?php esc_html_e( 'Last Updated:', 'amp' ); ?></strong>
						<?php
						echo esc_html(
						/* translators: %s: Human-readable time difference. */
							sprintf( __( '%s ago', 'amp' ), human_time_diff( $last_updated_timestamp ) )
						);
						?>
					</div>
					<div class="column-downloaded">
						<?php
						if ( $plugin['active_installs'] >= 1000000 ) {
							$active_installs_millions = floor( $plugin['active_installs'] / 1000000 );
							$active_installs_text     = sprintf(
							/* translators: %s: Number of millions. */
								_nx( '%s+ Million', '%s+ Million', $active_installs_millions, 'Active plugin installations', 'amp' ),
								number_format_i18n( $active_installs_millions )
							);
						} elseif ( 0 === $plugin['active_installs'] ) {
							$active_installs_text = _x( 'Less Than 10', 'Active plugin installations', 'amp' );
						} else {
							$active_installs_text = number_format_i18n( $plugin['active_installs'] ) . '+';
						}

						echo esc_html(
						/* translators: %s: Number of installations. */
							sprintf( __( '%s Active Installations', 'amp' ), $active_installs_text )
						);
						?>
					</div>
					<div class="column-compatibility">
						<?php
						if ( ! $tested_wp ) {
							echo wp_kses_post(
								'<span class="compatibility-untested">' . __( 'Untested with your version of WordPress', 'amp' ) . '</span>'
							);
						} elseif ( ! $compatible_wp ) {
							echo wp_kses_post(
								'<span class="compatibility-incompatible">' . __( '<strong>Incompatible</strong> with your version of WordPress', 'amp' ) . '</span>'
							);
						} else {
							echo wp_kses_post(
								'<span class="compatibility-compatible">' . __( '<strong>Compatible</strong> with your version of WordPress', 'amp' ) . '</span>'
							);
						}
						?>
					</div>
				</div>
				<div class="extension-card-px-message">
					<span class="amp-logo-icon">&nbsp;</span>
					<span class="tooltiptext">
						<?php
						esc_html_e( 'This plugin follow best practice and is known to work well with AMP plugin.', 'amp' );
						?>
					</span>
					<?php
					esc_html_e( 'Page Experience Enhancing', 'amp' );
					?>
				</div>
			</div>
			<?php
		}

		// Close off the group divs of the last one.
		if ( ! empty( $group ) ) {
			echo '</div></div>';
		}
	}
}
