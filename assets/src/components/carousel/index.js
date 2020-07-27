/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useEffect, useState, useCallback, useLayoutEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useWindowWidth } from '../../utils/use-window-width';
import { CarouselNav } from './carousel-nav';

const DEFAULT_GUTTER_WIDTH = 60;
const DEFAULT_ITEM_WIDTH = 268;
const DEFAULT_MOBILE_BREAKPOINT = 783;

/**
 * Renders a scrollable carousel with a button navigation.
 *
 * @param {Object} props Component props.
 * @param {number} props.gutterWidth Amount of space between items in pixels.
 * @param {HTMLCollection} props.items Items in the carousel.
 * @param {number} props.itemWidth The width of each item.
 * @param {number} props.mobileBreakpoint Breakpoint below which to render the mobile version.
 * @param {string} props.namespace CSS namespace.
 * @param {number} props.highlightedItemIndex Index of an item receiving special visual treatment.
 */
export function Carousel( {
	gutterWidth = DEFAULT_GUTTER_WIDTH,
	items,
	itemWidth = DEFAULT_ITEM_WIDTH,
	mobileBreakpoint = DEFAULT_MOBILE_BREAKPOINT,
	namespace = 'amp-carousel',
	highlightedItemIndex = 0,
} ) {
	const windowWidth = useWindowWidth();
	const [ currentItem, originalSetCurrentItem ] = useState( null );
	const carouselContainerRef = useRef();
	const carouselListRef = useRef();

	/**
	 * Sets the the currentItem state and optionally scrolls to it.
	 *
	 * This state-setting wrapper is required, as opposed to scrolling to items in an effect hook when they're set as current,
	 * because intersection observer needs to set currentItem, but it does so only when the new currentItem is already centered
	 * in the view. Calling scrollTo in that situation would cause jerky scroll effects.
	 */
	const setCurrentItem = useCallback( ( newCurrentItem, scrollToItem = true ) => {
		originalSetCurrentItem( newCurrentItem );

		if ( newCurrentItem && scrollToItem ) {
			const left = newCurrentItem.offsetLeft - (
				windowWidth > mobileBreakpoint
					? ( newCurrentItem.offsetWidth + gutterWidth ) // Center the item on desktop. If this isn't exact, the scroll snap CSS rules will fix it.
					: 0
			);
			carouselListRef.current.scrollTo( { top: 0, left, behavior: 'smooth' } );
		}
	}, [ gutterWidth, mobileBreakpoint, windowWidth ] );

	/**
	 * Center the highlighted item. On initial load, this will center the previously selected theme. Subsequently,
	 * it will center a theme when the user clicks its label (e.g., if they click a theme that's off to the side).
	 */
	useEffect( () => {
		const item = carouselListRef.current.children.item( highlightedItemIndex );

		setCurrentItem( item );
	}, [ highlightedItemIndex, setCurrentItem ] );

	/**
	 * Set up an intersection observer to set an item as the currentItem as it crosses the center of the view.
	 */
	useLayoutEffect( () => {
		const observerCallback = ( [ { isIntersecting, target } ] ) => {
			if ( isIntersecting ) {
				setCurrentItem( target, false );
			}
		};

		const observer = new global.IntersectionObserver( observerCallback, {
			root: carouselContainerRef.current,
			rootMargin: '0px -50%', // Run the callback as an item crosses the center.
		} );

		[ ...carouselListRef.current.children ].forEach( ( element ) => {
			observer.observe( element );
		} );

		return () => {
			observer.disconnect();
		};
	}, [ currentItem, setCurrentItem ] );

	return (
		<div className={ namespace }>
			<div className={ `${ namespace }__container` } ref={ carouselContainerRef }>
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
			{ currentItem && (
				<CarouselNav
					currentItem={ currentItem }
					items={ carouselListRef?.current?.children }
					namespace={ namespace }
					setCurrentItem={ setCurrentItem }
					highlightedItemIndex={ highlightedItemIndex }
					showDots={ mobileBreakpoint < windowWidth }
				/>
			) }
			<Style
				gutterWidth={ gutterWidth }
				itemWidth={ itemWidth }
				namespace={ namespace }
			/>
		</div>
	);
}
Carousel.propTypes = {
	gutterWidth: PropTypes.number,
	items: PropTypes.array.isRequired,
	itemWidth: PropTypes.number,
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
 * @param {number} props.gutterWidth The amount of space betwen items.
 * @param {number} props.itemWidth The width of items.
 * @param {string} props.namespace CSS property namespace.
 */
function Style( { gutterWidth, itemWidth, namespace } ) {
	return (
		<style>
			{
				`

.${ namespace }__carousel {
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
	outline: 1px dotted var(--amp-settings-color-brand);
	outline-offset: -2px;
}

.${ namespace }__nav {
	display: flex;
	justify-content: center;
	padding: 1.5rem;
}

.${ namespace }__nav .components-button.is-primary svg {
	display: block;
	margin-left: 0;
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

.${ namespace }__nav-dot-button--active .${ namespace }__nav-dot {
	background-color: var(--amp-settings-color-brand);
	border-color: var(--amp-settings-color-brand);
}

.${ namespace }__nav-dot-button--current .${ namespace }__nav-dot {
	transform: scale3d(1.3, 1.3, 1.3);
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
