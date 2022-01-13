/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useContext, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SiteScan } from '../site-scan-context-provider';
import { getPluginSlugFromFile } from '../../common/helpers/get-plugin-slug-from-file';
import ClipboardButton from '../clipboard-button';

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

	/**
	 * Get list of source that belongs to current plugin/theme.
	 *
	 * @param {Array} sources List of source.
	 *
	 * @return {Array} List of source that of current plugin/theme.
	 */
	const getAllowedSources = ( sources ) => {
		return sources.filter( ( source ) => {
			return slug === getPluginSlugFromFile( source.name );
		} );
	};

	/**
	 * Get list of errors for current plugin/theme.
	 *
	 * @param {Array} validationErrors List of validation errors.
	 *
	 * @return {Array} List of validation errors for current plugin/theme.
	 */
	const getAllowedErrors = ( validationErrors ) => {
		const errors = [];

		for ( const validationError of validationErrors ) {
			const allowedSources = getAllowedSources( validationError.sources );
			if ( allowedSources && allowedSources.length ) {
				const error = {
					...validationError,
					sources: allowedSources,
				};

				errors.push( error );
			}
		}

		return errors;
	};

	const extensionScannableUrls = [];

	for ( const scannableUrl of scannableUrls ) {
		const validationErrors = scannableUrl.validation_errors;
		const allowedErrors = getAllowedErrors( validationErrors );

		if ( allowedErrors && allowedErrors.length ) {
			const item = {
				...scannableUrl,
				validation_errors: allowedErrors,
			};
			extensionScannableUrls.push( item );
		}
	}

	const jsonData = JSON.stringify( extensionScannableUrls, null, 4 );

	return (
		<>
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
		</>
	);
}

SiteScanSourcesDetail.propTypes = {
	slug: PropTypes.string.isRequired,
};
