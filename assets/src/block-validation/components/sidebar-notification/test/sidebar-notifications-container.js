/**
 * External dependencies
 */
import { render } from '@testing-library/react';

/**
 * Internal dependencies
 */
import { SidebarNotificationsContainer } from '../index';

describe('SidebarNotificationsContainer', () => {
	it('renders sidebar notifications container along with children', () => {
		const { container } = render(
			<SidebarNotificationsContainer>
				{'Foo'}
			</SidebarNotificationsContainer>
		);

		expect(
			container.querySelector('.sidebar-notifications-container')
		).not.toBeNull();
		expect(
			container.querySelector('.sidebar-notifications-container')
				.textContent
		).toBe('Foo');
	});
});
