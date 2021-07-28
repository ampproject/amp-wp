/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FormattedMemoryValue from '../formatted-memory-value';
import SourceLabel from '../source-label';
import SourcesStack from '../sources-stack';

//eslint-disable-next-line complexity
export default function SourceDetailValue( { slug, source } ) {
	switch ( slug ) {
		case 'sources':
			return <SourcesStack sources={ source.sources } />;
		case 'type':
			return <SourceLabel type={ source.type } />;
		case 'name':
		case 'shortcode':
		case 'handle':
		case 'dependency_handle':
		case 'block_name':
		case 'post_type':
			return (
				<code>
					{ source[ slug ] }
				</code>
			);
		case 'function':
			return (
				<code>
					{ '{closure}' === source.function
						? source.function
						: `${ source.function }()` }
				</code>
			);
		case 'location':
			if ( ! source.location.link_url ) {
				return source.location.link_text;
			}

			const { protocol } = new URL( source.location.link_url );

			// Open link in new window unless the user has filtered the URL to open their system IDE.
			if ( [ 'http:', 'https:' ].includes( protocol ) ) {
				return (
					<a href={ source.location.link_url } target="_blank" rel="noreferrer">
						{ source.location.link_text }
					</a>
				);
			}

			return (
				<a href={ source.location.link_url }>
					{ source.location.link_text }
				</a>
			);
		case 'extra_key':
			if ( source.dependency_type === 'style' ) {
				return (
					<code>
						{ `wp_add_inline_style( "${ source.handle }", … )` }
					</code>
				);
			}
			if ( source.extra_key === 'data' ) {
				return (
					<code>
						{ `wp_localize_script( "${ source.handle }", … )` }
					</code>
				);
			}
			if ( source.extra_key === 'before' ) {
				return (
					<code>
						{ `wp_add_inline_script( "${ source.handle }", …, "before" )` }
					</code>
				);
			}
			if ( source.extra_key === 'after' ) {
				return (
					<code>
						{ `wp_add_inline_script( "${ source.handle }", …, "after" )` }
					</code>
				);
			}

			return null;
		case 'hook':
			return (
				<>
					<code>
						{ source.hook }
					</code>
					{ source.priority && source.priority !== null && sprintf(
						/* translators: %d is the hook priority */
						__( '(priority: %d)', 'amp' ),
						source.priority,
					) }
				</>
			);
		case 'text':
			return (
				<details>
					<summary>
						<FormattedMemoryValue
							value={ source.text.length }
							unit="B"
						/>
					</summary>
					<pre>
						{ source.text }
					</pre>
				</details>
			);
		default:
			// Render scalar value as is.
			if ( ( /boolean|number|string/ ).test( typeof source[ slug ] ) ) {
				return source[ slug ];
			}

			return (
				<pre>
					{ JSON.stringify( source[ slug ] ) }
				</pre>
			);
	}
}
SourceDetailValue.propTypes = {
	slug: PropTypes.string,
	source: PropTypes.object,
};
