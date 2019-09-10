/**
 * Validates blocks for AMP compatibility.
 *
 * This uses the REST API response from saving a page to find validation errors.
 * If one exists for a block, it display it inline with a Notice component.
 */

/**
 * WordPress dependencies
 */
import { addFilter } from '@wordpress/hooks';
import { select, subscribe } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { updateValidationErrors, maybeResetValidationErrors } from './helpers';
import { isAMPEnabled } from '../block-editor/helpers';
import { withValidationErrorNotice } from './components';
import './store';
import '../block-editor/store';

const { isEditedPostDirty } = select( 'core/editor' );

subscribe( () => {
	try {
		if ( ! isEditedPostDirty() ) {
			if ( ! isAMPEnabled() ) {
				maybeResetValidationErrors();
			} else {
				updateValidationErrors();
			}
		}
	} catch ( err ) {}
} );

addFilter( 'editor.BlockEdit', 'amp/add-notice', withValidationErrorNotice, 99 );
