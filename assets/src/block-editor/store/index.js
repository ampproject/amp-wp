/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import * as selectors from './selectors';

/**
 * Module Constants
 */
const MODULE_KEY = 'amp/block-editor';

export default registerStore(
	MODULE_KEY,
	{
		reducer: ( state ) => state,
		selectors,
		initialState: {
			...window.ampBlockEditor,
		},
	}
);
