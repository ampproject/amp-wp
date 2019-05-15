/**
 * WordPress dependencies
 */
import { Placeholder } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Display media placeholder.
 *
 * @param {string} name Block's name.
 * @param {string} url  URL.
 *
 * @return {Component} Placeholder.
 */
export default ( { name, url } ) => {
	return (
		<Placeholder label={ name }>
			<p className="components-placeholder__error">{ url }</p>
			<p className="components-placeholder__error">{ __( 'Previews for this are unavailable in the editor, sorry!', 'amp' ) }</p>
		</Placeholder>
	);
};
