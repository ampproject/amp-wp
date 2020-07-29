/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Navigation dot.
 *
 * @param {Object} props Component props.
 * @param {boolean} props.isHighlighted Whether the current item is highlighted.
 * @param {string} props.id An HTML ID.
 * @param {string} props.label Button label.
 * @param {string} props.namespace CSS namespace.
 * @param {Function} props.onClick Click callback.
 */
function Dot( { id, isHighlighted, label, namespace, onClick } ) {
	return (
		<Button
			className={
				classnames(
					`${ namespace }__nav-dot-button`,
					{ [ `${ namespace }__nav-dot-button--active` ]: isHighlighted },
				)
			}
			id={ id }
			onClick={ onClick }
			aria-label={ label }
		>
			<span className="components-visually-hidden">
				{ label }
			</span>
			{ isHighlighted && (
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
	isHighlighted: PropTypes.bool.isRequired,
	label: PropTypes.string.isRequired,
	namespace: PropTypes.string.isRequired,
	onClick: PropTypes.func.isRequired,
};

/**
 * Dot navigation component.
 *
 * @param {Object} props Component props.
 * @param {Element} props.currentPage The current item.
 * @param {HTMLCollection} props.items Items in the carousel.
 * @param {string} props.namespace CSS namespace.
 * @param {Function} props.setCurrentPage Sets an item as the current item.
 * @param {number} props.centeredItemIndex Index of the item centered in the view.
 * @param {boolean} props.showDots Whether to show the dot navigation.
 * @param {boolean} props.prevButtonDisabled Whether the prev button is disabled.
 * @param {boolean} props.nextButtonDisabled Whether the next button is disabled.
 */
export function CarouselNav( {
	currentPage,
	items,
	namespace,
	nextButtonDisabled,
	prevButtonDisabled,
	setCurrentPage,
	centeredItemIndex,
	showDots,
} ) {
	return (
		<div className={ `${ namespace }__nav` }>
			<Button
				id={ `${ namespace }__prev-button` }
				isPrimary
				disabled={ prevButtonDisabled }
				onClick={ () => {
					setCurrentPage( currentPage.previousElementSibling );
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
			{ showDots ? (
				<div className={ `${ namespace }__dots` }>
					{ [ ...items ].map( ( item, itemIndex ) => (
						<Dot
							id={ `${ namespace }__${ item.id }-dot` }
							key={ `${ namespace }__${ item.id }-dot` }
							isHighlighted={ itemIndex === centeredItemIndex }
							label={ item.getAttribute( 'data-label' ) }
							namespace={ namespace }
							onClick={ () => {
								setCurrentPage( item );
							} }
						/>
					) ) }
				</div>
			) : (
				<div className={ `${ namespace }__item-counter` }>
					<span>
						{ centeredItemIndex + 1 }
					</span>
					<span>
						{ items.length }
					</span>
				</div>
			) }
			<Button
				id={ `${ namespace }__next-button` }
				isPrimary
				disabled={ nextButtonDisabled }
				onClick={ () => {
					setCurrentPage( currentPage.nextElementSibling );
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
	currentPage: PropTypes.object.isRequired,
	items: PropTypes.object.isRequired,
	namespace: PropTypes.string.isRequired,
	centeredItemIndex: PropTypes.number.isRequired,
	nextButtonDisabled: PropTypes.bool.isRequired,
	prevButtonDisabled: PropTypes.bool.isRequired,
	setCurrentPage: PropTypes.func.isRequired,
	showDots: PropTypes.bool.isRequired,
};
