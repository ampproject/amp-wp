/**
 * Internal dependencies
 */
import { LAYER_DIRECTIONS } from '../../../../constants';

export function intersect( first, ...rest ) {
	if ( ! first || ! rest || rest.length === 0 ) {
		return first;
	}

	return rest.reduce(
		( intersection, list ) =>
			intersection.filter( ( value ) => list.includes( value ) ),
		first,
	);
}

export function isInsideRange( index, start, end ) {
	return index >= start && index <= end;
}

export function moveArrayElement( array, oldPosition, newPosition ) {
	// First remove from list.
	const element = array[ oldPosition ];
	const arrayWithoutElement = [
		...array.slice( 0, oldPosition ),
		...array.slice( oldPosition + 1 ),
	];

	// Then re-insert at the right point
	return [
		...arrayWithoutElement.slice( 0, newPosition ),
		element,
		...arrayWithoutElement.slice( newPosition ),
	];
}

export function getAbsolutePosition( {
	currentPosition,
	minPosition,
	maxPosition,
	desiredPosition,
} ) {
	if ( typeof desiredPosition === 'number' ) {
		return Math.min( maxPosition, Math.max( minPosition, desiredPosition ) );
	}

	if ( typeof desiredPosition !== 'string' ) {
		return currentPosition;
	}

	switch ( desiredPosition ) {
		case LAYER_DIRECTIONS.FRONT:
			return maxPosition;
		case LAYER_DIRECTIONS.BACK:
			return minPosition;
		case LAYER_DIRECTIONS.FORWARD:
			return Math.min( maxPosition, currentPosition + 1 );
		case LAYER_DIRECTIONS.BACKWARD:
			return Math.max( minPosition, currentPosition - 1 );
		default:
			return currentPosition;
	}
}

export function objectWithout( obj, propertiesToRemove ) {
	return Object.keys( obj )
		.filter( ( key ) => ! propertiesToRemove.includes( key ) )
		.reduce(
			( newObj, key ) => ( { ...newObj, [ key ]: obj[ key ] } ),
			{},
		);
}

