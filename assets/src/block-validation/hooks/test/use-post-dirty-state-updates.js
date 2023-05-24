/**
 * External dependencies
 */
import { render, act } from '@testing-library/react';

/**
 * WordPress dependencies
 */
import {
	createReduxStore,
	dispatch,
	register,
	select,
	useSelect,
} from '@wordpress/data';

/**
 * Internal dependencies
 */
import { usePostDirtyStateChanges } from '../use-post-dirty-state-changes';
import { store as blockValidationStore } from '../../store';

jest.mock('@wordpress/data/build/components/use-select', () => jest.fn());
jest.mock('@wordpress/compose/build/hooks/use-debounce', () => (fn) => fn);

describe('usePostDirtyStateChanges', () => {
	const getEditedPostContent = jest.fn();

	function ComponentContainingHook() {
		usePostDirtyStateChanges();

		return null;
	}

	function renderComponentContainingHook() {
		render(<ComponentContainingHook />);
	}

	function setupUseSelect(overrides) {
		useSelect.mockImplementation(() => ({
			getEditedPostContent,
			isSavingOrPreviewingPost: false,
			isPostDirty: select(blockValidationStore).getIsPostDirty(),
			...overrides,
		}));
	}

	beforeAll(() => {
		register(
			createReduxStore('test/use-post-dirty-state-updates', {
				reducer: (state = {}) => ({ ...state }),
				actions: {
					change: () => ({ type: 'DUMMY' }),
				},
			})
		);
	});

	it('sets dirty state when content changes and clears it after save', () => {
		// Initial render.
		getEditedPostContent.mockReturnValue('initial');
		setupUseSelect();

		renderComponentContainingHook();

		expect(select(blockValidationStore).getIsPostDirty()).toBe(false);

		// Change content - post should become dirty.
		getEditedPostContent.mockReturnValue('foo');

		act(() => {
			dispatch('test/use-post-dirty-state-updates').change();
		});

		expect(select(blockValidationStore).getIsPostDirty()).toBe(true);

		// Save post - dirty state should get cleared.
		setupUseSelect({
			isSavingOrPreviewingPost: true,
		});

		renderComponentContainingHook();

		expect(select(blockValidationStore).getIsPostDirty()).toBe(false);

		// Change content - getEditedPostContent() should be called again
		getEditedPostContent.mockReturnValue('baz');
		setupUseSelect({
			isSavingOrPreviewingPost: false,
		});

		renderComponentContainingHook();

		act(() => {
			dispatch('test/use-post-dirty-state-updates').change();
		});

		expect(select(blockValidationStore).getIsPostDirty()).toBe(true);
	});
});
