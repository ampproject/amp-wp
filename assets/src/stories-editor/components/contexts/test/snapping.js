/**
 * External dependencies
 */
import { mount } from 'enzyme';
import { act } from 'react-dom/test-utils';

/**
 * Internal dependencies
 */
import { default as Snapping, withSnapContext } from '../snapping';

const setup = () => {
	const Dummy = () => null;
	// Disable reason: I have no idea why eslint thinks this variable is unused?
	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const SnappyDummy = withSnapContext( Dummy );
	const container = (
		<Snapping>
			<SnappyDummy />
		</Snapping>
	);
	const wrapper = mount( container );
	const snapProps = wrapper.find( Dummy ).props();
	const callbacks = [
		'setSnapLines',
		'showSnapLines',
		'hideSnapLines',
		'clearSnapLines',
	];

	// Wrap each callback in act() and follow it up with wrapper.update()
	const wrappedSnapCallbacks = callbacks.reduce(
		( obj, cb ) => ( {
			...obj,
			[ cb ]: ( ...args ) => {
				act( () => snapProps[ cb ]( ...args ) );
				wrapper.update();
			},
		} ),
		{}
	);

	const getDisplayedSnapLines = () => wrapper.find( 'line' );

	return {
		container,
		wrapper,
		getDisplayedSnapLines,
		...wrappedSnapCallbacks,
	};
};

describe( 'Snapping', () => {
	const HORIZONTAL_SNAP_LINE = [ [ 0, 100 ], [ 100, 100 ] ];
	const VERTICAL_SNAP_LINE = [ [ 100, 0 ], [ 100, 100 ] ];

	it( 'should not display any snap lines by default', () => {
		const { getDisplayedSnapLines } = setup();

		const displayedSnapLines = getDisplayedSnapLines();

		expect( displayedSnapLines ).toHaveLength( 0 );
	} );

	it( 'should not display any snap lines when set but not shown', () => {
		const { setSnapLines, getDisplayedSnapLines } = setup();

		setSnapLines( [ VERTICAL_SNAP_LINE, HORIZONTAL_SNAP_LINE ] );

		const displayedSnapLines = getDisplayedSnapLines();

		expect( displayedSnapLines ).toHaveLength( 0 );
	} );

	it( 'should display snap lines when shown and set', () => {
		const { setSnapLines, showSnapLines, getDisplayedSnapLines } = setup();

		showSnapLines();
		setSnapLines( [ VERTICAL_SNAP_LINE, HORIZONTAL_SNAP_LINE ] );

		const displayedSnapLines = getDisplayedSnapLines();

		expect( displayedSnapLines ).toHaveLength( 2 );
	} );

	it( 'should render a single snap line correctly when shown and set', () => {
		const { setSnapLines, showSnapLines, getDisplayedSnapLines } = setup();

		showSnapLines();
		setSnapLines( [ HORIZONTAL_SNAP_LINE ] );

		const displayedSnapLines = getDisplayedSnapLines();

		expect( displayedSnapLines ).toMatchSnapshot();
	} );

	it( 'should display only new snap lines when shown, set and set again', () => {
		const { setSnapLines, showSnapLines, getDisplayedSnapLines } = setup();

		showSnapLines();
		setSnapLines( [ VERTICAL_SNAP_LINE, HORIZONTAL_SNAP_LINE ] );
		setSnapLines( [ VERTICAL_SNAP_LINE ] );

		const displayedSnapLines = getDisplayedSnapLines();

		expect( displayedSnapLines ).toHaveLength( 1 );
	} );

	it( 'should not display any snap lines when set, shown and then hidden', () => {
		const {
			setSnapLines,
			showSnapLines,
			hideSnapLines,
			getDisplayedSnapLines,
		} = setup();

		setSnapLines( [ VERTICAL_SNAP_LINE, HORIZONTAL_SNAP_LINE ] );
		showSnapLines();
		hideSnapLines();

		const displayedSnapLines = getDisplayedSnapLines();

		expect( displayedSnapLines ).toHaveLength( 0 );
	} );

	it( 'should display snap lines when set, shown, hidden and then shown', () => {
		const {
			setSnapLines,
			showSnapLines,
			hideSnapLines,
			getDisplayedSnapLines,
		} = setup();

		setSnapLines( [ VERTICAL_SNAP_LINE, HORIZONTAL_SNAP_LINE ] );
		showSnapLines();
		hideSnapLines();
		showSnapLines();

		const displayedSnapLines = getDisplayedSnapLines();

		expect( displayedSnapLines ).toHaveLength( 2 );
	} );

	it( 'should not display any snap lines when set, shown and cleared', () => {
		const {
			setSnapLines,
			showSnapLines,
			clearSnapLines,
			getDisplayedSnapLines,
		} = setup();

		setSnapLines( [ VERTICAL_SNAP_LINE, HORIZONTAL_SNAP_LINE ] );
		showSnapLines();
		clearSnapLines();

		const displayedSnapLines = getDisplayedSnapLines();

		expect( displayedSnapLines ).toHaveLength( 0 );
	} );
} );
