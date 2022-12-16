/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { ConditionalDetails } from '..';

describe('ConditionalDetails', () => {
	it('renders as expected', () => {
		let wrapper = create(
			<ConditionalDetails summary={<div>{'Summary'}</div>}>
				<div>{'children'}</div>
			</ConditionalDetails>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();

		wrapper = create(
			<ConditionalDetails summary={<div>{'Summary'}</div>}>
				{[null, null]}
			</ConditionalDetails>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('has correct classes', () => {
		let { container } = render(
			<ConditionalDetails summary={<div>{'Summary'}</div>}>
				{'children'}
			</ConditionalDetails>
		);

		expect(container.querySelector('details')).not.toBeNull();
		expect(container.querySelector('summary')).not.toBeNull();

		({ container } = render(
			<ConditionalDetails summary={<div>{'Summary'}</div>}>
				{[null, null]}
			</ConditionalDetails>
		));

		expect(container.querySelector('summary')).toBeNull();
		expect(container.querySelector('details')).toBeNull();
	});
});
