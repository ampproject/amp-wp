
/**
 * External dependencies
 */
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { ThemeCard } from '../theme-card';
import { Providers } from '../../..';

describe( 'ThemeCard', () => {
	it( 'matches snapshot', () => {
		const wrapper = create(
			<Providers>
				<ThemeCard homepage="http://mysite.com" description="Theme card" screenshotUrl="http://screenshot.jpeg" slug="theme-slug" name="Theme name" />
			</Providers>,
		);
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
