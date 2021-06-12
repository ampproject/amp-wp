/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Display media placeholder.
 *
 * @param {Object} props Component props.
 * @param {string} props.name Block's name.
 * @param {string} props.url URL.
 * @return {ReactElement} Placeholder.
 */
const MediaPlaceholder = ( { name, url } ) => {
	return (
		<Placeholder label={ name }>
			<p className="components-placeholder__error">
				{ url }
			</p>
			<p className="components-placeholder__error">
				{ __( 'Previews for this are unavailable in the editor, sorry!', 'amp' ) }
			</p>
		</Placeholder>
	);
};

MediaPlaceholder.propTypes = {
	name: PropTypes.string.isRequired,
	url: PropTypes.string,
};

export default MediaPlaceholder;
