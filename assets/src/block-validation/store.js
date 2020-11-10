/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';
/**
 * Internal dependencies
 */
import { VALIDATION_ERROR_ACK_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_REJECTED_STATUS, VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_NEW_REJECTED_STATUS } from './constants';

export const BLOCK_VALIDATION_STORE_KEY = 'amp/block-validation';

const SET_RAW_VALIDATION_ERRORS = 'SET_RAW_VALIDATION_ERRORS';
const SET_REVIEW_LINK = 'SET_REVIEW_LINK';
const SET_VALIDATION_ERRORS = 'SET_VALIDATION_ERRORS';

const initialState = {
	ampBroken: false,
	rawValidationErrors: [],
	reviewLink: null,
	reviewedValidationErrors: [],
	unreviewedValidationErrors: [],
	validationErrors: [],
};

/**
 * Register the store for block validation.
 */
export default registerStore(
	BLOCK_VALIDATION_STORE_KEY,
	{
		reducer: ( state = initialState, action ) => {
			switch ( action.type ) {
				case SET_RAW_VALIDATION_ERRORS:
					return { ...state, rawValidationErrors: action.rawValidationErrors };

				case SET_REVIEW_LINK:
					return { ...state, reviewLink: action.reviewLink };

				case SET_VALIDATION_ERRORS:
					return {
						...state,
						ampBroken: Boolean(
							action.validationErrors.filter( ( { status } ) =>
								status === VALIDATION_ERROR_NEW_REJECTED_STATUS || status === VALIDATION_ERROR_ACK_REJECTED_STATUS,
							),
						),
						reviewedValidationErrors: action.validationErrors
							.filter( ( { status } ) =>
								status === VALIDATION_ERROR_ACK_ACCEPTED_STATUS || status === VALIDATION_ERROR_ACK_REJECTED_STATUS,
							),
						validationErrors: action.validationErrors,
						unreviewedValidationErrors: action.validationErrors
							.filter( ( { status } ) =>
								status === VALIDATION_ERROR_NEW_ACCEPTED_STATUS || status === VALIDATION_ERROR_NEW_REJECTED_STATUS,
							),
					};

				default:
					return state;
			}
		},
		actions: {
			setRawValidationErrors: ( rawValidationErrors ) => ( { type: SET_RAW_VALIDATION_ERRORS, rawValidationErrors } ),
			setReviewLink: ( reviewLink ) => ( { type: SET_REVIEW_LINK, reviewLink } ),
			setValidationErrors: ( validationErrors ) => ( { type: SET_VALIDATION_ERRORS, validationErrors } ),
		},
		selectors: {
			getAMPBroken: ( { ampBroken } ) => ampBroken,
			getBlockValidationErrors: ( { validationErrors }, clientId ) => validationErrors.filter( ( error ) => error.clientId === clientId ),
			getRawValidationErrors: ( { rawValidationErrors } ) => rawValidationErrors,
			getReviewLink: ( { reviewLink } ) => reviewLink,
			getValidationErrors: ( { validationErrors } ) => validationErrors,
			getReviewedValidationErrors: ( { reviewedValidationErrors } ) => reviewedValidationErrors,
			getUnreviewedValidationErrors: ( { unreviewedValidationErrors } ) => unreviewedValidationErrors,
		},
		initialState,
	},
);
