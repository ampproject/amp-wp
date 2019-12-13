/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import MovableGroup from './movableGroup';
import MovableSingle from './movableSingle';

function Movable( {
	selectedEl,
	targetEl,
	targets: targetList,
	pushEvent,
} ) {
	if ( targetList && 1 <= targetList.length ) {
		return (
			<MovableGroup targets={ targetList } />
		);
	}

	return (
		<MovableSingle targetEl={ targetEl } pushEvent={ pushEvent } selectedEl={ selectedEl } />
	);
}

Movable.propTypes = {
	selectedEl: PropTypes.object,
	targetEl: PropTypes.object,
	targets: PropTypes.array,
	pushEvent: PropTypes.object,
};

export default Movable;
