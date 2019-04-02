/**
 * WordPress dependencies
 */
import { registerStore } from '@wordpress/data';

/**
 * Internal dependencies
 */
import reducer from './reducer';
import * as actions from './actions';
import * as selectors from './selectors';

/**
 * Module Constants
 */
const MODULE_KEY = 'amp/story';

export default registerStore(
	MODULE_KEY,
	{
		reducer,
		selectors,
		actions,
		initialState: {
			animations: {},
			blocks: {
				order: [],
				isReordering: false,
			},
		},
	}
);
