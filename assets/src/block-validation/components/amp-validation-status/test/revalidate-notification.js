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
import AMPRevalidateNotification from '../revalidate-notification';
import { useErrorsFetchingStateChanges } from '../../../hooks/use-errors-fetching-state-changes';

jest.mock('@wordpress/data/build/components/use-select', () => jest.fn());
jest.mock('@wordpress/data/build/components/use-dispatch/use-dispatch', () =>
	jest.fn()
);
jest.mock('../../../hooks/use-errors-fetching-state-changes', () => ({
	useErrorsFetchingStateChanges: jest.fn(),
}));

describe('AMPRevalidateNotification', () => {
	const autosave = jest.fn();
	const savePost = jest.fn();

	function setupUseSelect(overrides) {
		useSelect.mockImplementation(() => ({
			hasActiveMetaboxes: false,
			isDraft: false,
			isFetchingErrors: false,
			isPostDirty: false,
			maybeIsPostDirty: false,
			...overrides,
		}));
	}

	beforeAll(() => {
		useDispatch.mockImplementation(() => ({ autosave, savePost }));
	});

	beforeEach(() => {
		useErrorsFetchingStateChanges.mockImplementation(() => ({
			isFetchingErrors: false,
			fetchingErrorsMessage: '',
		}));
	});

	it('does not render revalidate message if post is not dirty', () => {
		setupUseSelect();

		const { container } = render(<AMPRevalidateNotification />);

		expect(container.children).toHaveLength(0);
	});

	it('renders loading spinner when errors are being fetched', () => {
		useErrorsFetchingStateChanges.mockImplementation(() => ({
			isFetchingErrors: true,
			fetchingErrorsMessage: 'Loading',
		}));

		const { container } = render(<AMPRevalidateNotification />);

		expect(
			container.querySelector('.amp-spinner-container')
		).not.toBeNull();
		expect(container.innerHTML).toContain('Loading');
	});

	it('renders revalidate message if post is dirty', () => {
		setupUseSelect({
			isPostDirty: true,
		});

		const { container } = render(<AMPRevalidateNotification />);

		expect(container.innerHTML).toMatchSnapshot();
		expect(container.children).toHaveLength(1);
		expect(container.innerHTML).toContain('has changed');
		expect(container.querySelector('svg')).not.toBeNull();
		expect(container.querySelector('button').textContent).toContain(
			'Re-validate'
		);

		container.querySelector('button').click();
		expect(autosave).toHaveBeenCalledWith({ isPreview: true });
	});

	it('renders revalidate message if draft post is dirty', () => {
		setupUseSelect({
			isDraft: true,
			isPostDirty: true,
		});

		const { container } = render(<AMPRevalidateNotification />);

		expect(container.innerHTML).toMatchSnapshot();
		expect(container.innerHTML).toContain('has changed');
		expect(container.querySelector('button').textContent).toContain(
			'Save draft'
		);

		container.querySelector('button').click();
		expect(savePost).toHaveBeenCalledWith({ isPreview: true });
	});

	it('always renders revalidate status message if there are active meta boxes', () => {
		setupUseSelect({
			isDraft: false,
			isPostDirty: false,
			maybeIsPostDirty: true,
		});

		const { container } = render(<AMPRevalidateNotification />);

		expect(container.innerHTML).toMatchSnapshot();
		expect(container.children).toHaveLength(1);
		expect(container.innerHTML).toContain('may have changed');
		expect(container.querySelector('button')).not.toBeNull();
	});
});
