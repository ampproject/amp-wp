/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';
import { useMemo, ReactElement } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Check } from '../check';
import './stepper.css';

/**
 * List bullet for a stepper item.
 *
 * @param {Object} props Component props.
 * @param {number} props.activePageIndex The index of the currently visible screen.
 * @param {number} props.index The index of the item being rendered.
 * @return {ReactElement} Item JSX.
 */
export function StepperBullet( { activePageIndex, index } ) {
	const isCheckMark = useMemo( () => index === 0, [ index ] );
	const isCurrent = useMemo( () => ( ! isCheckMark ) && activePageIndex === index, [ activePageIndex, index, isCheckMark ] );

	if ( isCheckMark ) {
		return (
			<span className="amp-stepper__bullet amp-stepper__bullet--check">
				<Check />
			</span>
		);
	}

	if ( isCurrent ) {
		return (
			<span className="amp-stepper__bullet amp-stepper__bullet--dot">
				<span />
			</span>
		);
	}

	return (
		<span className="amp-stepper__bullet">
			{ index + 1 }
		</span>
	);
}

StepperBullet.propTypes = {
	activePageIndex: PropTypes.number.isRequired,
	index: PropTypes.number.isRequired,
};

/**
 * Stepper component.
 *
 * @param {Object} props Component props.
 * @param {number} props.activePageIndex The index of the currently visible screen.
 * @param {Array} props.pages Page objects.
 * @return {ReactElement} Stepper JSX.
 */
export function Stepper( { activePageIndex, pages } ) {
	const instanceId = useInstanceId( Stepper );

	return (
		<div className="amp-stepper">
			<ul>
				{ pages.map( ( { navTitle }, index ) => (
					<li
						className={ `amp-stepper__item ${ index === activePageIndex ? 'amp-stepper__item--active' : '' }` }
						key={ `${ instanceId }-${ index }` }
					>
						<StepperBullet activePageIndex={ activePageIndex } index={ index } />
						<span className="amp-stepper__item-title">
							{ navTitle }
						</span>
					</li>
				) ) }
			</ul>
		</div>
	);
}

Stepper.propTypes = {
	activePageIndex: PropTypes.number.isRequired,
	pages: PropTypes.arrayOf(
		PropTypes.shape( {
			navTitle: PropTypes.string.isRequired,
		} ),
	).isRequired,
};
