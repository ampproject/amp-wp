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
 * @param {boolean} props.isSelected Whether the current item is selected.
 * @param {string} props.id An HTML ID.
 * @param {string} props.label Button label.
 * @param {string} props.namespace CSS namespace.
 * @param {Function} props.onClick Click callback.
 */
function Dot( { id, isCurrent, isSelected, label, namespace, onClick } ) {
	return (
		<Button
			className={
				[
					`${ namespace }__nav-dot-button`,
					isCurrent ? `${ namespace }__nav-dot-button--current` : '',
					isSelected ? `${ namespace }__nav-dot-button--active` : '',
				]
					.filter( ( item ) => item )
					.join( ' ' )
			}
			id={ id }
			onClick={ onClick }
			aria-label={ label }
		>
			<span className="components-visually-hidden">
				{ label }
			</span>
			{ isCurrent && (
				<span className="components-visually-hidden">
					{ __( '(Current slide)', 'amp' ) }
				</span>
			) }
			{ isSelected && (
				<span className="components-visually-hidden">
					{ __( '(Selected item)', 'amp' ) }
				</span>
			) }
			<span className={ `${ namespace }__nav-dot` } />
		</Button>
	);
}
Dot.propTypes = {
	id: PropTypes.string.isRequired,
	isCurrent: PropTypes.bool.isRequired,
	isSelected: PropTypes.bool.isRequired,
	label: PropTypes.string.isRequired,
	namespace: PropTypes.string.isRequired,
	onClick: PropTypes.func.isRequired,
};

/**
 * Dot navigation component.
 *
 * @param {Object} props Component props.
 * @param {number} props.currentItem The current item.
 * @param {HTMLCollection} props.items Items in the carousel.
 * @param {string} props.namespace CSS namespace.
 * @param {Function} props.setCurrentItem Sets an item as the current item.
 * @param {number} props.highlightedItemIndex Index of an item to highlight.
 * @param {boolean} props.showDots Whether to show the dot navigation.
 */
export function CarouselNav( {
	currentItem,
	items,
	namespace,
	setCurrentItem,
	highlightedItemIndex,
	showDots,
} ) {
	return (
		<div className={ `${ namespace }__nav` }>
			<Button
				id={ `${ namespace }__prev-button` }
				isPrimary
				disabled={ null === currentItem.previousElementSibling }
				onClick={ () => {
					setCurrentItem( currentItem.previousElementSibling );
				} }
				className={ `${ namespace }__prev` }
				aria-label={ __( 'Previous', 'amp' ) }
			>
				<span className="components-visually-hidden">
					{ __( 'Previous', 'amp' ) }
				</span>
				<svg width="12" height="11" viewBox="0 0 12 11" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M5.47729 1.19531L1.18289 5.48906L5.47729 9.78347" stroke="#FAFAFC" strokeWidth="2" strokeLinejoin="round" />
					<path d="M1.15854 5.48828L10.281 5.48828" stroke="#FAFAFC" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" />
				</svg>

			</Button>
			{ showDots && (
				<div className={ `${ namespace }__dots` }>
					{ [ ...items ].map( ( item, itemIndex ) => (
						<Dot
							id={ `${ namespace }__${ item.id }-dot` }
							key={ `${ namespace }__${ item.id }-dot` }
							isCurrent={ item === currentItem }
							isSelected={ itemIndex === highlightedItemIndex }
							label={ item.getAttribute( 'data-label' ) }
							namespace={ namespace }
							onClick={ () => {
								setCurrentItem( item );
							} }
						/>
					) ) }
				</div>
			) }
			<Button
				id={ `${ namespace }__next-button` }
				isPrimary
				disabled={ null === currentItem.nextElementSibling }
				onClick={ () => {
					setCurrentItem( currentItem.nextElementSibling );
				} }
				className={ `${ namespace }__next` }
				aria-label={ __( 'Next', 'amp' ) }
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
CarouselNav.propTypes = {
	currentItem: PropTypes.object.isRequired,
	items: PropTypes.object.isRequired,
	namespace: PropTypes.string.isRequired,
	highlightedItemIndex: PropTypes.number.isRequired,
	setCurrentItem: PropTypes.func.isRequired,
	showDots: PropTypes.bool.isRequired,
};
