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
	const isMovingForward = newPosition > oldPosition;
	const insertionPoint = isMovingForward ? newPosition - 1 : newPosition;
	return [
		...arrayWithoutElement.slice( 0, insertionPoint ),
		element,
		...arrayWithoutElement.slice( insertionPoint ),
	];
}

export function getAbsolutePosition( currentPosition, maxPosition, newPosition ) {
	if ( typeof newPosition !== 'string' ) {
		return newPosition;
	}

	switch ( newPosition ) {
		case 'FRONT':
			return maxPosition;
		case 'BACK':
			return 0;
		case 'FORWARD':
			return currentPosition + 1;
		case 'BACKWARD':
			return currentPosition - 1;
		default:
			return currentPosition;
	}
}
