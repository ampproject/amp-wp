/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { AMPSettingToggle } from '../';

describe('AMPSettingToggle', () => {
	it('matches snapshots', () => {
		let wrapper = create(
			<AMPSettingToggle
				checked={true}
				onChange={() => null}
				text={'My text'}
				title={'My title'}
			/>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();

		wrapper = create(
			<AMPSettingToggle
				checked={false}
				onChange={() => null}
				title={'My title'}
			/>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('has correct elements and text', () => {
		let { container } = render(
			<AMPSettingToggle
				title="My title"
				onChange={() => null}
				checked={false}
			>
				{'children'}
			</AMPSettingToggle>
		);

		expect(container.querySelector('h3').textContent).toBe('My title');
		expect(container.querySelector('p')).toBeNull();
		expect(container.querySelector('input:checked')).toBeNull();

		container = render(
			<AMPSettingToggle
				title="My title"
				onChange={() => null}
				checked={true}
				text="My text"
			>
				{'children'}
			</AMPSettingToggle>
		).container;

		expect(container.querySelector('h3').textContent).toBe('My title');
		expect(container.querySelector('p').textContent).toBe('My text');
		expect(container.querySelector('input:checked')).not.toBeNull();
	});

	it('renders title if it is a valid element', () => {
		const { container } = render(
			<AMPSettingToggle
				title={<h6>{'My title'}</h6>}
				onChange={() => null}
				checked={false}
			/>
		);

		expect(container.querySelector('h6').textContent).toBe('My title');
	});

	it('does not render title if nothing is passed', () => {
		const { container } = render(
			<AMPSettingToggle onChange={() => null} checked={false} />
		);

		expect(container.querySelector('h3')).toBeNull();
	});
});
