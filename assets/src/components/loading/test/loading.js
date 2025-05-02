/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { create } from 'react-test-renderer';
import { describe, expect, it } from '@jest/globals';

/**
 * Internal dependencies
 */
import { Loading } from '..';

describe('the Loading component', () => {
	it('matches the snapshots', () => {
		const wrapperBlock = create(<Loading />);
		const wrapperInline = create(<Loading inline={true} />);

		expect(wrapperBlock.toJSON()).toMatchSnapshot();
		expect(wrapperInline.toJSON()).toMatchSnapshot();
	});

	it('renders a loading spinner', () => {
		const { container } = render(<Loading />);

		expect(
			container.querySelector('.amp-spinner-container')
		).not.toBeNull();
		expect(container.querySelector('.amp-spinner-container').tagName).toBe(
			'DIV'
		);
		expect(container.querySelector('.components-spinner')).not.toBeNull();
	});

	it('renders an inline loading spinner', () => {
		const { container } = render(<Loading inline={true} />);

		expect(
			container.querySelector('.amp-spinner-container--inline')
		).not.toBeNull();
		expect(
			container.querySelector('.amp-spinner-container--inline').tagName
		).toBe('SPAN');
	});
});
