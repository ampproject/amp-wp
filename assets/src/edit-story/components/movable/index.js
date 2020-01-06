/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import MovableGroup from './movableGroup';
import MovableSingle from './movableSingle';

function Movable( {
	selectedElement,
	targetEl,
	targets: targetList,
	pushEvent,
} ) {
	const {
		state: { selectedElements },
	} = useStory();

	if ( 1 < selectedElements.length ) {
		return (
			<MovableGroup targets={ targetList } />
		);
	}

	return (
		<MovableSingle targetEl={ targetEl } pushEvent={ pushEvent } selectedEl={ selectedElement } />
	);
}

Movable.propTypes = {
	selectedElement: PropTypes.object,
	targetEl: PropTypes.object,
	targets: PropTypes.array,
	pushEvent: PropTypes.object,
};

export default Movable;
