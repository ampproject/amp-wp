/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect, useState, useRef } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import apiFetch from '@wordpress/api-fetch';

/**
 * External dependencies
 */
import { SITE_HOME } from 'amp-setup'; // From WP inline script.

/**
 * Internal dependencies
 */
import { Button } from '@wordpress/components';
import { Options } from '../../components/options-context-provider';
import { Loading } from '../../components/loading';
import { User } from '../../components/user-context-provider';
import { Phone } from '../../components/phone';
import './style.css';

/**
 * Provides the description for the done screen.
 *
 * @param {string} mode The selected mode.
 * @return {string} The text.
 */
function getDescription( mode ) {
	switch ( mode ) {
		case 'reader':
			return __( 'Your site is using Reader mode. AMP pages on your site will be served using the reader theme you have selected (shown to the right), while your site’s primary theme will still be used where pages are not served using AMP.', 'amp' );

		case 'standard':
			return __( 'Your site is using Standard mode, which means it is AMP-first, and all canonical URLs are AMP by default. You can still opt specific content types and templates out of AMP upon returning to the AMP settings screen. Depending on your theme and plugins, development work may be required to maintain your site’s AMP compatibility.', 'amp' );

		case 'transitional':
			return __( 'Your site is using Transitional mode, which means your current theme will be used to generate both AMP and non-AMP versions of all URLs on your site. With further development work to address AMP-compatibility issues in your themes and plugins, your site can be made fully AMP-first.', 'amp' );

		default:
			return '';
	}
}

/**
 * Final screen, where data is saved.
 */
export function Save() {
	const [ fetchingFirstPost, setFetchingFirstPost ] = useState( true );
	const [ firstPostPermalink, setFirstPostPermalink ] = useState();
	const [ firstPostPermalinkError, setFirstPostPermalinkError ] = useState( null );

	const { didSaveOptions, options, saveOptions, savingOptions } = useContext( Options );
	const { didSaveDeveloperToolsOption, saveDeveloperToolsOption, savingDeveloperToolsOption } = useContext( User );

	const { theme_support: themeSupport } = options;

	const hasUnmounted = useRef( false );

	useEffect( () => () => {
		hasUnmounted.current = true;
	}, [] );

	/**
	 * If reader mode was selected, AMP won't be active on the site homepage, so we fetch the most recent post link.
	 */
	useEffect( () => {
		if ( 'reader' !== themeSupport ) {
			setFetchingFirstPost( false );
			return;
		}

		( async () => {
			try {
				const posts = await apiFetch( { path: addQueryArgs( '/wp/v2/posts', { per_page: 1, _fields: 'link' } ) } );

				if ( true === hasUnmounted.current ) {
					return;
				}

				if ( ! posts.length ) {
					setFirstPostPermalinkError( __( 'No post found', 'amp' ) );
				} else {
					setFirstPostPermalink( posts[ 0 ].link );
				}
			} catch ( e ) {
				if ( true === hasUnmounted.current ) {
					return;
				}

				setFirstPostPermalinkError( e );
			}

			setFetchingFirstPost( false );
		} )();
	}, [ themeSupport ] );

	/**
	 * Triggers saving of options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! didSaveOptions && ! savingOptions ) {
			saveOptions();
		}
	}, [ didSaveOptions, saveOptions, savingOptions ] );

	/**
	 * Triggers saving of user options on arrival of this screen.
	 *
	 * @todo Possibly wait for a different user action to save.
	 */
	useEffect( () => {
		if ( ! didSaveDeveloperToolsOption && ! savingDeveloperToolsOption ) {
			saveDeveloperToolsOption();
		}
	}, [ didSaveDeveloperToolsOption, savingDeveloperToolsOption, saveDeveloperToolsOption ] );

	if ( savingOptions || savingDeveloperToolsOption ) {
		return <Loading />;
	}

	if ( fetchingFirstPost || ! didSaveOptions || ! didSaveDeveloperToolsOption ) {
		return null;
	}

	let heading = __( 'Congratulations!', 'amp' );
	if ( 'standard' === themeSupport ) {
		heading = __( 'Your site is ready', 'amp' );
	}

	const urlArgs = {
		amp: 1,
		'amp-no-admin-bar': 1,
	};

	return (
		<div className="done grid grid-5-4">
			<div>
				<h1>
					{ heading }
				</h1>
				<p>
					{ getDescription( themeSupport ) }
				</p>
			</div>
			<div className="done__preview-container">
				<p className="reader-summary__caption">
					<svg width="13" height="20" viewBox="0 0 13 20" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1.66211 0.265625H11.2246C11.5684 0.265625 11.8496 0.390625 12.0684 0.640625C12.3184 0.859375 12.4434 1.14063 12.4434 1.48438V18.2656C12.4434 18.6094 12.3184 18.9062 12.0684 19.1562C11.8496 19.375 11.5684 19.4844 11.2246 19.4844H1.66211C1.31836 19.4844 1.02148 19.375 0.771484 19.1562C0.552734 18.9062 0.443359 18.6094 0.443359 18.2656V1.48438C0.443359 1.14063 0.552734 0.859375 0.771484 0.640625C1.02148 0.390625 1.31836 0.265625 1.66211 0.265625ZM10.0527 14.6562V2.65625H2.83398V14.6562H10.0527ZM4.05273 3.875H8.83398L4.05273 9.875V3.875Z" fill="black" fillOpacity="0.87" />
					</svg>

					{ __( 'Live view of your site', 'amp' ) }
				</p>
				<Phone>
					{ firstPostPermalinkError && (
						<div>
							{ __( 'There was an error renering the preview', 'amp' ) }
						</div>
					)
					}
					{ ! firstPostPermalinkError && (
						<iframe
							className="done__preview-iframe"
							sandbox=""
							src={ addQueryArgs( firstPostPermalink || SITE_HOME, urlArgs ) }
							title={ __( 'Site preview', 'amp' ) }
						/>
					) }
				</Phone>

				<Button isPrimary href={ SITE_HOME } target="_blank" rel="noreferrer">
					{ __( 'Visit your site', 'amp' ) }
				</Button>
			</div>
		</div>
	);
}
