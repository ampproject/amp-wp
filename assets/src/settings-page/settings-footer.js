/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useContext, useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Options } from '../components/options-context-provider';
import { ReaderThemes } from '../components/reader-themes-context-provider';
import { AMPNotice, NOTICE_TYPE_SUCCESS } from '../components/amp-notice';

/**
 * The bottom section of the settings page.
 */
export function SettingsFooter() {
	const { didSaveOptions, editedOptions, hasOptionsChanges, saveOptions, savingOptions } = useContext( Options );
	const { downloadingTheme } = useContext( ReaderThemes );
	const [ saved, setSaved ] = useState( false );

	const { reader_theme: readerTheme, theme_support: themeSupport } = editedOptions;

	/**
	 * Shows a saved notice on success.
	 */
	useEffect( () => {
		if ( true === didSaveOptions && ! downloadingTheme ) {
			setSaved( true );

			const timeout = setTimeout( () => [
				setSaved( false ),
			], 9000 );

			return () => {
				clearTimeout( timeout );
			};
		}

		return () => undefined;
	}, [ didSaveOptions, downloadingTheme ] );

	const disabled = ! themeSupport ||
		! hasOptionsChanges ||
		savingOptions ||
		didSaveOptions ||
		downloadingTheme ||
		( 'reader' === themeSupport && ! readerTheme );

	return (
		<section className="settings-footer">
			<Button isPrimary onClick={ saveOptions } disabled={ disabled } isBusy={ savingOptions || downloadingTheme }>
				{ __( 'Save changes', 'amp' ) }
				<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
					<path d="M43.16 10.18c-0.881-0.881-2.322-0.881-3.203 0s-0.881 2.322 0 3.203l16.335 16.335h-54.051c-1.281 0-2.242 1.041-2.242 2.242 0 1.281 0.961 2.322 2.242 2.322h54.051l-16.415 16.335c-0.881 0.881-0.881 2.322 0 3.203s2.322 0.881 3.203 0l20.259-20.259c0.881-0.881 0.881-2.322 0-3.203l-20.179-20.179z" />
				</svg>
			</Button>
			{ saved && (
				<AMPNotice className={ `amp-save-success-notice` } type={ NOTICE_TYPE_SUCCESS }>
					<p>
						{ __( 'Settings saved', 'amp' ) }
					</p>
				</AMPNotice>
			) }
		</section>
	);
}
