/**
 * WordPress dependencies
 */
import { createReduxStore, register } from '@wordpress/data';

/**
 * Internal dependencies
 */
import * as selectors from './selectors';

/**
 * Module Constants
 */
const MODULE_KEY = 'amp/block-editor';

export default register(
	createReduxStore(MODULE_KEY, {
		reducer: (state) => state,
		selectors,
		initialState: {
			...window.ampBlockEditor,
		},
	})
);
