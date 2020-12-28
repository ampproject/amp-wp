/**
 * WordPress dependencies
 */
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY, createStore, INITIAL_STATE } from '../store';
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

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken() ).toBe( false );

		dispatch( BLOCK_VALIDATION_STORE_KEY ).setValidationErrors( rawValidationErrors );

		expect( select( BLOCK_VALIDATION_STORE_KEY ).getAMPCompatibilityBroken() ).toBe( true );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getValidationErrors() ).toHaveLength( 8 );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getReviewedValidationErrors() ).toHaveLength( 3 );
		expect( select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors() ).toHaveLength( 5 );
	} );
} );
