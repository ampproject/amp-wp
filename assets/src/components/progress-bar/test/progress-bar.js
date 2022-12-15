/**
 * External dependencies
 */
import { render } from '@testing-library/react';
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { ProgressBar } from '../index';

describe('ProgressBar', () => {
	it('matches the snapshot', () => {
		const wrapper = create(<ProgressBar value={33} />);

		expect(wrapper.toJSON()).toMatchSnapshot();
	});

	it('renders a progress bar', () => {
		const { container } = render(<ProgressBar value={33} />);

		expect(
			container.querySelector('.progress-bar[role="progressbar"]')
		).not.toBeNull();
		expect(
			container.querySelector('.progress-bar[aria-valuemin="0"]')
		).not.toBeNull();
		expect(
			container.querySelector('.progress-bar[aria-valuemax="100"]')
		).not.toBeNull();
		expect(
			container.querySelector('.progress-bar[aria-valuenow="33"]')
		).not.toBeNull();
	});

	it('the bar is shifted correctly', () => {
		const { container } = render(<ProgressBar value={75} />);

		expect(
			container.querySelector('.progress-bar[aria-valuenow="75"]')
		).not.toBeNull();
		expect(
			container.querySelector('.progress-bar__indicator')
		).not.toBeNull();
		expect(
			container.querySelector('.progress-bar__indicator').style.transform
		).toBe('translateX(-25%)');
	});

	it('does not allow the bar to be completely out of view for low values', () => {
		const { container } = render(<ProgressBar value={1} />);

		expect(
			container.querySelector('.progress-bar[aria-valuenow="1"]')
		).not.toBeNull();
		expect(
			container.querySelector('.progress-bar__indicator')
		).not.toBeNull();
		expect(
			container.querySelector('.progress-bar__indicator').style.transform
		).toBe('translateX(-97%)');
	});
});
