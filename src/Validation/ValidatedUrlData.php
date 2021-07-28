<?php
/**
 * Validated URL data.
 *
 * @package AMP
 * @since 2.2
 */

namespace AmpProject\AmpWP\Validation;

use AMP_Style_Sanitizer;
use AMP_Validated_URL_Post_Type;
use AmpProject\AmpWP\Services;
use WP_Error;
use WP_Post;

/**
 * ValidatedUrlData class.
 *
 * @since 2.2
 * @internal
 */
final class ValidatedUrlData {

	/**
	 * Validated URL post.
	 *
	 * @var WP_Post
	 */
	private $post;

	/**
	 * Validated URL stylesheets data parsed from the JSON string in post meta.
	 *
	 * @var array|null
	 */
	private $stylesheets = null;

	/**
	 * ValidatedUrlDataProvider constructor.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function __construct( $post ) {
		$this->post = $post;
	}

	/**
	 * Get validated URL ID.
	 *
	 * @return int|null
	 */
	public function get_id() {
		if ( ! $this->post->ID ) {
			return null;
		}

		return $this->post->ID;
	}

	/**
	 * Get the URL that was validated.
	 *
	 * @return string|null
	 */
	public function get_url() {
		if ( ! $this->post ) {
			return null;
		}

		return AMP_Validated_URL_Post_Type::get_url_from_post( $this->post );
	}

	/**
	 * Get the date that the URL was validated.
	 *
	 * @return string|null
	 */
	public function get_date() {
		if ( ! $this->post->post_date ) {
			return null;
		}

		return $this->post->post_date;
	}

	/**
	 * Get the user that last validated the URL.
	 *
	 * @return int|null
	 */
	public function get_author() {
		if ( ! $this->post->post_author ) {
			return null;
		}

		return (int) $this->post->post_author;
	}

	/**
	 * Get the validated URL stylesheets data.
	 *
	 * @return array|WP_Error
	 */
	public function get_stylesheets() {
		if ( null !== $this->stylesheets ) {
			return $this->stylesheets;
		}

		$stylesheets = get_post_meta( $this->get_id(), AMP_Validated_URL_Post_Type::STYLESHEETS_POST_META_KEY, true );

		if ( empty( $stylesheets ) ) {
			return new WP_Error(
				'amp_validated_url_stylesheets_no_longer_available',
				__( 'Stylesheet information for this URL is no longer available. Such data is automatically deleted after a week to reduce database storage. It is of little value to store long-term given that it becomes stale as themes and plugins are updated. To obtain the latest stylesheet information, recheck this URL.', 'amp' )
			);
		}

		$stylesheets = json_decode( $stylesheets, true );

		if ( ! is_array( $stylesheets ) ) {
			return new WP_Error(
				'amp_validated_url_stylesheets_missing',
				__( 'Unable to retrieve stylesheets data for this URL.', 'amp' )
			);
		}

		foreach ( $stylesheets as $key => $stylesheet ) {
			$stylesheets[ $key ]['original_tag_abbr'] = $this->format_stylesheet_original_tag_abbreviation( $stylesheet['origin'] );
			$stylesheets[ $key ]['original_tag']      = $this->format_stylesheet_original_tag( $stylesheet['element']['name'], $stylesheet['element']['attributes'] );

			if ( ! empty( $stylesheet['sources'] ) ) {
				$stylesheets[ $key ]['sources'] = $this->format_sources( $stylesheet['sources'] );
			}
		}

		$this->stylesheets = $stylesheets;

		return $this->stylesheets;
	}

	/**
	 * Get validated environment information.
	 *
	 * @return array
	 */
	public function get_environment() {
		return get_post_meta( $this->get_id(), AMP_Validated_URL_Post_Type::VALIDATED_ENVIRONMENT_POST_META_KEY, true );
	}

	/**
	 * Format stylesheet original tag abbreviation.
	 *
	 * @param string $origin Original element.
	 *
	 * @return string Formatted tag abbreviation.
	 */
	private function format_stylesheet_original_tag_abbreviation( $origin ) {
		if ( 'link_element' === $origin ) {
			return '<link …>'; // @todo Consider adding the basename of the CSS file.
		}

		if ( 'style_element' === $origin ) {
			return '<style>';
		}

		if ( 'style_attribute' === $origin ) {
			return 'style="…"';
		}

		return '?';
	}

	/**
	 * Construct stylesheet original tag name based on the attributes.
	 *
	 * @param string $name       Original element name.
	 * @param array  $attributes Original element attributes.
	 *
	 * @return string
	 */
	private function format_stylesheet_original_tag( $name, $attributes ) {
		$result = '<' . $name;

		if ( ! empty( $attributes ) ) {
			if ( ! empty( $attributes['class'] ) ) {
				$attributes['class'] = trim( preg_replace( '/(^|\s)amp-wp-\w+(\s|$)/', ' ', $attributes['class'] ) );
				if ( empty( $attributes['class'] ) ) {
					unset( $attributes['class'] );
				}
			}
			if ( isset( $attributes[ AMP_Style_Sanitizer::ORIGINAL_STYLE_ATTRIBUTE_NAME ] ) ) {
				$attributes['style'] = $attributes[ AMP_Style_Sanitizer::ORIGINAL_STYLE_ATTRIBUTE_NAME ];
				unset( $attributes[ AMP_Style_Sanitizer::ORIGINAL_STYLE_ATTRIBUTE_NAME ] );
			}
			if ( ! empty( $attributes ) ) {
				foreach ( $attributes as $attribute_name => $attribute_value ) {
					if ( '' === $attribute_value ) {
						$result .= ' ' . sprintf( '%s', esc_html( $attribute_name ) );
					} else {
						$result .= ' ' . sprintf( '%s="%s"', esc_html( $attribute_name ), esc_attr( $attribute_value ) );
					}
				}
			}
		}

		$result .= '>';

		return $result;
	}

	/**
	 * Format sources list.
	 *
	 * @param array $sources Sources list.
	 *
	 * @return array Formatted sources array.
	 */
	private function format_sources( $sources ) {
		if ( empty( $sources ) ) {
			return $sources;
		}

		foreach ( $sources as $key => $source ) {
			if ( 'sources' === $source['type'] ) {
				return $this->format_sources( $source );
			}

			if ( isset( $source['file'], $source['line'] ) ) {
				$sources[ $key ]['location'] = [
					'link_text' => $source['file'] . ':' . $source['line'],
					'link_url'  => $this->get_file_editor_url( $source ),
				];
			}
		}

		return $sources;
	}

	/**
	 * Get the URL for opening the file for a AMP validation error in an external editor.
	 *
	 * @since 1.4
	 *
	 * @param array $source Source for AMP validation error.
	 * @return string|null File editor URL or null if not available.
	 */
	private function get_file_editor_url( $source ) {
		if ( ! isset( $source['file'], $source['line'], $source['type'], $source['name'] ) ) {
			return null;
		}

		$edit_url = null;

		/**
		 * Filters the template for the URL for linking to an external editor to open a file for editing.
		 *
		 * Users of IDEs that support opening files in via web protocols can use this filter to override
		 * the edit link to result in their editor opening rather than the theme/plugin editor.
		 *
		 * The initial filtered value is null, requiring extension plugins to supply the URL template
		 * string themselves. If no template string is provided, links to the theme/plugin editors will
		 * be provided if available. For example, for an extension plugin to cause file edit links to
		 * open in PhpStorm, the following filter can be used:
		 *
		 *     add_filter( 'amp_validation_error_source_file_editor_url_template', function () {
		 *         return 'phpstorm://open?file={{file}}&line={{line}}';
		 *     } );
		 *
		 * For a template to be considered, the string '{{file}}' must be present in the filtered value.
		 *
		 * @since 1.4
		 *
		 * @param string|null $editor_url_template Editor URL template.
		 */
		$editor_url_template = apply_filters( 'amp_validation_error_source_file_editor_url_template', null );

		// Supply the file path to the editor template.
		if ( null !== $editor_url_template && false !== strpos( $editor_url_template, '{{file}}' ) ) {
			$file_path = null;
			if ( 'core' === $source['type'] ) {
				if ( 'wp-includes' === $source['name'] ) {
					$file_path = ABSPATH . WPINC . '/' . $source['file'];
				} elseif ( 'wp-admin' === $source['name'] ) {
					$file_path = ABSPATH . 'wp-admin/' . $source['file'];
				}
			} elseif ( 'plugin' === $source['type'] ) {
				$file_path = WP_PLUGIN_DIR . '/' . $source['name'];
				if ( $source['name'] !== $source['file'] ) {
					$file_path .= '/' . $source['file'];
				}
			} elseif ( 'mu-plugin' === $source['type'] ) {
				$file_path = WPMU_PLUGIN_DIR . '/' . $source['name'];
			} elseif ( 'theme' === $source['type'] ) {
				$theme = wp_get_theme( $source['name'] );
				if ( $theme instanceof WP_Theme && ! $theme->errors() ) {
					$file_path = $theme->get_stylesheet_directory() . '/' . $source['file'];
				}
			}

			if ( $file_path && file_exists( $file_path ) ) {
				/**
				 * Filters the file path to be opened in an external editor for a given AMP validation error source.
				 *
				 * This is useful to map the file path from inside of a Docker container or VM to the host machine.
				 *
				 * @since 1.4
				 *
				 * @param string|null $editor_url_template Editor URL template.
				 * @param array       $source              Source information.
				 */
				$file_path = apply_filters( 'amp_validation_error_source_file_path', $file_path, $source );
				if ( $file_path ) {
					$edit_url = str_replace(
						[
							'{{file}}',
							'{{line}}',
						],
						[
							rawurlencode( $file_path ),
							rawurlencode( $source['line'] ),
						],
						$editor_url_template
					);
				}
			}
		}

		// Fall back to using the theme/plugin editors if no external editor is offered.
		if ( ! $edit_url ) {
			if ( 'plugin' === $source['type'] && current_user_can( 'edit_plugins' ) ) {
				$plugin_registry = Services::get( 'plugin_registry' );
				$plugin          = $plugin_registry->get_plugin_from_slug( $source['name'] );
				if ( $plugin ) {
					$file = $source['file'];

					// Prepend the plugin directory name to the file name as the plugin editor requires.
					$i = strpos( $plugin['file'], '/' );
					if ( false !== $i ) {
						$file = substr( $plugin['file'], 0, $i ) . '/' . $file;
					}

					$edit_url = add_query_arg(
						[
							'plugin' => rawurlencode( $plugin['file'] ),
							'file'   => rawurlencode( $file ),
							'line'   => rawurlencode( $source['line'] ),
						],
						admin_url( 'plugin-editor.php' )
					);
				}
			} elseif ( 'theme' === $source['type'] && current_user_can( 'edit_themes' ) ) {
				$edit_url = add_query_arg(
					[
						'file'  => rawurlencode( $source['file'] ),
						'theme' => rawurlencode( $source['name'] ),
						'line'  => rawurlencode( $source['line'] ),
					],
					admin_url( 'theme-editor.php' )
				);
			}
		}

		return $edit_url;
	}
}
