/**
 * External dependencies
 */
import { render, fireEvent } from '@testing-library/react';
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { NavMenu } from '../index';

const links = [
	{
		url: 'https://example.com/foo',
		label: 'Foo',
		isActive: false,
	},
	{
		url: 'https://example.com/bar',
		label: 'Bar',
		isActive: true,
	},
];

describe('NavMenu', () => {
	it('matches the snapshot', () => {
		const wrapper = create(<NavMenu links={links} />);

		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('renders a nav menu with a list of links', () => {
		const { container } = render(<NavMenu links={links} />);

		expect(container.querySelector('nav')).not.toBeNull();
		expect(container.querySelector('ul')).not.toBeNull();
		expect(container.querySelectorAll('li')).toHaveLength(2);
	});

	it('contains correct links', () => {
		const { container } = render(<NavMenu links={links} />);

		expect(container.querySelector('nav').textContent).toBe('FooBar');
		expect(container.querySelectorAll('a')).toHaveLength(2);
		expect(
			container.querySelectorAll('a[href="https://example.com/foo"]')
		).toHaveLength(1);
		expect(
			container.querySelectorAll('a[href="https://example.com/bar"]')
		).toHaveLength(1);
		expect(container.querySelectorAll('a[class*="--active"]')).toHaveLength(
			1
		);
		expect(
			container.querySelector('a[class*="--active"]').getAttribute('href')
		).toBe('https://example.com/bar');
	});

	it('calls the handler function on click', () => {
		const handler = jest.fn();

		const { container } = render(
			// Pass empty URLs to avoid `Error: Not implemented: navigation (except hash changes)` in tests.
			// This is due to the JSDOM limitation to not support navigation.
			// Since we're not testing onClick event and not testing navigation, we can safely pass empty URLs.
			<NavMenu
				links={[
					{
						url: '',
						label: 'Foo',
						isActive: false,
					},
					{
						url: '',
						label: 'Bar',
						isActive: true,
					},
				]}
				onClick={handler}
			/>
		);

		fireEvent.click(container.querySelector('a'));

		expect(handler).toHaveBeenCalledTimes(1);

		const [event, link] = handler.mock.calls[0];
		expect(event.type).toBe('click');
		expect(link).toStrictEqual({
			url: '',
			label: 'Foo',
			isActive: false,
		});
	});
});
