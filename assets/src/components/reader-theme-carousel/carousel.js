/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useState, useRef, useEffect, useCallback } from '@wordpress/element';
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ThemeCard } from '../theme-card';

export function Carousel( { availableThemes, currentTheme, hideCurrentlyActiveTheme = true, selectedTheme } ) {
	const [ index, setIndex ] = useState( 0 );
	const [ cardwidth, setCardwidth ] = useState( null );

	const carousel = useRef();
	const carouselItems = useRef();

	// The carousel has an empty slide at the beginning and the end.
	const slideCount = availableThemes.length + 2;

	console.log( carouselItems );

	const scrollToItem = useCallback( ( newIndex ) => {
		carousel.current.scrollTo( { top: 0, left: carouselItems.current[ newIndex ].offsetLeft, behavior: 'smooth' } );
	}, [] );

	/**
	 * On component mount, find all the theme cards.
	 */
	useEffect( () => {
		carouselItems.current = [ ...carousel.current.querySelectorAll( '.theme-card' ) ];
	}, [] );

	/**
	 * Find the width of each card. The CSS for the component must ensure slides are equal width.
	 */
	useEffect( () => {
		setCardwidth( carousel.current.scrollWidth / slideCount );
	}, [ slideCount ] );

	useEffect( () => {
		const newIndex = availableThemes.findIndex( ( theme ) => theme.name === selectedTheme.name ) || 0;

		if ( carouselItems.current && newIndex in carouselItems.current ) {
			scrollToItem( newIndex );
		}
	}, [ availableThemes, scrollToItem, selectedTheme.name ] );

	/**
	 * Respond to user scrolls by setting the new index.
	 */
	useEffect( () => {
		if ( ! carousel.current ) {
			return () => null;
		}

		const currentContainer = carousel.current;

		const scrollCallback = () => {
			const newIndex = Math.floor( currentContainer.scrollLeft / cardwidth );
			if ( newIndex !== index ) {
				setIndex( newIndex );
			}
		};
		currentContainer.addEventListener( 'scroll', scrollCallback );

		return () => {
			currentContainer.removeEventListener( 'scroll', scrollCallback );
		};
	}, [ cardwidth, index, setIndex ] );

	return (
		<div>
			<div className="reader-theme-carousel">
				<ul className="reader-theme-carousel__carousel" ref={ carousel }>
					<div className="theme-card" />
					{ availableThemes.map( ( theme ) => {
						const disabled = hideCurrentlyActiveTheme && currentTheme.name === theme.name;

						return ! disabled && (
							<ThemeCard
								key={ `theme-card-${ theme.slug }` }
								screenshotUrl={ theme.screenshot_url }
								{ ...theme }
							/>
						);
					} ) }
					<div className="theme-card" />
				</ul>
			</div>

			{ 3 < availableThemes.length && (
				<div className="reader-theme-carousel__nav-container">
					<div className="reader-theme-carousel__nav">
						<Button
							isPrimary
							disabled={ 0 === index }
							onClick={ () => {
								scrollToItem( index - 1 );
							} }
						>
							<span className="components-visually-hidden">
								{ __( 'Previous', 'amp' ) }
							</span>
							<svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M5.47729 1.19531L1.18289 5.48906L5.47729 9.78347" stroke="#FAFAFC" strokeWidth="2" strokeLinejoin="round" />
								<path d="M1.15854 5.48828L10.281 5.48828" stroke="#FAFAFC" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
							</svg>

						</Button>
						<div className="reader-theme-carousel__dots">
							{ availableThemes.map( ( theme, i ) => (
								<Button
									key={ `available-themes-dot-${ theme.name }` }
									className={
										`reader-theme-carousel__nav-dot-button ${
											theme.name === selectedTheme.name ? 'reader-theme-carousel__nav-dot-button--active' : '' } ${
											index === i ? 'reader-theme-carousel__nav-dot-button--current' : '' }`
									}
									onClick={ () => {
										scrollToItem( i );
									} }
								>
									<span className="components-visually-hidden">
										{ theme.name }
									</span>
									<span className="reader-theme-carousel__nav-dot" />
								</Button>
							) ) }
						</div>
						<Button
							disabled={ slideCount - 4 < index }
							isPrimary
							onClick={ () => {
								scrollToItem( index + 1 );
							} }
						>
							<span className="components-visually-hidden">
								{ __( 'Next', 'amp' ) }
							</span>
							<svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg">
								<path d="M5.95255 1.19531L10.247 5.48906L5.95255 9.78347" stroke="#FAFAFC" strokeWidth="2" strokeLinejoin="round" />
								<path d="M10.2712 5.48828L1.14868 5.48828" stroke="#FAFAFC" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
							</svg>

						</Button>
					</div>
				</div>
			) }
		</div>
	);
}

const themeShape = PropTypes.shape( {
	name: PropTypes.string,
} );

Carousel.propTypes = {
	availableThemes: PropTypes.arrayOf( themeShape ).isRequired,
	currentTheme: themeShape.isRequired,
	hideCurrentlyActiveTheme: PropTypes.bool,
	selectedTheme: themeShape,
};
