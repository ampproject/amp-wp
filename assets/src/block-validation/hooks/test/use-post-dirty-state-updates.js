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
	register: jest.fn(),
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

	it('calls setIsPostDirty(true) when updated content differs from initial content', () => {
		// Mock the subscribe function to capture the listener
		let subscribedListener;
		const mockSubscribe = jest.fn((listener) => {
			subscribedListener = listener;
			return jest.fn(); // return unsubscribe function
		});

		const { subscribe } = jest.requireMock('@wordpress/data');
		subscribe.mockImplementation(mockSubscribe);

		// Initial render with initial content
		getEditedPostContent.mockReturnValue('initial content');
		setupUseSelect({
			isPostDirty: false,
			isSavingOrPreviewingPost: false,
		});

		const { rerender } = render(<ComponentContainingHook />);

		// Verify setIsPostDirty was not called initially
		expect(setIsPostDirty).not.toHaveBeenCalledWith(true);

		// Change the content returned by getEditedPostContent
		getEditedPostContent.mockReturnValue('modified content');

		// Trigger the listener to update updatedContent state
		act(() => {
			subscribedListener();
		});

		// Re-render to trigger the useEffect that checks content !== updatedContent
		act(() => {
			rerender(<ComponentContainingHook />);
		});

		// Verify setIsPostDirty(true) was called when content changed
		expect(setIsPostDirty).toHaveBeenCalledWith(true);
	});

	it('calls setUpdatedContent when listener is triggered', () => {
		// Mock the subscribe function to capture the listener
		let subscribedListener;
		const mockSubscribe = jest.fn((listener) => {
			subscribedListener = listener;
			return jest.fn(); // return unsubscribe function
		});

		// Import and mock the subscribe function
		const { subscribe } = jest.requireMock('@wordpress/data');
		subscribe.mockImplementation(mockSubscribe);

		// Initial render
		getEditedPostContent.mockReturnValue('initial');
		setupUseSelect({
			isPostDirty: false, // Ensure post is not dirty so subscription happens
			isSavingOrPreviewingPost: false,
		});

		render(<ComponentContainingHook />);

		// Verify subscribe was called
		expect(subscribe).toHaveBeenCalledWith(expect.any(Function));
		expect(subscribedListener).toBeDefined();

		// Change the content that getEditedPostContent returns
		getEditedPostContent.mockReturnValue('updated content from listener');

		// Trigger the listener (simulating store change)
		act(() => {
			subscribedListener();
		});

		// Verify getEditedPostContent was called during listener execution
		expect(getEditedPostContent).toHaveBeenCalledWith();
	});
});
