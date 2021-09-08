/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
/**
 * Internal dependencies
 */
import { AMPNotice, NOTICE_TYPE_ERROR, NOTICE_TYPE_SUCCESS } from '../components/amp-notice';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { ReaderThemes } from '../components/reader-themes-context-provider';
import { ErrorContext } from '../components/error-context-provider';
import { User } from '../components/user-context-provider';
import { READER } from '../common/constants';

/**
 * Renders an error notice.
 *
 * @param {Object} props              Component props.
 * @param {string} props.errorMessage Error message text.
 */
function ErrorNotice( { errorMessage } ) {
	return (
		<div className="amp-error-notice">
			<AMPNotice type={ NOTICE_TYPE_ERROR }>
				<p>
					<strong>
						{ __( 'Error:', 'amp' ) }
					</strong>
					{ ' ' }
					{ errorMessage }
				</p>
			</AMPNotice>
		</div>
	);
}
ErrorNotice.propTypes = {
	errorMessage: PropTypes.string,
};

/**
 * The bottom section of the settings page.
 */
export function SettingsFooter() {
	const { didSaveOptions, editedOptions, hasOptionsChanges, savingOptions } = useContext( Options );
	const { downloadingTheme } = useContext( ReaderThemes );
	const { error } = useContext( ErrorContext );
	const { didSaveDeveloperToolsOption, hasDeveloperToolsOptionChange, savingDeveloperToolsOption } = useContext( User );

	const { reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	const hasChanges = hasOptionsChanges || hasDeveloperToolsOptionChange;
	const isBusy = savingOptions || downloadingTheme || savingDeveloperToolsOption;
	const disabled = ! hasChanges || isBusy || ! themeSupport || ( READER === themeSupport && ! readerTheme );

	return (
		<section className="amp-settings-nav">
			<div className="amp-settings-nav__inner">
				<Button isPrimary disabled={ disabled } isBusy={ isBusy } type="submit">
					{ isBusy ? __( 'Saving', 'amp' ) : __( 'Save', 'amp' ) }
					<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
						<path d="M43.16 10.18c-0.881-0.881-2.322-0.881-3.203 0s-0.881 2.322 0 3.203l16.335 16.335h-54.051c-1.281 0-2.242 1.041-2.242 2.242 0 1.281 0.961 2.322 2.242 2.322h54.051l-16.415 16.335c-0.881 0.881-0.881 2.322 0 3.203s2.322 0.881 3.203 0l20.259-20.259c0.881-0.881 0.881-2.322 0-3.203l-20.179-20.179z" />
					</svg>
				</Button>
				{ error && <ErrorNotice errorMessage={ error.message || __( 'An error occurred. You might be offline or logged out.', 'amp' ) } /> }
				{ ! error && ! hasChanges && ! downloadingTheme && ( didSaveOptions || didSaveDeveloperToolsOption ) && (
					<AMPNotice
						className="amp-save-success-notice"
						type={ NOTICE_TYPE_SUCCESS }
					>
						<p>
							{ __( 'Saved', 'amp' ) }
						</p>
					</AMPNotice>
				) }
			</div>
		</section>
	);
}
