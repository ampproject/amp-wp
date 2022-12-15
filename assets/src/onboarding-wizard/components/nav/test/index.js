/**
 * External dependencies
 */
import { render, fireEvent } from '@testing-library/react';
import { create } from 'react-test-renderer';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { Nav } from '..';
import { NavigationContextProvider } from '../../navigation-context-provider';
import { UserContextProvider } from '../../../../components/user-context-provider';
import { OptionsContextProvider } from '../../../../components/options-context-provider';
import { ReaderThemesContextProvider } from '../../../../components/reader-themes-context-provider';
import { SiteScanContextProvider } from '../../../../components/site-scan-context-provider';
import { STANDARD, READER } from '../../../../common/constants';

jest.mock('../../../../components/options-context-provider');
jest.mock('../../../../components/reader-themes-context-provider');
jest.mock('../../../../components/user-context-provider');
jest.mock('../../../../components/site-scan-context-provider');

const getNavButtons = (containerElement) => ({
	nextButton: containerElement.querySelector(
		'.amp-settings-nav__prev-next button.is-primary'
	),
	prevButton: containerElement.querySelector(
		'.amp-settings-nav__prev-next button:not(.is-primary)'
	),
});

const MyPageComponent = () => <div />;
const testPages = [
	{ PageComponent: MyPageComponent, slug: 'slug', title: 'Page 0' },
	{ PageComponent: MyPageComponent, slug: 'slug-2', title: 'Page 1' },
];

const Providers = ({
	children,
	pages,
	themeSupport = READER,
	downloadingTheme = false,
}) => (
	<OptionsContextProvider themeSupport={themeSupport}>
		<UserContextProvider>
			<SiteScanContextProvider>
				<NavigationContextProvider pages={pages}>
					<ReaderThemesContextProvider
						downloadingTheme={downloadingTheme}
					>
						{children}
					</ReaderThemesContextProvider>
				</NavigationContextProvider>
			</SiteScanContextProvider>
		</UserContextProvider>
	</OptionsContextProvider>
);
Providers.propTypes = {
	children: PropTypes.any,
	pages: PropTypes.array,
	themeSupport: PropTypes.string,
	downloadingTheme: PropTypes.bool,
};

describe('Nav', () => {
	it('matches snapshot', () => {
		const wrapper = create(
			<Providers pages={testPages}>
				<Nav
					closeLink="http://site.test/wp-admin"
					finishLink="http://site.test"
				/>
			</Providers>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('hides previous button on first page', () => {
		const { container } = render(
			<Providers pages={testPages}>
				<Nav
					closeLink="http://site.test/wp-admin"
					finishLink="http://site.test"
				/>
			</Providers>
		);

		const { nextButton, prevButton } = getNavButtons(container);

		expect(prevButton).toBeNull();
		expect(nextButton).not.toBeNull();
	});

	it('changes next button to "Customize" on last page', () => {
		const { container } = render(
			<Providers pages={testPages}>
				<Nav
					closeLink="http://site.test/wp-admin"
					finishLink="http://site.test"
				/>
			</Providers>
		);

		const { nextButton } = getNavButtons(container);

		expect(nextButton.textContent).toBe('Next');

		fireEvent.click(nextButton, new MouseEvent('click', { bubbles: true }));

		expect(nextButton.textContent).toBe('Customize');
	});

	it('close button hides on last page when reader mode is not selected', () => {
		const { container } = render(
			<Providers pages={testPages} themeSupport={STANDARD}>
				<Nav
					closeLink="http://site.test/wp-admin"
					finishLink="http://site.test"
				/>
			</Providers>
		);

		const { nextButton } = getNavButtons(container);
		let closeButton = container.querySelector('.amp-settings-nav__close a');

		expect(closeButton).not.toBeNull();

		fireEvent.click(nextButton, new MouseEvent('click', { bubbles: true }));

		closeButton = container.querySelector('.amp-settings-nav__close a');
		expect(closeButton).toBeNull();
	});

	it('close button hides on last page when reader mode is selected', () => {
		const { container } = render(
			<Providers pages={testPages}>
				<Nav
					closeLink="http://site.test/wp-admin"
					finishLink="http://site.test"
				/>
			</Providers>
		);

		const { nextButton } = getNavButtons(container);
		let closeButton = container.querySelector('.amp-settings-nav__close a');

		expect(closeButton).not.toBeNull();

		fireEvent.click(nextButton, new MouseEvent('click', { bubbles: true }));

		closeButton = container.querySelector('.amp-settings-nav__close a');
		expect(closeButton).not.toBeNull();
	});
});
