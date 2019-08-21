/**
 * Internal dependencies
 */
import { getResizedWidthAndHeight } from '../helpers';

describe( 'getResizedWidthAndHeight', () => {
	const event = {
		clientX: 100,
		clientY: 100,
	};
	const angle = 45;
	const lastSeenX = 150;
	const lastSeenY = 150;

	it( 'should return zero for width delta when resizing from bottom', () => {
		const delta = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, 'bottom' );
		expect( delta.deltaW ).toStrictEqual( 0 );
	} );

	it( 'should return zero for height delta when resizing from right', () => {
		const delta = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, 'right' );
		expect( delta.deltaH ).toStrictEqual( 0 );
	} );

	it( 'should return correct width delta when resizing from right', () => {
		const delta = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, 'right' );
		expect( delta.deltaW ).toStrictEqual( -70.71067811865476 );
	} );

	it( 'should return correct height delta when resizing from bottom', () => {
		const delta = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, 'bottom' );
		expect( delta.deltaH ).toStrictEqual( -8.659560562354933e-15 );
	} );

	it( 'should return zero values if the direction is not matched', () => {
		const delta = getResizedWidthAndHeight( event, angle, lastSeenX, lastSeenY, 'some-direction' );
		expect( delta ).toStrictEqual( { deltaH: 0, deltaW: 0 } );
	} );
} );
