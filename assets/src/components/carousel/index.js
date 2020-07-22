/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { debounce } from 'lodash';

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
const DEFAULT_MOBILE_BREAKPOINT = 783;

/**
 * Renders a scrollable carousel with a button navigation.
 *
 * @param {Object} props Component props.
 * @param {number} props.gutterWidth Amount of space between items in pixels.
 * @param {Array} props.items Items in the carousel.
 * @param {number} props.itemWidth The width of each item.
 * @param {number} props.mobileBreakpoint Breakpoint below which to render the mobile version.
 * @param {string} props.namespace CSS namespace.
 * @param {number} props.selectedItemIndex Index of an item to force into focus.
 */
export function Carousel( {
	gutterWidth = DEFAULT_GUTTER_WIDTH,
	items,
	itemWidth = DEFAULT_ITEM_WIDTH,
	mobileBreakpoint = DEFAULT_MOBILE_BREAKPOINT,
	namespace = 'amp-carousel',
	selectedItemIndex = 0,
} ) {
	const width = useWindowWidth();
	const [ currentItemIndex, setCurrentItemIndex ] = useState( selectedItemIndex );
	const [ prevButtonDisabled, setPrevButtonDisabled ] = useState( true );
	const [ nextButtonDisabled, setNextButtonDisabled ] = useState( true );

	const carouselList = useRef();
	const mounted = useRef( false );

	/**
	 * Set up ref to track whether the component is mounted, as there is a debounced callback in an effect below.
	 */
	useEffect( () => {
		mounted.current = true;
		return () => {
			mounted.current = false;
		};
	}, [] );

	/**
	 * Scrolls to the carousel item at the given index.
	 */
	const scrollToItem = useCallback( ( newIndex ) => {
		if ( ! ( newIndex in carouselList.current.children ) ) {
			return;
		}

		carouselList.current.scrollTo( {
			top: 0,
			left: carouselList.current.children[ newIndex + ( width > mobileBreakpoint ? 0 : 1 ) ].offsetLeft,
			behavior: 'smooth',
		} );

		carouselList.current.children[ newIndex ].focus( { preventScroll: true } );
	}, [ mobileBreakpoint, width ] );

	/**
	 * When an item is selected, center it.
	 */
	useEffect( () => {
		scrollToItem( selectedItemIndex );
	}, [ scrollToItem, selectedItemIndex ] );

	/**
	 * Respond to user scrolls by setting the new index.
	 */
	useEffect( () => {
		if ( ! carouselList.current ) {
			return () => null;
		}

		const currentContainer = carouselList.current;

		const scrollCallback = debounce( () => {
			if ( ! mounted.current ) {
				return;
			}

			const realItemWidth = currentContainer.scrollWidth / currentContainer.children.length;
			const newIndex = Math.floor( currentContainer.scrollLeft / realItemWidth ) - ( width > mobileBreakpoint ? 0 : 1 );

			if ( newIndex < items.length ) {
				setCurrentItemIndex( newIndex );
			}

			setPrevButtonDisabled( newIndex < 1 );
			setNextButtonDisabled( newIndex > items.length - 2 );
		}, 50 );
		currentContainer.addEventListener( 'scroll', scrollCallback );

		return () => {
			currentContainer.removeEventListener( 'scroll', scrollCallback );
		};
	}, [ items.length, itemWidth, mobileBreakpoint, scrollToItem, width ] );

	return (
		<div className={ namespace }>
			<div className={ `${ namespace }__container` }>
				<ul className={ `${ namespace }__carousel` } ref={ carouselList }>
					<li className={ `${ namespace }__item` } />
					{ items.map( ( { name, Item } ) => (
						<li className={ `${ namespace }__item` } key={ `${ namespace }-item-${ name }` } tabIndex={ -1 }>
							<Item />
						</li>
					) ) }
					<li className={ `${ namespace }__item` } />
				</ul>
			</div>
			<DotNav
				currentItemIndex={ currentItemIndex }
				items={ items }
				mobileBreakpoint={ mobileBreakpoint }
				namespace={ namespace }
				scrollToItem={ scrollToItem }
				width={ width }
				prevButtonDisabled={ prevButtonDisabled }
				nextButtonDisabled={ nextButtonDisabled }
				selectedItemIndex={ selectedItemIndex }
			/>
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
	selectedItemIndex: PropTypes.number,
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
	padding: 1rem 0;
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
	scroll-snap-align: center;
	width: ${ itemWidth }px;
}

.${ namespace }__item:focus,
.${ namespace }__item:focus > * {
	outline: 1px dotted #c4c4c4;
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
