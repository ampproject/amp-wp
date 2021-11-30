/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { addQueryArgs } from '@wordpress/url';
import { useContext } from '@wordpress/element';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { CUSTOMIZER_LINK, AMP_QUERY_VAR } from 'amp-settings'; // From WP inline script.

/**
 * Internal dependencies
 */
import { Navigation } from '../navigation-context-provider';
import './style.css';
import { Options } from '../../../components/options-context-provider';
import { User } from '../../../components/user-context-provider';
import { READER } from '../../../common/constants';
import { ReaderThemes } from '../../../components/reader-themes-context-provider';
import { SiteScan } from '../../../components/site-scan-context-provider';
import { useWindowWidth } from '../../../utils/use-window-width';

/**
 * Renders a link to leave the onboarding wizard.
 *
 * @param {Object} props           Component props.
 * @param {string} props.closeLink The link URL.
 */
export function CloseLink( { closeLink } ) {
	return (
		<Button isLink href={ closeLink }>
			<svg width="25" height="25" viewBox="0 0 25 25" fill="none" xmlns="http://www.w3.org/2000/svg">
				<mask id="close-icon" mask-type="alpha" maskUnits="userSpaceOnUse" x="3" y="3" width="19" height="19">
					<path fillRule="evenodd" clipRule="evenodd" d="M19.895 3.71875H5.89502C4.79502 3.71875 3.89502 4.61875 3.89502 5.71875V19.7188C3.89502 20.8188 4.79502 21.7188 5.89502 21.7188H19.895C21.005 21.7188 21.895 20.8188 21.895 19.7188V15.7188H19.895V19.7188H5.89502V5.71875H19.895V9.71875H21.895V5.71875C21.895 4.61875 21.005 3.71875 19.895 3.71875ZM13.395 17.7188L14.805 16.3088L12.225 13.7188H21.895V11.7188H12.225L14.805 9.12875L13.395 7.71875L8.39502 12.7188L13.395 17.7188Z" fill="white" />
				</mask>
				<g mask="url(#close-icon)">
					<rect width="24" height="24" transform="matrix(-1 0 0 1 24.895 0.71875)" fill="#2459E7" />
				</g>
			</svg>

			{ __( 'Close', 'amp' ) }
		</Button>
	);
}
CloseLink.propTypes = {
	closeLink: PropTypes.string.isRequired,
};

/**
 * Navigation component.
 *
 * @param {Object} props            Component props.
 * @param {string} props.closeLink  Link to return to previous user location.
 * @param {string} props.finishLink Link to exit the application.
 */
export function Nav( { closeLink, finishLink } ) {
	const { isMobile } = useWindowWidth();
	const { activePageIndex, canGoForward, isLastPage, moveBack, moveForward } = useContext( Navigation );
	const {
		savingOptions,
		editedOptions: { theme_support: themeSupport },
		originalOptions: { preview_permalink: previewPermalink, reader_theme: readerTheme },
	} = useContext( Options );
	const { savingDeveloperToolsOption } = useContext( User );
	const { downloadingTheme } = useContext( ReaderThemes );
	const { isBusy } = useContext( SiteScan );

	let nextText;
	let nextLink;

	if ( isLastPage && READER === themeSupport ) {
		nextText = __( 'Customize', 'amp' );
		nextLink = ! savingDeveloperToolsOption && ! savingOptions ? addQueryArgs(
			CUSTOMIZER_LINK,
			'legacy' === readerTheme
				? { 'autofocus[panel]': 'amp_panel', url: previewPermalink }
				: { url: previewPermalink, [ AMP_QUERY_VAR ]: '1' },
		) : undefined;
	} else if ( isLastPage ) {
		nextText = __( 'Finish', 'amp' );
		nextLink = ! savingDeveloperToolsOption && ! savingOptions ? finishLink : undefined;
	} else {
		nextText = __( 'Next', 'amp' );
		nextLink = undefined;
	}

	return (
		<div className="amp-settings-nav">
			<div className="amp-settings-nav__inner">
				<div className="amp-settings-nav__close">
					{ ! isMobile && ( ! isLastPage || READER === themeSupport ) && (
						<CloseLink closeLink={ closeLink } />
					) }
				</div>
				<div className="amp-settings-nav__prev-next">
					{ 1 > activePageIndex
						? (
							<span className="amp-settings-nav__placeholder">
								{ ' ' }
							</span>
						)
						: (
							<Button
								disabled={ isBusy }
								className="amp-settings-nav__prev"
								onClick={ moveBack }
							>
								<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
									<path d="M43.16 10.18c-0.881-0.881-2.322-0.881-3.203 0s-0.881 2.322 0 3.203l16.335 16.335h-54.051c-1.281 0-2.242 1.041-2.242 2.242 0 1.281 0.961 2.322 2.242 2.322h54.051l-16.415 16.335c-0.881 0.881-0.881 2.322 0 3.203s2.322 0.881 3.203 0l20.259-20.259c0.881-0.881 0.881-2.322 0-3.203l-20.179-20.179z" />
								</svg>

								{ __( 'Previous', 'amp' ) }
							</Button>
						)
					}

					<Button
						disabled={ ! canGoForward || savingOptions || savingDeveloperToolsOption || downloadingTheme }
						href={ nextLink }
						id="next-button"
						isPrimary
						onClick={ moveForward }
					>
						{ nextText }

						{ ! isLastPage && (
							<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64">
								<path d="M43.16 10.18c-0.881-0.881-2.322-0.881-3.203 0s-0.881 2.322 0 3.203l16.335 16.335h-54.051c-1.281 0-2.242 1.041-2.242 2.242 0 1.281 0.961 2.322 2.242 2.322h54.051l-16.415 16.335c-0.881 0.881-0.881 2.322 0 3.203s2.322 0.881 3.203 0l20.259-20.259c0.881-0.881 0.881-2.322 0-3.203l-20.179-20.179z" />
							</svg>
						) }

					</Button>

				</div>
			</div>
		</div>
	);
}

Nav.propTypes = {
	closeLink: PropTypes.string.isRequired,
	finishLink: PropTypes.string.isRequired,
};
