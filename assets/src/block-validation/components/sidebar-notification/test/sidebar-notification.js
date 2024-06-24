/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { describe, expect, it } from '@jest/globals';

/**
 * Internal dependencies
 */
import { SidebarNotification } from '../index';

describe('SidebarNotification', () => {
	it('renders notification without icon and call to action', () => {
		const { container } = render(<SidebarNotification message="Foobar" />);

		expect(container.innerHTML).toMatchSnapshot();
		expect(container.children).toHaveLength(1);
		expect(container.querySelector('.sidebar-notification')).not.toBeNull();
		expect(
			container.querySelector('.sidebar-notification__icon')
		).toBeNull();
		expect(
			container.querySelector('.sidebar-notification__content')
				.textContent
		).toBe('Foobar');
	});

	it('renders status message with icon and call to action', () => {
		const { container } = render(
			<SidebarNotification
				message="Foobar"
				icon={<svg />}
				action={<button />}
			/>
		);

		expect(container.innerHTML).toMatchSnapshot();
		expect(container.querySelector('svg')).not.toBeNull();
		expect(container.querySelector('button')).not.toBeNull();
	});
});
