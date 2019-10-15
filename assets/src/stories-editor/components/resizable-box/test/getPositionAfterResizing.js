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
			width: 250,
			height: 30,
			appliedWidth: 280,
			appliedHeight: 60,
			blockElementLeft: 5,
			blockElementTop: 10,
		};
		expect( getPositionAfterResizing( { attributes } ) ).toStrictEqual( { left: 6.318621296669647, top: -69.58141571295789 } );
	} );

	it( 'should return correct left and top position for a non-text block', () => {
		const attributes = {
			direction: 'bottomRight',
			angle: '-30',
			isText: false,
			width: 250,
			height: 30,
			appliedWidth: 280,
			appliedHeight: 60,
			blockElementLeft: 5,
			blockElementTop: 10,
		};
		expect( getPositionAfterResizing( { attributes } ) ).toStrictEqual( { left: -5.490381056766566, top: -9.509618943233406 } );
	} );
} );
