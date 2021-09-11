/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

//eslint-disable-next-line complexity
export default function SourceDetailTitle( { slug, source } ) {
	//eslint-disable-next-line default-case
	switch ( slug ) {
		case 'name':
			return __( 'Name', 'amp' );
		case 'post_id':
			return __( 'Post ID', 'amp' );
		case 'post_type':
			return __( 'Post Type', 'amp' );
		case 'handle':
			if ( source?.dependency_type === 'script' ) {
				return __( 'Enqueued Script', 'amp' );
			}
			if ( source?.dependency_type === 'style' ) {
				return __( 'Enqueued Style', 'amp' );
			}
			break;
		case 'dependency_handle':
			if ( source?.dependency_type === 'script' ) {
				return __( 'Dependent Script', 'amp' );
			}
			if ( source?.dependency_type === 'style' ) {
				return __( 'Dependent Style', 'amp' );
			}
			break;
		case 'extra_key':
			return __( 'Inline Type', 'amp' );
		case 'text':
			return __( 'Inline Text', 'amp' );
		case 'block_content_index':
			return __( 'Block Index', 'amp' );
		case 'block_name':
			return __( 'Block Name', 'amp' );
		case 'shortcode':
			return __( 'Shortcode', 'amp' );
		case 'type':
			return __( 'Type', 'amp' );
		case 'function':
			return __( 'Function', 'amp' );
		case 'location':
			return __( 'Location', 'amp' );
		case 'sources':
			return __( 'Sources', 'amp' );
		case 'hook':
			return source?.filter
				? __( 'Filter', 'amp' )
				: __( 'Action', 'amp' );
	}

	return slug;
}
SourceDetailTitle.propTypes = {
	slug: PropTypes.string,
	source: PropTypes.shape( {
		dependency_type: PropTypes.string,
		filter: PropTypes.bool,
	} ).isRequired,
};
