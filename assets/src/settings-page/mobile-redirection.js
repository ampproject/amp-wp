/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { RedirectToggle } from '../components/redirect-toggle';
import { Options } from '../components/options-context-provider';

/**
 * Mobile redirection section of the settings page.
 *
 * @param {Object} props    Component props.
 * @param {string} props.id Unique HTML ID.
 */
export function MobileRedirection( { id } ) {
	const { editedOptions } = useContext( Options );

	const { theme_support: themeSupport } = editedOptions || {};

	// Don't show if the mode is standard or the themeSupport is not yet set.
	if ( ! themeSupport || 'standard' === themeSupport ) {
		return null;
	}

	return (
		<section className="mobile-redirection" id={ id }>
			<RedirectToggle direction="left" />
		</section>
	);
}
MobileRedirection.propTypes = {
	id: PropTypes.string.isRequired,
};
