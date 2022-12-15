/**
 * External dependencies
 */
import { render } from '@testing-library/react';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { useAMPDocumentToggle } from '../use-amp-document-toggle';

jest.mock('@wordpress/data/build/components/use-select', () => jest.fn());
jest.mock('@wordpress/data/build/components/use-dispatch/use-dispatch', () =>
	jest.fn()
);

describe('useAMPDocumentToggle', () => {
	let container;

	const editPost = jest.fn();

	function ComponentContainingHook() {
		const { isAMPEnabled, toggleAMP } = useAMPDocumentToggle();

		return (
			<button onClick={toggleAMP}>
				{isAMPEnabled ? 'enabled' : 'disabled'}
			</button>
		);
	}

	function setupAndRender(isAMPEnabled) {
		useSelect.mockReturnValue(isAMPEnabled);

		container = render(<ComponentContainingHook />).container;
	}

	beforeAll(() => {
		useDispatch.mockImplementation(() => ({ editPost }));
	});

	it('returns AMP document enable state', () => {
		setupAndRender(false);
		expect(container.querySelector('button').textContent).toBe('disabled');

		setupAndRender(true);

		expect(container.querySelector('button').textContent).toBe('enabled');
	});

	it('toggleAMP disables AMP is it was enabled', () => {
		setupAndRender(true);
		container.querySelector('button').click();

		expect(editPost).toHaveBeenCalledWith({ amp_enabled: false });
	});

	it('toggleAMP enables AMP is it was disabled', () => {
		setupAndRender(false);
		container.querySelector('button').click();

		expect(editPost).toHaveBeenCalledWith({ amp_enabled: true });
	});
});
