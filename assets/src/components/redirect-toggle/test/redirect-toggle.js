/**
 * External dependencies
 */
import { render, fireEvent } from '@testing-library/react';
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { RedirectToggle } from '../';
import { OptionsContextProvider } from '../../options-context-provider';

jest.mock('../../options-context-provider');

describe('RedirectToggle', () => {
	it('matches snapshot', () => {
		const wrapper = create(
			<OptionsContextProvider>
				<RedirectToggle />
			</OptionsContextProvider>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('can be toggled', () => {
		const { container } = render(
			<OptionsContextProvider>
				<RedirectToggle />
			</OptionsContextProvider>
		);

		expect(container.querySelector('.is-checked')).not.toBeNull();

		fireEvent(container.querySelector('input'), new MouseEvent('click'));

		expect(container.querySelector('input:checked')).toBeNull();

		fireEvent(container.querySelector('input'), new MouseEvent('click'));

		expect(container.querySelector('.is-checked')).not.toBeNull();
	});
});
