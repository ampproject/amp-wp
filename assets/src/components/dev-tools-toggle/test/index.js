/**
 * External dependencies
 */
import { render, fireEvent } from '@testing-library/react';
import { create } from 'react-test-renderer';
import { describe, expect, it, jest } from '@jest/globals';

/**
 * Internal dependencies
 */
import { DevToolsToggle } from '../';
import { UserContextProvider } from '../../user-context-provider';

jest.mock('../../user-context-provider');

describe('DevToolsToggle', () => {
	it('matches snapshot', () => {
		const wrapper = create(
			<UserContextProvider>
				<DevToolsToggle />
			</UserContextProvider>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('renders a loading spinner when a user data is fetched', () => {
		const { container } = render(
			<UserContextProvider fetchingUser={true}>
				<DevToolsToggle />
			</UserContextProvider>
		);

		expect(
			container.querySelector('.amp-spinner-container')
		).not.toBeNull();
	});

	it('matches snapshot for the loading state', () => {
		const wrapper = create(
			<UserContextProvider fetchingUser={true}>
				<DevToolsToggle />
			</UserContextProvider>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('can be toggled', () => {
		const { container } = render(
			<UserContextProvider>
				<DevToolsToggle />
			</UserContextProvider>
		);

		expect(container.querySelector('input:checked')).toBeNull();

		fireEvent(container.querySelector('input'), new MouseEvent('click'));

		expect(container.querySelector('input:checked')).not.toBeNull();

		fireEvent(container.querySelector('input'), new MouseEvent('click'));

		expect(container.querySelector('input:checked')).toBeNull();
	});
});
