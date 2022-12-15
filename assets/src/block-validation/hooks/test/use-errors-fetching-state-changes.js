/**
 * External dependencies
 */
import { render } from '@testing-library/react';

/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useErrorsFetchingStateChanges } from '../use-errors-fetching-state-changes';

jest.mock('@wordpress/data/build/components/use-select', () => jest.fn());

describe('useErrorsFetchingStateChanges', () => {
	function ComponentContainingHook() {
		const { isFetchingErrors, fetchingErrorsMessage } =
			useErrorsFetchingStateChanges();

		return (
			<div>
				<div id="status">{isFetchingErrors ? 'Fetching' : 'Idle'}</div>
				<div id="message">{fetchingErrorsMessage}</div>
			</div>
		);
	}

	function setupAndRender(overrides) {
		useSelect.mockImplementation(() => ({
			isEditedPostNew: false,
			isFetchingErrors: false,
			...overrides,
		}));

		return render(<ComponentContainingHook />);
	}

	it('returns no loading message when errors are not being fetched', () => {
		const { container } = setupAndRender({
			isFetchingErrors: false,
		});

		expect(container.querySelector('#status').textContent).toBe('Idle');
		expect(container.querySelector('#message').textContent).toBe('');
	});

	it('returns correct status message when a new post is validated', () => {
		const { container } = setupAndRender({
			isEditedPostNew: true,
			isFetchingErrors: false,
		});

		expect(container.querySelector('#status').textContent).toBe('Idle');
		expect(container.querySelector('#message').textContent).toBe(
			'Validating content.'
		);
	});

	it('returns correct message when fetching errors and re-validating', () => {
		const { container, rerender } = setupAndRender({
			isFetchingErrors: true,
		});

		expect(container.querySelector('#status').textContent).toBe('Fetching');
		expect(container.querySelector('#message').textContent).toBe(
			'Loading…'
		);

		useSelect.mockImplementation(() => ({
			isEditedPostNew: false,
			isFetchingErrors: false,
		}));

		rerender(<ComponentContainingHook />);

		expect(container.querySelector('#status').textContent).toBe('Idle');
		expect(container.querySelector('#message').textContent).toBe(
			'Re-validating content.'
		);
	});
});
