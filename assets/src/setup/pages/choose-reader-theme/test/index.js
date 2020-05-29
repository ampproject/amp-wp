
/**
 * External dependencies
 */
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { ThemeCard } from '../theme-card';

let container;

describe( 'ThemeCard', () => {
	beforeEach( () => {
		container = document.createElement( 'div' );
		document.body.appendChild( container );
	} );

	afterEach( () => {
		document.body.removeChild( container );
		container = null;
	} );

	it( 'matches snapshot', () => {
		const wrapper = create(
			<ThemeCard description="Theme card" screenshotUrl="http://screenshot.jpeg" slug="theme-slug" name="Theme name" />,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
