/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useRef, useEffect, useState, useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useWindowWidth } from '../../utils/use-window-width';
import { DotNav } from './dot-nav';

const DEFAULT_GUTTER_WIDTH = 60;
const DEFAULT_ITEM_WIDTH = 268;
const MOBILE_BREAKPOINT = 783;

/**
 * Renders a scrollable carousel with a button navigation.
 *
 * @param {Object} props Component props.
 * @param {number} props.gutterWidth Amount of space between items in pixels.
 * @param {Array} props.items Items in the carousel.
 * @param {number} props.itemWidth The width of each item.
 * @param {number} props.mobileBreakpoint Breakpoint below which to render the mobile version.
 * @param {string} props.namespace CSS namespace.
 */
export function Carousel( {
	gutterWidth = DEFAULT_GUTTER_WIDTH,
	items,
	itemWidth = DEFAULT_ITEM_WIDTH,
	mobileBreakpoint = MOBILE_BREAKPOINT,
	namespace = 'amp-carousel',
} ) {
	const width = useWindowWidth();
	const [ activeItemIndex, setActiveItemIndex ] = useState( 0 );

	const carouselList = useRef();
	const carouselItems = useRef();

	/**
	 * Scrolls to the carousel item at the given index.
	 */
	const scrollToItem = useCallback( ( newIndex ) => {
		carouselList.current.scrollTo( {
			top: 0,
			left: carouselItems.current[ newIndex ].offsetLeft - ( width > mobileBreakpoint ? itemWidth : 0 ),
			behavior: 'smooth',
		} );
	}, [ itemWidth, mobileBreakpoint, width ] );

	/**
	 * On component mount, find all the theme cards.
	 */
	useEffect( () => {
		carouselItems.current = [ ...carouselList.current.querySelectorAll( `.${ namespace }__item` ) ];
	}, [ namespace ] );

	/**
	 * Respond to user scrolls by setting the new index.
	 */
	useEffect( () => {
		if ( ! carouselList.current ) {
			return () => null;
		}

		const currentContainer = carouselList.current;

		const scrollCallback = () => {
			const newIndex = Math.floor( currentContainer.scrollLeft / itemWidth );
			if ( newIndex < items.length ) {
				setActiveItemIndex( newIndex );
			}
		};
		currentContainer.addEventListener( 'scroll', scrollCallback );

		return () => {
			currentContainer.removeEventListener( 'scroll', scrollCallback );
		};
	}, [ items.length, itemWidth, scrollToItem ] );

	return (
		<div className={ namespace }>
			<div className={ `${ namespace }__container` }>
				<ul className={ `${ namespace }__carousel` } ref={ carouselList }>
					{ width > mobileBreakpoint && <li className={ `${ namespace }__item` } /> }
					{ items.map( ( { name, Item } ) => (
						<li className={ `${ namespace }__item` } key={ `${ namespace }-item-${ name }` }>
							<Item />
						</li>
					) ) }
					{ width > mobileBreakpoint && <li className={ `${ namespace }__item` } /> }
				</ul>
			</div>
			<DotNav { ...{ activeItemIndex, items, namespace, scrollToItem } } />
			<Style gutterWidth={ gutterWidth } itemWidth={ itemWidth } namespace={ namespace } />
		</div>
	);
}
Carousel.propTypes = {
	gutterWidth: PropTypes.number,
	items: PropTypes.array.isRequired,
	itemWidth: PropTypes.number,
	mobileBreakpoint: PropTypes.number,
	namespace: PropTypes.string,
};

/**
 * Styles for the carousel component, rendered as a string in JSX to facilitate dynamic rules.
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
	display: flex;
	overflow-x: scroll;
	-ms-overflow-style: none;
	scrollbar-width: none;
	scroll-snap-type: x mandatory;
}

.${ namespace }__carousel::-webkit-scrollbar {
	display: none;
}

.${ namespace }__carousel > li {
	margin-left: ${ gutterWidth / 2 }px;
	margin-right: ${ gutterWidth / 2 }px;
}

.${ namespace }__item {
	flex-shrink: 0;
	scroll-snap-align: start;
	width: ${ itemWidth }px;
}

.${ namespace }__nav {
	display: flex;
	justify-content: center;
	padding: 3rem 1.5rem;
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
	padding: 0 10px;
}

.${ namespace }__nav .${ namespace }__nav-dot-button {
	display: flex;
	align-items: center;
	justify-content: center;
	padding: 0;
	width: 20px;
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
