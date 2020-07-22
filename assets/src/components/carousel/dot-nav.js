/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Navigation dot.
 *
 * @param {Object} props Component props.
 * @param {boolean} props.isCurrent Whether the dot is currently selected.
 * @param {string} props.label Button label.
 * @param {Function} props.onClick Click callback.
 * @param {boolean} props.isSelected Whether the current item is selected.
 */
function Dot( { isCurrent, isSelected, label, onClick } ) {
	return (
		<Button
			className={
				`amp-carousel__nav-dot-button ${
					isCurrent ? 'amp-carousel__nav-dot-button--current' : '' } ${
					isSelected ? 'amp-carousel__nav-dot-button--active' : '' }`
			}
			onClick={ onClick }
		>
			<span className="components-visually-hidden">
				{ label }
			</span>
			{ isCurrent && (
				<span className="components-visually-hidden">
					{ __( '(Current slide)', 'amp' ) }
				</span>
			) }
			<span className="amp-carousel__nav-dot" />
		</Button>
	);
}
Dot.propTypes = {
	isCurrent: PropTypes.bool.isRequired,
	isSelected: PropTypes.bool.isRequired,
	label: PropTypes.string.isRequired,
	onClick: PropTypes.func.isRequired,
};

/**
 * Dot navigation component.
 *
 * @param {Object} props Component props.
 * @param {number} props.currentItemIndex The index of the item currently prominent in the view.
 * @param {Array} props.items Items in the carousel.
 * @param {number} props.mobileBreakpoint The breakpoint below which to show a mobile view.
 * @param {Function} props.scrollToItem Callback to scroll to a given item.
 * @param {string} props.namespace CSS namespace.
 * @param {number} props.width Current window width.
 * @param {boolean} props.prevButtonDisabled Whether the previous button should be disabled.
 * @param {boolean} props.nextButtonDisabled Whether the next button should be disabled.
 * @param {number} props.selectedItemIndex Index of an item to highlight.
 */
export function DotNav( { prevButtonDisabled, nextButtonDisabled, currentItemIndex, items, mobileBreakpoint, namespace, scrollToItem, selectedItemIndex, width } ) {
	return (
		<div className={ `${ namespace }__nav` }>
			<Button
				isPrimary
				disabled={ prevButtonDisabled }
				onClick={ () => {
					scrollToItem( currentItemIndex - 1 );
				} }
				className={ `${ namespace }__prev` }
			>
				<span className="components-visually-hidden">
					{ __( 'Previous', 'amp' ) }
				</span>
				<svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M5.47729 1.19531L1.18289 5.48906L5.47729 9.78347" stroke="#FAFAFC" strokeWidth="2" strokeLinejoin="round" />
					<path d="M1.15854 5.48828L10.281 5.48828" stroke="#FAFAFC" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
				</svg>

			</Button>
			{ width > mobileBreakpoint && (
				<div className={ `${ namespace }__dots` }>
					{ items.map( ( { label, name }, itemIndex ) => (
						<Dot
							id={ `${ namespace }__${ name }` }
							key={ `${ namespace }__${ name }` }
							isCurrent={ currentItemIndex === itemIndex }
							isSelected={ itemIndex === selectedItemIndex }
							label={ label }
							onClick={ () => {
								scrollToItem( itemIndex );
							} }
						/>
					) ) }
				</div>
			) }
			<Button
				disabled={ nextButtonDisabled }
				isPrimary
				onClick={ () => {
					scrollToItem( currentItemIndex + 1 );
				} }
				className={ `${ namespace }__next` }
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
	);
}
DotNav.propTypes = {
	currentItemIndex: PropTypes.number.isRequired,
	items: PropTypes.array.isRequired,
	mobileBreakpoint: PropTypes.number.isRequired,
	namespace: PropTypes.string.isRequired,
	nextButtonDisabled: PropTypes.bool.isRequired,
	prevButtonDisabled: PropTypes.bool.isRequired,
	scrollToItem: PropTypes.func.isRequired,
	selectedItemIndex: PropTypes.number,
	width: PropTypes.number.isRequired,
};
