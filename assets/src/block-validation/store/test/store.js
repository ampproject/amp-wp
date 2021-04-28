/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY, createStore, INITIAL_STATE } from '../index';
import { rawValidationErrors } from './__data__/raw-validation-errors';

describe( 'Block validation data store', () => {
	beforeEach( () => {
		createStore( INITIAL_STATE );
	} );

	it( 'sets and selects state correctly', () => {
		dispatch( BLOCK_VALIDATION_STORE_KEY ).setIsShowingReviewed( true );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsShowingReviewed() ).toBe( true );
		dispatch( BLOCK_VALIDATION_STORE_KEY ).setIsShowingReviewed( false );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsShowingReviewed() ).toBe( false );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getMaybeIsPostDirty() ).toBe( false );
		dispatch( BLOCK_VALIDATION_STORE_KEY ).setMaybeIsPostDirty( true );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getMaybeIsPostDirty() ).toBe( true );
		dispatch( BLOCK_VALIDATION_STORE_KEY ).setMaybeIsPostDirty( false );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getMaybeIsPostDirty() ).toBe( false );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getFetchingErrorsRequestErrorMessage() ).toBe( '' );
		dispatch( BLOCK_VALIDATION_STORE_KEY ).setFetchingErrorsRequestErrorMessage( 'Error message' );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getFetchingErrorsRequestErrorMessage() ).toBe( 'Error message' );

		dispatch( BLOCK_VALIDATION_STORE_KEY ).setReviewLink( 'http://example.com' );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getReviewLink() ).toBe( 'http://example.com' );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken() ).toBe( false );

		dispatch( BLOCK_VALIDATION_STORE_KEY ).setValidationErrors( rawValidationErrors );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken() ).toBe( true );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 8 );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getReviewedValidationErrors() ).toHaveLength( 3 );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors() ).toHaveLength( 5 );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getKeptMarkupValidationErrors() ).toHaveLength( 1 );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors() ).toBe( false );
		dispatch( BLOCK_VALIDATION_STORE_KEY ).setIsFetchingErrors( true );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors() ).toBe( true );
		dispatch( BLOCK_VALIDATION_STORE_KEY ).setIsFetchingErrors( false );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors() ).toBe( false );
	} );
} );
