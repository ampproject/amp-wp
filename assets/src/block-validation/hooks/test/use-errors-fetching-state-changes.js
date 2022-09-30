/**
 * External dependencies
 */
import { act } from 'react-dom/test-utils';

/**
 * WordPress dependencies
 */
import { render, unmountComponentAtNode } from '@wordpress/element';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useErrorsFetchingStateChanges } from '../use-errors-fetching-state-changes';

jest.mock('@wordpress/data/build/components/use-select', () => jest.fn());

describe('useErrorsFetchingStateChanges', () => {
	let container = null;

	function ComponentContainingHook() {
		const { isFetchingErrors, fetchingErrorsMessage } =
			useErrorsFetchingStateChanges();

		return (
			<div>
				<div id="status">
{isFetchingErrors ? 'Fetching' : 'Idle'}
</div>
				<div id="message">
{fetchingErrorsMessage}
</div>
			</div>
		);
	}

	function setupAndRender(overrides) {
		useSelect.mockImplementation(() => ({
			isEditedPostNew: false,
			isFetchingErrors: false,
			...overrides,
		}));

		render(<ComponentContainingHook />, container);
	}

	beforeEach(() => {
		container = document.createElement('div');
		document.body.appendChild(container);
	});

	afterEach(() => {
		unmountComponentAtNode(container);
		container.remove();
		container = null;
	});

	it('returns no loading message when errors are not being fetched', () => {
		act(() => {
			setupAndRender({
				isFetchingErrors: false,
			});
		});

		expect(container.querySelector('#status').textContent).toBe('Idle');
		expect(container.querySelector('#message').textContent).toBe('');
	});

	it('returns correct status message when a new post is validated', () => {
		act(() => {
			setupAndRender({
				isEditedPostNew: true,
				isFetchingErrors: false,
			});
		});

		expect(container.querySelector('#status').textContent).toBe('Idle');
		expect(container.querySelector('#message').textContent).toBe(
			'Validating content.'
		);
	});

	it('returns correct message when fetching errors and re-validating', () => {
		act(() => {
			setupAndRender({
				isFetchingErrors: true,
			});
		});

		expect(container.querySelector('#status').textContent).toBe('Fetching');
		expect(container.querySelector('#message').textContent).toBe(
			'Loading…'
		);

		// Simulate state change so that the message is changed.
		act(() => {
			setupAndRender({
				isFetchingErrors: false,
			});
		});

		expect(container.querySelector('#status').textContent).toBe('Idle');
		expect(container.querySelector('#message').textContent).toBe(
			'Re-validating content.'
		);
	});
});
