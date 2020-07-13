/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { Check } from '../../../components/svg/check';

/**
 * List bullet for a stepper item.
 *
 * @param {Object} props Component props.
 * @param {number} props.activePageIndex The index of the currently visible screen.
 * @param {number} props.index The index of the item being rendered.
 */
export function StepperBullet( { activePageIndex, index } ) {
	const isCheckMark = activePageIndex > index;
	const isCurrent = ( ! isCheckMark ) && activePageIndex === index;

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
 */
export function Stepper( { activePageIndex, pages } ) {
	const instanceId = useInstanceId( Stepper );

	return (
		<div className="amp-stepper">
			<ul>
				{ pages.map( ( { title }, index ) => (
					<li
						className={ `amp-stepper__item ${ index === activePageIndex ? 'amp-stepper__item--active' : '' } ${ activePageIndex > index ? 'amp-stepper__item--done' : '' }` }
						key={ `${ instanceId }-${ index }` }
					>
						<StepperBullet activePageIndex={ activePageIndex } index={ index } />
						<span className="amp-stepper__item-title">
							{ title }
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
			title: PropTypes.string.isRequired,
		} ),
	).isRequired,
};
