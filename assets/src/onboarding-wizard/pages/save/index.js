/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect } from '@wordpress/element';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { User } from '../../../components/user-context-provider';
import { Phone } from '../../../components/phone';
import './style.css';
import { ReaderThemes } from '../../../components/reader-themes-context-provider';
import { AMPNotice, NOTICE_SIZE_LARGE, NOTICE_TYPE_SUCCESS, NOTICE_TYPE_INFO } from '../../../components/amp-notice';
import { Navigation } from '../../components/navigation-context-provider';
import { Options } from '../../../components/options-context-provider';
import { Done } from '../../../components/svg/done';

/**
 * Provides the description for the done screen.
 *
 * @param {string} mode The selected mode.
 * @return {string} The text.
 */
function getDescription( mode ) {
	switch ( mode ) {
		case 'standard':
			return __( 'Your site is ready to serve AMP pages to your users! In Standard mode (AMP-first) all canonical URLs are AMP by default. You can still opt out of AMP for specific content types and templates from the AMP settings screen. Depending on the theme and plugins you are using, development work may be required to maintain your site’s AMP compatibility.', 'amp' );

		case 'transitional':
			return __( 'Your site is ready to serve AMP pages to your users! In Transitional mode both the AMP and non-AMP versions of your site will be served using your currently active theme. With further development work to address AMP-compatibility issues in your themes and plugins, your site can be made fully AMP-first.', 'amp' );

		case 'reader':
			return __( 'Your site is ready to serve AMP pages to your users! In Reader mode the AMP version of your site will be served using the Reader theme you have selected (shown to the right), while pages for the non-AMP version of your site will be served using your primary theme. As a last step, make sure you tailor the Reader theme as needed using the Customizer.', 'amp' );
		default:
			return '';
	}
}

/**
 * UI for a saving state.
 */
function Saving() {
	return (
		<div className="saving">
			<svg width="285" height="138" viewBox="0 0 285 138" fill="none" xmlns="http://www.w3.org/2000/svg">
				<path d="M95.1831 136.785C95.1831 136.785 129.883 102.785 204.483 119.185" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M117.883 123.285C117.883 123.285 73.9833 98.8854 34.3833 113.985" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M177.583 127.785C168.583 123.285 160.383 125.985 160.683 126.685" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M49.1836 109.788C47.4836 109.188 46.4836 106.988 47.2836 105.288C47.9836 103.588 50.2836 102.788 51.8836 103.688C51.6836 102.488 52.4836 101.288 53.5836 100.888C54.6836 100.488 56.0836 100.888 56.7836 101.788C57.4836 99.7882 59.6836 98.4882 61.8836 98.6882C63.4836 98.8882 64.8836 99.8882 65.5836 101.388C66.4836 100.488 68.1836 100.488 69.0836 101.488C69.9836 102.388 69.8836 104.088 68.8836 104.988C70.0836 104.688 71.3836 105.188 71.9836 106.188C72.5836 107.188 72.6836 108.588 71.9836 109.588" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M51.4828 120.886C52.7828 120.586 54.1828 120.386 55.3828 119.786C56.3828 119.286 57.2828 118.186 56.1828 117.286C55.4828 116.686 53.6828 116.786 52.7828 116.786C51.1828 116.686 49.5828 116.586 48.0828 117.286C47.0828 117.786 43.6828 120.186 44.0828 121.486C44.6828 122.986 50.2828 121.086 51.4828 120.886Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M68.6833 115.885C67.9833 115.685 67.1833 115.485 66.4833 115.585C63.9833 115.685 61.9833 117.685 64.4833 119.385C66.0833 120.485 68.3833 120.585 70.1833 120.785C71.2833 120.985 72.6833 120.885 73.1833 119.885C73.4833 119.185 73.1833 118.285 72.6833 117.785C72.1833 117.185 71.3833 116.885 70.6833 116.685C70.0833 116.285 69.3833 116.085 68.6833 115.885Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M246.827 89.9262C247.727 89.7262 248.627 89.4262 249.327 88.6262C250.427 87.5262 251.227 85.9262 250.527 84.3262C250.027 83.0262 248.327 80.9262 244.527 81.2262C243.727 81.3262 242.627 77.8262 242.127 77.2262C240.827 75.6262 239.027 74.7262 237.027 74.3262C236.027 74.1262 234.927 74.1262 234.027 74.5262C233.627 74.7262 231.827 76.0262 232.127 76.6262C232.127 76.6262 227.727 69.1262 221.527 75.5262C220.027 77.3262 219.527 78.7262 219.527 78.7262C219.527 78.7262 213.827 77.4262 212.627 82.0262C212.127 84.4262 211.127 89.2262 218.527 89.6262C223.727 89.6262 228.927 89.7262 234.127 89.8262C237.727 89.9262 241.327 90.4262 245.027 90.0262C245.627 90.1262 246.227 90.0262 246.827 89.9262Z" fill="white" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M235.629 109.054H254.729C255.929 107.454 255.529 104.854 254.029 103.554C252.529 102.254 250.029 102.154 248.629 103.554C248.729 101.454 247.329 99.454 245.329 98.854C243.329 98.254 241.029 99.154 239.929 100.854C237.429 99.154 233.529 100.154 232.229 102.954C230.829 105.654 232.629 108.254 235.629 109.054Z" fill="white" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M275.75 56.9414L277.85 62.8414L283.75 64.9414L277.85 67.0414L275.75 72.9414L273.65 67.0414L267.75 64.9414L273.65 62.8414L275.75 56.9414Z" fill="white" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M34.3833 64.9414L36.4833 70.8414L42.3833 72.9414L36.4833 75.0414L34.3833 80.9414L32.2833 75.0414L26.3833 72.9414L32.2833 70.8414L34.3833 64.9414Z" fill="white" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M10.4722 81.5586L12.8722 88.1586L19.4722 90.5586L12.8722 92.9586L10.4722 99.5586L8.07217 92.9586L1.47217 90.5586L8.07217 88.1586L10.4722 81.5586Z" fill="white" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" strokeLinecap="round" strokeLinejoin="round" />
				<path d="M144.095 75.0624L145.303 84.4721L151.979 86.8034L156.709 93.8481L155.359 100.717L164.592 105.589L169.674 100.473L178.103 99.0727L184.393 103.366L191.437 98.6364L189.651 92.1705L194.524 82.9369L201.182 80.1693L199.782 71.7409L193.106 69.4095L187.395 62.172L188.745 55.3034L180.493 50.6238L175.218 56.7209L166.982 57.1403L159.711 52.6535L153.84 56.5953L155.434 64.0425L150.561 73.276L144.095 75.0624Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
				<path d="M170.614 87.9204C176.033 88.9857 181.29 85.4562 182.355 80.037C183.42 74.6179 179.891 69.3612 174.471 68.296C169.052 67.2307 163.796 70.7603 162.73 76.1794C161.665 81.5986 165.195 86.8552 170.614 87.9204Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
				<path d="M138.394 47.0394L148.416 53.086L146.101 64.8607L134.536 66.6639L131.609 71.1842L134.394 82.9421L123.827 90.0372L113.998 83.0094L108.706 84.0074L103.834 93.241L91.0776 90.7336L90.0628 80.3427L84.7541 76.2417L74.9587 79.4119L67.8636 68.845L73.7173 59.8043L72.7192 54.5124L62.6973 48.4659L65.0119 36.6912L76.5768 34.888L79.6966 29.3864L76.7193 18.6098L87.0933 12.4959L96.9223 19.5237L102.407 17.5445L107.28 8.31088L120.035 10.8183L122.032 21.4021L126.359 25.3101L137.136 22.3328L144.424 31.9185L137.589 40.7664L138.394 47.0394Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
				<path d="M103.628 60.5884C109.047 61.6537 114.304 58.1241 115.369 52.705C116.434 47.2858 112.905 42.0292 107.486 40.964C102.066 39.8987 96.8098 43.4282 95.7445 48.8474C94.6793 54.2665 98.2088 59.5232 103.628 60.5884Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
				<path d="M101.718 70.3011C112.502 72.421 122.963 65.3972 125.083 54.6131C127.203 43.829 120.179 33.3683 109.395 31.2485C98.6109 29.1286 88.1502 36.1524 86.0303 46.9365C83.9105 57.7206 90.9342 68.1813 101.718 70.3011Z" stroke="#2459E7" strokeWidth="2" strokeMiterlimit="10" />
			</svg>

			<h1>
				{ __( 'Saving your settings …', 'amp' ) }
			</h1>
		</div>
	);
}

function Preview() {
	const {
		originalOptions: { preview_permalink: previewPermalink },
	} = useContext( Options );

	return (
		<>
			<Phone>
				<iframe
					className="done__preview-iframe"
					src={ previewPermalink }
					title={ __( 'Site preview', 'amp' ) }
					name="amp-wizard-completion-preview"
				/>
			</Phone>
			<div className="done__link-buttons">

				<Button
					isPrimary
					href={ previewPermalink }
					target="_blank"
					rel="noreferrer"
				>
					{ __( 'Browse AMP', 'amp' ) }
				</Button>

			</div>
		</>
	);
}

/**
 * Final screen, where data is saved.
 */
export function Save() {
	const {
		didSaveOptions,
		editedOptions: { theme_support: themeSupport, reader_theme: readerTheme },
		saveOptions,
		savingOptions,
	} = useContext( Options );
	const { didSaveDeveloperToolsOption, saveDeveloperToolsOption, savingDeveloperToolsOption } = useContext( User );
	const { canGoForward, setCanGoForward } = useContext( Navigation );
	const { downloadedTheme, downloadingTheme, downloadingThemeError } = useContext( ReaderThemes );

	/**
	 * Allow the finish button to be enabled.
	 */
	useEffect(
		() => {
			if ( ! canGoForward ) {
				setCanGoForward( true );
			}
		},
		[ setCanGoForward, canGoForward ],
	);

	/**
	 * Triggers saving of options on arrival to this screen.
	 */
	useEffect( () => {
		if ( ! didSaveOptions && ! savingOptions ) {
			saveOptions();
		}
	}, [ didSaveOptions, saveOptions, savingOptions ] );

	/**
	 * Triggers saving of user options on arrival of this screen.
	 */
	useEffect( () => {
		if ( ! didSaveDeveloperToolsOption && ! savingDeveloperToolsOption ) {
			saveDeveloperToolsOption();
		}
	}, [ didSaveDeveloperToolsOption, savingDeveloperToolsOption, saveDeveloperToolsOption ] );

	if ( savingOptions || savingDeveloperToolsOption || downloadingTheme ) {
		return <Saving />;
	}

	let heading = __( 'Congratulations!', 'amp' );
	if ( 'standard' === themeSupport ) {
		heading = __( 'Your site is ready', 'amp' );
	}

	return (
		<div className="done">
			<div className="done__text">
				<Done />
				<h1>
					{ heading }
				</h1>
				{
					'reader' === themeSupport && downloadedTheme === readerTheme && (
						<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_SUCCESS }>
							{ __( 'Your Reader theme was automatically installed', 'amp' ) }
						</AMPNotice>
					)
				}
				<p>
					{ getDescription( themeSupport ) }
				</p>
			</div>
			<div className="done__preview-container">
				{ 'reader' === themeSupport && downloadingThemeError && (
					<AMPNotice size={ NOTICE_SIZE_LARGE } type={ NOTICE_TYPE_INFO }>
						{ __( 'There was an error downloading your Reader theme. As a result, your site is currently using the legacy reader theme. Please install your chosen theme manually.', 'amp' ) }
					</AMPNotice>
				) }
				<Preview />

			</div>
		</div>
	);
}
