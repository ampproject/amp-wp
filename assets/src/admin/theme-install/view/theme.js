/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { AMP_THEMES, NONE_WPORG_THEMES } from 'amp-themes'; // From WP inline script.

const wpThemeView = wp.themes.view.Theme;

export default wpThemeView.extend( {

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

		if ( slug && this.isAMPTheme( slug ) ) {
			const messageElement = document.createElement( 'div' );
			const iconElement = document.createElement( 'span' );
			const tooltipElement = document.createElement( 'span' );

			messageElement.classList.add( 'extension-card-px-message' );
			iconElement.classList.add( 'amp-logo-icon' );
			tooltipElement.classList.add( 'tooltiptext' );

			tooltipElement.append(
				__( 'This theme follow best practice and is known to work well with AMP plugin.', 'amp' ),
			);

			messageElement.append( iconElement );
			messageElement.append( tooltipElement );
			messageElement.append( ' ' );
			messageElement.append( __( 'AMP Compatible', 'amp' ) );

			element.appendChild( messageElement );
		}

		if ( slug && ! this.isWPORGTheme( slug ) ) {
			const siteLinkButton = document.createElement( 'a' );
			siteLinkButton.classList.add( 'button' );
			siteLinkButton.classList.add( 'button-primary' );
			siteLinkButton.innerText = __( 'Visit Site', 'amp' );

			if ( data?.preview_url ) {
				siteLinkButton.setAttribute( 'href', data.preview_url );
			} else {
				siteLinkButton.setAttribute( 'href', data.homepage );
			}

			siteLinkButton.setAttribute( 'target', '_blank' );
			siteLinkButton.setAttribute( 'aria-label', sprintf(
				/* translators: %s: theme name. */
				__( 'Visit site of %s theme', 'amp' ),
				data.name,
			) );

			const themeActions = element.querySelector( '.theme-actions' );
			if ( themeActions ) {
				themeActions.innerHTML = '';
				themeActions.appendChild( siteLinkButton );
			}

			const moreDetail = element.querySelector( '.more-details' );
			if ( moreDetail ) {
				moreDetail.innerHTML = '';
				moreDetail.append( __( 'Visit site', 'amp' ) );
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
			window.open( data.preview_url, '_blank' );
		}
	},

	/**
	 * Check if theme is AMP compatible or not.
	 *
	 * @param {string} slug Theme slug.
	 * @return {boolean} True if theme is AMP compatible, Otherwise False.
	 */
	isAMPTheme( slug ) {
		return AMP_THEMES.includes( slug );
	},

	/**
	 * Check if theme is from WordPress org or not.
	 *
	 * @param {string} slug Theme slug.
	 * @return {boolean} True if theme is listed in WordPress org, Otherwise False.
	 */
	isWPORGTheme( slug ) {
		return ( ! NONE_WPORG_THEMES.includes( slug ) );
	},
} );
