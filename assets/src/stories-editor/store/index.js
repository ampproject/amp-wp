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
import applyMiddlewares from './middlewares';

/**
 * Module Constants
 */
const MODULE_KEY = 'amp/story';

const store = registerStore(
	MODULE_KEY,
	{
		reducer,
		selectors,
		actions,
		initialState: {
			animations: {
				animationOrder: {},
				isPlayingAnimation: false,
			},
			editorSettings: {
				...window.ampStoriesEditorSettings,
			},
			blocks: {
				order: [],
				isReordering: false,
			},
		},
	}
);

applyMiddlewares( store );

export default store;
