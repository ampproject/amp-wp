/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { store as blockValidationStore } from '../index';
import { rawValidationErrors } from './__data__/raw-validation-errors';

describe('Block validation data store', () => {
	it('sets and selects state correctly', () => {
		dispatch(blockValidationStore).setIsShowingReviewed(true);
		expect(select(blockValidationStore).getIsShowingReviewed()).toBe(true);
		dispatch(blockValidationStore).setIsShowingReviewed(false);
		expect(select(blockValidationStore).getIsShowingReviewed()).toBe(false);

		expect(select(blockValidationStore).getMaybeIsPostDirty()).toBe(false);
		dispatch(blockValidationStore).setMaybeIsPostDirty(true);
		expect(select(blockValidationStore).getMaybeIsPostDirty()).toBe(true);
		dispatch(blockValidationStore).setMaybeIsPostDirty(false);
		expect(select(blockValidationStore).getMaybeIsPostDirty()).toBe(false);

		expect(
			select(blockValidationStore).getFetchingErrorsRequestErrorMessage()
		).toBe('');
		dispatch(blockValidationStore).setFetchingErrorsRequestErrorMessage(
			'Error message'
		);
		expect(
			select(blockValidationStore).getFetchingErrorsRequestErrorMessage()
		).toBe('Error message');

		dispatch(blockValidationStore).setReviewLink('http://example.com');
		expect(select(blockValidationStore).getReviewLink()).toBe(
			'http://example.com'
		);

		expect(select(blockValidationStore).getAMPCompatibilityBroken()).toBe(
			false
		);

		dispatch(blockValidationStore).setValidationErrors(rawValidationErrors);

		expect(select(blockValidationStore).getAMPCompatibilityBroken()).toBe(
			true
		);
		expect(select(blockValidationStore).getValidationErrors()).toHaveLength(
			8
		);
		expect(
			select(blockValidationStore).getReviewedValidationErrors()
		).toHaveLength(3);
		expect(
			select(blockValidationStore).getUnreviewedValidationErrors()
		).toHaveLength(5);
		expect(
			select(blockValidationStore).getKeptMarkupValidationErrors()
		).toHaveLength(1);

		expect(select(blockValidationStore).getIsFetchingErrors()).toBe(false);
		dispatch(blockValidationStore).setIsFetchingErrors(true);
		expect(select(blockValidationStore).getIsFetchingErrors()).toBe(true);
		dispatch(blockValidationStore).setIsFetchingErrors(false);
		expect(select(blockValidationStore).getIsFetchingErrors()).toBe(false);
	});
});
