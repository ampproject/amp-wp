/**
 * Internal dependencies
 */
import {
	intersect,
	isInsideRange,
	moveArrayElement,
	getAbsolutePosition,
	objectWithout,
} from '../utils';
import { LAYER_DIRECTIONS } from '../../../../../constants';

const ABC = [ 'A', 'B', 'C' ];
const BCD = [ 'B', 'C', 'D' ];
const ABCD = [ 'A', 'B', 'C', 'D' ];
const D = [ 'D' ];

describe( 'intersect', () => {
	it( 'should return first element if only one given', () => {
		const result = intersect( ABC );

		expect( result ).toStrictEqual( ABC );
	} );

	it( 'should return intersection set if multiple elements given', () => {
		const shouldBeBC = intersect( ABC, BCD );
		expect( shouldBeBC ).toStrictEqual( [ 'B', 'C' ] );

		const shouldBeD = intersect( ABCD, BCD, D );
		expect( shouldBeD ).toStrictEqual( [ 'D' ] );

		const shouldBeEmpty = intersect( ABC, BCD, D );
		expect( shouldBeEmpty ).toStrictEqual( [] );
	} );
} );

describe( 'isInsideRange', () => {
	it( 'should function as expected', () => {
		const isSingleDigit = ( number ) => isInsideRange( number, 0, 9 );

		expect( isSingleDigit( 0 ) ).toStrictEqual( true );
		expect( isSingleDigit( 3.2 ) ).toStrictEqual( true );
		expect( isSingleDigit( 9 ) ).toStrictEqual( true );

		expect( isSingleDigit( -0.1 ) ).toStrictEqual( false );
		expect( isSingleDigit( 10 ) ).toStrictEqual( false );

		expect( isSingleDigit( Number.POSITIVE_INFINITY ) ).toStrictEqual( false );
		expect( isSingleDigit( Number.NEGATIVE_INFINITY ) ).toStrictEqual( false );
		expect( isSingleDigit( Number.NaN ) ).toStrictEqual( false );
	} );
} );

describe( 'moveArrayElement', () => {
	it( 'should move element forwards', () => {
		// Move B from being 2nd in the array (position 1) to being 3rd (position 2)
		const result = moveArrayElement( ABCD, 1, 2 );
		expect( result ).toStrictEqual( [ 'A', 'C', 'B', 'D' ] );
	} );

	it( 'should move element backwards', () => {
		// Move C from being 3rd (position 2) to being 2nd (position 1)
		const result = moveArrayElement( ABCD, 2, 1 );
		expect( result ).toStrictEqual( [ 'A', 'C', 'B', 'D' ] );
	} );

	it( 'should not allow to move element outside of range', () => {
		// Move C from being 3rd (position 2) to being last (position 3+)
		const firstResult = moveArrayElement( ABCD, 2, 100 );
		expect( firstResult ).toStrictEqual( [ 'A', 'B', 'D', 'C' ] );

		// Move C from being 3rd (position 2) to being first (position 0-)
		const secondResult = moveArrayElement( ABCD, 2, -100 );
		expect( secondResult ).toStrictEqual( [ 'C', 'A', 'B', 'D' ] );
	} );
} );

describe( 'getAbsolutePosition', () => {
	it( 'should return clamped number', () => {
		const resultWithinLimits = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: 11,
		} );
		expect( resultWithinLimits ).toStrictEqual( 11 );

		const resultBelowLimit = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: -3,
		} );
		expect( resultBelowLimit ).toStrictEqual( 0 );

		const resultAboveLimit = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: 33,
		} );
		expect( resultAboveLimit ).toStrictEqual( 20 );
	} );

	it( 'should return top and bottom limit', () => {
		const resultToBack = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: LAYER_DIRECTIONS.BACK,
		} );
		expect( resultToBack ).toStrictEqual( 0 );

		const resultToFront = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: LAYER_DIRECTIONS.FRONT,
		} );
		expect( resultToFront ).toStrictEqual( 20 );
	} );

	it( 'should return relative position', () => {
		const resultGoingBackward = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: LAYER_DIRECTIONS.BACKWARD,
		} );
		expect( resultGoingBackward ).toStrictEqual( 9 );

		const resultGoingBelow = getAbsolutePosition( {
			currentPosition: 0,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: LAYER_DIRECTIONS.BACKWARD,
		} );
		expect( resultGoingBelow ).toStrictEqual( 0 );

		const resultGoingForward = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: LAYER_DIRECTIONS.FORWARD,
		} );
		expect( resultGoingForward ).toStrictEqual( 11 );

		const resultGoingAbove = getAbsolutePosition( {
			currentPosition: 20,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: LAYER_DIRECTIONS.FORWARD,
		} );
		expect( resultGoingAbove ).toStrictEqual( 20 );
	} );

	it( 'should ignore invalid input', () => {
		const resultGoingBackward = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: 'OFF_THE_CHARTS',
		} );
		expect( resultGoingBackward ).toStrictEqual( 10 );

		const resultGoingForward = getAbsolutePosition( {
			currentPosition: 10,
			minPosition: 0,
			maxPosition: 20,
			desiredPosition: [ 15 ],
		} );
		expect( resultGoingForward ).toStrictEqual( 10 );
	} );
} );

describe( 'objectWithout', () => {
	it( 'should return a cloned object without the given key', () => {
		const input = { a: 1, b: 2 };
		const result = objectWithout( input, 'a' );
		expect( input ).toStrictEqual( { a: 1, b: 2 } );
		expect( result ).toStrictEqual( { b: 2 } );
	} );

	it( 'should do nothing if key not found', () => {
		const input = { a: 1, b: 2 };
		const result = objectWithout( input, 'c' );
		expect( input ).toStrictEqual( { a: 1, b: 2 } );
		expect( result ).toStrictEqual( { a: 1, b: 2 } );
	} );
} );
