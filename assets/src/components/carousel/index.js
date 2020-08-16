/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { debounce } from 'lodash';

/**
 * WordPress dependencies
 */
import { useRef, useEffect, useState, useCallback, useLayoutEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useWindowWidth } from '../../utils/use-window-width';
import { CarouselNav } from './carousel-nav';

export const DEFAULT_GUTTER_WIDTH = 60;
export const DEFAULT_MOBILE_BREAKPOINT = 783;

/**
 * Renders a scrollable carousel with a button navigation.
 *
 * @param {Object} props Component props.
 * @param {number} props.gutterWidth Amount of space between items in pixels.
 * @param {Array}  props.items Items in the carousel.
 * @param {number} props.mobileBreakpoint Breakpoint below which to render the mobile version.
 * @param {string} props.namespace CSS namespace.
 * @param {number} props.highlightedItemIndex Index of an item receiving special visual treatment.
 */
export function Carousel( {
	gutterWidth = DEFAULT_GUTTER_WIDTH,
	items,
	mobileBreakpoint = DEFAULT_MOBILE_BREAKPOINT,
	namespace = 'amp-carousel',
	highlightedItemIndex = 0,
} ) {
	const { windowWidth } = useWindowWidth();
	const [ currentPage, originalSetCurrentPage ] = useState( null );
	const [ pageWidth, setPageWidth ] = useState( 0 );
	const carouselListRef = useRef();

	/**
	 * Sets the the currentPage state and optionally scrolls to it.
	 *
	 * This state-setting wrapper is required, as opposed to scrolling to items in an effect hook when they're set as current,
	 * because scroll listener below needs to set currentPage, but it does so only when the new currentPage is already centered
	 * in the view. Calling scrollTo in that situation would cause jerky scroll effects.
	 */
	const setCurrentPage = useCallback( ( newCurrentPage, scrollToItem = true, smooth = true ) => {
		originalSetCurrentPage( newCurrentPage );

		if ( newCurrentPage && scrollToItem ) {
			carouselListRef.current.scrollTo( {
				top: 0,
				left: newCurrentPage.offsetLeft,
				behavior: smooth ? 'smooth' : 'auto',
			} );
		}
	}, [] );

	/**
	 * Scroll to the highlighted item when it changes.
	 */
	useEffect( () => {
		const newCurrentPage = carouselListRef.current.children.item( highlightedItemIndex );

		setCurrentPage( newCurrentPage, true, false );
	}, [ highlightedItemIndex, pageWidth, setCurrentPage ] );

	/**
	 * Set up a scroll listener to set an item as the currentPage as it crosses the center of the view.
	 */
	useLayoutEffect( () => {
		let mounted = true;
		const currentCarouselList = carouselListRef.current;

		const scrollCallback = debounce( () => {
			if ( ! mounted ) {
				return;
			}
			for ( const child of [ ...currentCarouselList.children ] ) {
				if ( child.offsetLeft >= currentCarouselList.scrollLeft ) {
					if ( child !== currentPage ) {
						setCurrentPage( child, false );
					}
					return;
				}
			}
		}, 300 );
		currentCarouselList.addEventListener( 'scroll', scrollCallback, { passive: true } );

		return () => {
			mounted = false;
			currentCarouselList.removeEventListener( 'scroll', scrollCallback );
		};
	}, [ currentPage, setCurrentPage ] );

	/**
	 * Update page width.
	 */
	useEffect( () => {
		setPageWidth( carouselListRef?.current?.clientWidth || 0 );
	}, [ items.length, windowWidth ] );

	const centeredItemIndex = [ ...( carouselListRef.current?.children || [] ) ].indexOf( currentPage );
	const nextButtonDisabled = centeredItemIndex >= items.length - 1;
	const prevButtonDisabled = centeredItemIndex <= 0;

	return (
		<div className={ namespace }>
			<div className={ `${ namespace }__container` }>
				<ul className={ `${ namespace }__carousel` } ref={ carouselListRef }>
					{ items.map( ( { label, name, Item } ) => (
						<li
							className={ `${ namespace }__item` }
							data-label={ label }
							id={ `${ namespace }-item-${ name }` }
							key={ `${ namespace }-item-${ name }` }
							tabIndex={ -1 }
						>
							<Item />
						</li>
					) ) }
				</ul>
			</div>
			{ currentPage && (
				<CarouselNav
					currentPage={ currentPage }
					centeredItemIndex={ centeredItemIndex }
					items={ carouselListRef?.current?.children }
					namespace={ namespace }
					nextButtonDisabled={ nextButtonDisabled }
					prevButtonDisabled={ prevButtonDisabled }
					setCurrentPage={ setCurrentPage }
					highlightedItemIndex={ highlightedItemIndex }
					showDots={ mobileBreakpoint < windowWidth }
				/>
			) }
			<Style
				gutterWidth={ gutterWidth }
				itemWidth={ pageWidth }
				namespace={ namespace }
			/>
		</div>
	);
}
Carousel.propTypes = {
	gutterWidth: PropTypes.number,
	items: PropTypes.array.isRequired,
	mobileBreakpoint: PropTypes.number,
	namespace: PropTypes.string,
	highlightedItemIndex: PropTypes.number,
};

/**
 * Styles for the carousel component, rendered as a string in JSX to facilitate dynamic rules.
 *
 * @todo Installing a styled components library would provide better tooling for this.
 *
 * @param {Object} props Component props.
 * @param {number} props.gutterWidth The amount of space between items.
 * @param {number} props.itemWidth The width of items.
 * @param {string} props.namespace CSS property namespace.
 */
function Style( { gutterWidth, itemWidth, namespace } ) {
	return (
		<style>
			{
				`

.${ namespace }__carousel {
	position: relative;
	display: grid;
	gap: ${ gutterWidth }px;
	grid-auto-flow: column;
	overflow-x: scroll;
	-ms-overflow-style: none;
	padding: 1rem 0;
	scrollbar-width: none;
	scroll-snap-type: x mandatory;
}

.${ namespace }__carousel::-webkit-scrollbar {
	display: none;
}

.${ namespace }__item {
	flex-shrink: 0;
	scroll-snap-align: center;
	width: ${ itemWidth }px;
}

.${ namespace }__item:focus,
.${ namespace }__item:focus > * {
	outline: none;
}

.${ namespace }__nav {
	display: flex;
	justify-content: center;
	padding: 1.5rem;
}

.${ namespace }__nav .components-button.is-primary svg {
	display: block;
	margin-left: 0;
	margin-right: 0;
}

.${ namespace }__nav .components-button.is-primary svg path {
	fill: transparent;
}

.${ namespace }__nav .components-button.is-primary {
	padding: 7px;
}

.${ namespace }__dots {
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
}

.${ namespace }__nav .${ namespace }__nav-dot-button {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 0;
	width: 20px;
}

.${ namespace }__prev {
	margin-right: 10px;
}

.${ namespace }__next {
	margin-left: 10px;
}

.${ namespace }__nav-dot-button .${ namespace }__nav-dot {
	background-color: #c4c4c4;
	border-radius: 5px;
	border: 2px solid #c4c4c4;
	flex-shrink: 0;
	height: 10px;
	padding: 0;
	transition: .2s all;
	width: 10px;
}

.${ namespace }__item-counter {
	align-items: center;
	display: flex;
	font-size: 14px;
	font-variant-numeric: tabular-nums;
	padding: 0 0.5rem;
}

.${ namespace }__item-counter span:first-of-type {
	display: flex;
	font-size: 20px;
}

.${ namespace }__item-counter span:first-of-type:after {
	border-left: 2px solid var(--color-gray-medium);
	content: '';
	margin: -0.5rem 0.5rem;
}

.${ namespace }__nav-dot-button--active .${ namespace }__nav-dot {
	background-color: var(--amp-settings-color-brand);
	border-color: var(--amp-settings-color-brand);
}

` }
		</style>
	);
}
Style.propTypes = {
	gutterWidth: PropTypes.number.isRequired,
	itemWidth: PropTypes.number.isRequired,
	namespace: PropTypes.string.isRequired,
};
