/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { usePrevious } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from '../store';

/**
 * Custom hook providing loading message when validation errors are fetched.
 */
export function useErrorsFetchingStateChanges() {
	const [ didFetchErrors, setDidFetchErrors ] = useState( false );
	const [ fetchingErrorsMessage, setFetchingErrorsMessage ] = useState( '' );

	const {
		isEditedPostNew,
		isFetchingErrors,
	} = useSelect( ( select ) => ( {
		isEditedPostNew: select( 'core/editor' ).isEditedPostNew(),
		isFetchingErrors: select( BLOCK_VALIDATION_STORE_KEY ).getIsFetchingErrors(),
	} ), [] );

	const wasEditedPostNew = usePrevious( isEditedPostNew );
	const wasFetchingErrors = usePrevious( isFetchingErrors );

	useEffect( () => {
		if ( didFetchErrors ) {
			return;
		}

		// Set up the state right after errors fetching has finished.
		if ( ! isFetchingErrors && wasFetchingErrors ) {
			setDidFetchErrors( true );
		}
	}, [ didFetchErrors, isFetchingErrors, wasFetchingErrors ] );

	/**
	 * Display best-suited loading message depending if the post has been
	 * already validated or not, or the editor has just been opened.
	 */
	useEffect( () => {
		if ( didFetchErrors ) {
			setFetchingErrorsMessage( __( 'Re-validating page content.', 'amp' ) );
		} else if ( isEditedPostNew || wasEditedPostNew ) {
			setFetchingErrorsMessage( __( 'Validating page content.', 'amp' ) );
		} else if ( isFetchingErrors ) {
			setFetchingErrorsMessage( __( 'Loading…', 'amp' ) );
		} else {
			setFetchingErrorsMessage( '' );
		}
	}, [ didFetchErrors, isEditedPostNew, isFetchingErrors, wasEditedPostNew ] );

	return {
		isFetchingErrors,
		fetchingErrorsMessage,
	};
}
