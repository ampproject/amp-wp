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
import { SupportedTemplatesToggle } from '../components/supported-templates-toggle';
import { Options } from '../components/options-context-provider';
import { SupportedTemplatesVisibility } from './supported-templates-visibility';

/**
 * Supported templates section of the settings page.
 *
 * @param {Object} props Component props.
 * @param {Object} props.themeSupportArgs Theme support settings passed from the backend.
 */
export function SupportedTemplates( { themeSupportArgs } ) {
	const { fetchingOptions } = useContext( Options );

	if ( fetchingOptions ) {
		return null;
	}

	return (
		<>
			<SupportedTemplatesToggle themeSupportArgs={ themeSupportArgs } />
			<SupportedTemplatesVisibility />
		</>
	);
}

SupportedTemplates.propTypes = {
	themeSupportArgs: PropTypes.oneOfType( [
		PropTypes.bool,
		PropTypes.shape( {
			templates_supported: PropTypes.any,
		} ),
	] ).isRequired,
};
