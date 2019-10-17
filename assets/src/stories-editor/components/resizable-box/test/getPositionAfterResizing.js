/**
 * Internal dependencies
 */
import { getPositionAfterResizing } from '../helpers';

describe( 'getPositionAfterResizing', () => {
	it( 'should return correct left and top position for text block', () => {
		const attributes = {
			direction: 'topLeft',
			angle: '-30',
			isText: true,
			oldWidth: 250,
			oldHeight: 30,
			newWidth: 280,
			newHeight: 60,
			oldPositionLeft: 5,
			oldPositionTop: 10,
		};
		expect( getPositionAfterResizing( attributes ) ).toStrictEqual( { left: -19.49038105676658, top: 34.509618943233406 } );
	} );

	it( 'should return correct left and top position for a non-text block', () => {
		const attributes = {
			direction: 'bottomRight',
			angle: '-30',
			isText: false,
			oldWidth: 250,
			oldHeight: 30,
			newWidth: 280,
			newHeight: 60,
			oldPositionLeft: 5,
			oldPositionTop: 10,
		};
		expect( getPositionAfterResizing( attributes ) ).toStrictEqual( { left: 21.490381056766566, top: 45.490381056766594 } );
	} );
} );
