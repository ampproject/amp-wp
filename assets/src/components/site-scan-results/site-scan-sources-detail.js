/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext, useState, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SiteScan } from '../site-scan-context-provider';
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';
import ClipboardButton from '../clipboard-button';

/**
 * Get list of source that belongs to current plugin/theme.
 *
 * @param {Array}  sources List of source.
 * @param {string} slug    Plugin/Theme slug
 *
 * @return {Array} List of source that of current plugin/theme.
 */
const getAllowedSources = ( sources, slug ) => {
	return sources.filter( ( source ) => {
		return slug === getPluginSlugFromFile( source.name );
	} );
};

/**
 * Get list of errors for current plugin/theme.
 *
 * @param {Array}  validationErrors List of validation errors.
 * @param {string} slug             Plugin/Theme slug
 *
 * @return {Array} List of validation errors for current plugin/theme.
 */
const getAllowedErrors = ( validationErrors, slug ) => {
	const errors = [];

	for ( const validationError of validationErrors ) {
		const sources = validationError.sources || [];
		const allowedSources = getAllowedSources( sources, slug );
		if ( allowedSources && 0 < allowedSources.length ) {
			const error = {
				...validationError,
				sources: allowedSources,
			};

			errors.push( error );
		}
	}

	return errors;
};

/**
 * Site scan error source detail component.
 *
 * @param {Object} props      Component props.
 * @param {string} props.slug Slug of plugin or theme.
 */
export function SiteScanSourcesDetail( {
	slug,
} ) {
	const [ hasCopied, setHasCopied ] = useState( false );
	const { scannableUrls } = useContext( SiteScan );

	const jsonData = useMemo( () => {
		const extensionScannableUrls = scannableUrls.map( ( scannableUrl ) => {
			const validationErrors = scannableUrl.validation_errors || [];
			const allowedErrors = getAllowedErrors( validationErrors, slug );

			if ( allowedErrors?.length > 0 ) {
				return {
					...scannableUrl,
					validation_errors: allowedErrors,
				};
			}

			return null;
		} ).filter( Boolean );
		return JSON.stringify( extensionScannableUrls, null, 4 );
	}, [ scannableUrls, slug ] );

	return (
		<div className="site-scan-results__detail-body">
			<pre className="site-scan-results__source-detail">
				{ jsonData }
			</pre>
			<ClipboardButton
				isSmall={ true }
				text={ jsonData }
				onCopy={ () => setHasCopied( true ) }
				onFinishCopy={ () => setHasCopied( false ) }
			>
				{ hasCopied ? __( 'Copied!', 'amp' ) : __( 'Copy Validation Data', 'amp' ) }
			</ClipboardButton>
		</div>
	);
}

SiteScanSourcesDetail.propTypes = {
	slug: PropTypes.string.isRequired,
};
