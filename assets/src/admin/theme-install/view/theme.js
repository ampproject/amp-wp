/**
 * External dependencies
 */
import { AMP_THEMES, AMP_COMPATIBLE, NONE_WPORG_THEMES } from 'amp-themes'; // From WP inline script.

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

const wpThemeView = wp.themes.view.Theme;

export default wpThemeView.extend( {

	/**
	 * Check if "AMP Compatible" tab is open or not.
	 */
	isAmpCompatibleTab() {
		const queryParams = new URLSearchParams( window.location.search.substr( 1 ) );
		return queryParams.get( 'browse' ) === AMP_COMPATIBLE;
	},

	/**
	 * Render theme card.
	 *
	 * @param {...any} args Render arguments.
	 */
	render( ...args ) {
		wpThemeView.prototype.render.apply( this, args );

		if ( 0 >= this.$el?.length || ! this.$el[ 0 ] ) {
			return;
		}

		const element = this.$el[ 0 ];

		const data = this.model.toJSON();
		let slug = data?.slug;

		if ( ! slug ) {
			slug = data?.id;
		}

		if ( slug && this.isAmpTheme( slug ) ) {
			/*
			 * Note: the setTimeout is needed because when the user taps on the AMP Compatible tab, the UI will render
			 * before history.pushState() is called, meaning isAmpCompatibleTab cannot be called yet to inspect the
			 * current location. By waiting for the next tick, we can safely read it.
			 */
			setTimeout( () => {
				if ( this.isAmpCompatibleTab() ) {
					return;
				}

				const messageElement = document.createElement( 'div' );
				const iconElement = document.createElement( 'span' );
				const tooltipElement = document.createElement( 'span' );

				messageElement.classList.add( 'amp-extension-card-message' );
				iconElement.classList.add( 'amp-logo-icon' );
				tooltipElement.classList.add( 'tooltiptext' );

				tooltipElement.append(
					__( 'This is known to work well with the AMP plugin.', 'amp' ),
				);

				messageElement.append( iconElement, tooltipElement );

				element.appendChild( messageElement );
			} );
		}

		if ( slug && ! this.isWPORGTheme( slug ) ) {
			const externalLinkIcon = document.createElement( 'span' );
			externalLinkIcon.classList.add( 'dashicons', 'dashicons-external' );
			externalLinkIcon.setAttribute( 'aria-hidden', 'true' );

			const screenReaderText = document.createElement( 'span' );
			screenReaderText.classList.add( 'screen-reader-text' );
			screenReaderText.append( __( '(opens in a new tab)', 'amp' ) );

			const siteLinkButton = document.createElement( 'a' );
			siteLinkButton.classList.add( 'button', 'button-primary' );
			siteLinkButton.append( __( 'Visit Site', 'amp' ), screenReaderText, externalLinkIcon );

			if ( data?.preview_url ) {
				siteLinkButton.href = data.preview_url;
			} else {
				siteLinkButton.href = data.homepage;
			}

			siteLinkButton.target = '_blank';
			siteLinkButton.rel = 'noopener noreferrer';
			siteLinkButton.setAttribute( 'aria-label', sprintf(
				/* translators: %s: theme name. */
				__( 'Visit site of %s theme', 'amp' ),
				data.name,
			) );

			const themeActions = element.querySelector( '.theme-actions' );
			if ( themeActions ) {
				themeActions.textContent = ''; // Remove children.
				themeActions.append( siteLinkButton );
			}

			const moreDetail = element.querySelector( '.more-details' );
			if ( moreDetail ) {
				moreDetail.textContent = ''; // Remove children.
				moreDetail.append( __( 'Visit Site', 'amp' ), externalLinkIcon.cloneNode( true ) );
			}
		}
	},

	/**
	 * Prevent the preview of none WordPress org theme and redirect to theme site.
	 *
	 * @param {...any} args Preview arguments.
	 */
	preview( ...args ) {
		const data = this.model.toJSON();

		if ( this.isWPORGTheme( data.slug ) ) {
			wpThemeView.prototype.preview.apply( this, args );
		} else if ( data?.preview_url ) {
			window.open( data.preview_url, '_blank', 'noopener,noreferrer' );
		}
	},

	/**
	 * Check if a theme is AMP compatible or not.
	 *
	 * @param {string} slug Theme slug.
	 * @return {boolean} True if theme is AMP compatible, Otherwise False.
	 */
	isAmpTheme( slug ) {
		return AMP_THEMES.includes( slug );
	},

	/**
	 * Check if a theme is from WordPress org or not.
	 *
	 * @param {string} slug Theme slug.
	 * @return {boolean} True if theme is listed in WordPress org, Otherwise False.
	 */
	isWPORGTheme( slug ) {
		return ( ! NONE_WPORG_THEMES.includes( slug ) );
	},
} );
