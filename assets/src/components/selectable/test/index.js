/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { create } from 'react-test-renderer';
import { describe, expect, it } from '@jest/globals';

/**
 * Internal dependencies
 */
import { Selectable } from '..';

describe('Selectable', () => {
	it('matches snapshot', () => {
		let wrapper = create(
			<Selectable selected={true}>
				<div>{'Component children'}</div>
			</Selectable>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();

		wrapper = create(
			<Selectable
				selected={true}
				ElementName="section"
				className="my-cool-class"
				direction="top"
			>
				<div>{'Component children'}</div>
			</Selectable>
		);
		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('has correct classes', () => {
		let { container } = render(
			<Selectable
				selected={true}
				ElementName="section"
				className="my-cool-class"
				direction="top"
			>
				<div>{'children'}</div>
			</Selectable>
		);

		expect(container.querySelector('section').getAttribute('class')).toBe(
			'my-cool-class selectable selectable--selected selectable--top'
		);

		({ container } = render(
			<Selectable selected={false} ElementName="section">
				<div>{'children'}</div>
			</Selectable>
		));

		expect(container.querySelector('section').getAttribute('class')).toBe(
			'selectable selectable--left'
		);
	});
});
