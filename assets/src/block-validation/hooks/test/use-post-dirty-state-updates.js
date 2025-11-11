/**
 * External dependencies
 */
import { render, act } from '@testing-library/react';
import {
	beforeAll,
	beforeEach,
	describe,
	expect,
	it,
	jest,
} from '@jest/globals';

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

const mockState = { isPostDirty: false };

jest.mock('@wordpress/data', () => ({
	useSelect: jest.fn(),
	useDispatch: jest.fn(() => ({})),
	createReduxStore: jest.fn((key, options) => ({ key, ...options })),
	combineReducers: jest.fn((reducers) => reducers),
	register: jest.fn(),
	createSelector: jest.fn(),
	select: jest.fn((storeName) => {
		if (storeName?.key === 'amp/block-validation') {
			return { getIsPostDirty: jest.fn(() => mockState.isPostDirty) };
		}
		return { getIsPostDirty: jest.fn(() => false) };
	}),
	dispatch: jest.fn((storeName) => {
		if (storeName === 'test/use-post-dirty-state-updates') {
			return {
				change: jest.fn(() => {
					mockState.isPostDirty = true;
				}),
			};
		}
		return { change: jest.fn() };
	}),
	subscribe: jest.fn(() => jest.fn()),
}));

const { useDispatch } = jest.requireMock('@wordpress/data');
jest.mock('@wordpress/compose', () => ({
	...jest.requireActual('@wordpress/compose'),
	useDebounce: (fn) => fn,
}));

describe('usePostDirtyStateChanges', () => {
	const getEditedPostContent = jest.fn();
	const setIsPostDirty = jest.fn();
	const setMaybeIsPostDirty = jest.fn();

	function ComponentContainingHook() {
		usePostDirtyStateChanges();

		return null;
	}

	function renderComponentContainingHook() {
		render(<ComponentContainingHook />);
	}

	function setupUseSelect(overrides) {
		const settings = {
			getEditedPostContent,
			isSavingOrPreviewingPost: false,
			isPostDirty: select(blockValidationStore).getIsPostDirty(),
			...overrides,
		};

		// If saving/previewing, clear dirty state
		if (settings.isSavingOrPreviewingPost) {
			mockState.isPostDirty = false;
		}

		useSelect.mockImplementation(() => settings);
	}

	function setupUseDispatch() {
		useDispatch.mockReturnValue({
			setIsPostDirty,
			setMaybeIsPostDirty,
		});
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

	beforeEach(() => {
		mockState.isPostDirty = false;
		setupUseDispatch();
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
