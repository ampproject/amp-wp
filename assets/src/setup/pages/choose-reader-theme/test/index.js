
/**
 * External dependencies
 */
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { ThemeCard } from '../theme-card';
import { Providers } from '../../..';

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
			<Providers>
				<ThemeCard homepage="http://mysite.com" description="Theme card" screenshotUrl="http://screenshot.jpeg" slug="theme-slug" name="Theme name" />
			</Providers>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
