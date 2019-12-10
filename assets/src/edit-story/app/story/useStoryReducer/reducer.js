/**
 * Internal dependencies
 */
import * as types from './types';
import * as reducers from './reducers';

function reducer( state, { type, payload } ) {
	switch ( type ) {
		case types.ADD_PAGE: {
			return reducers.addPage( state, payload );
		}

		case types.DELETE_PAGE: {
			return reducers.deletePage( state, payload );
		}

		case types.UPDATE_PAGE: {
			return reducers.updatePage( state, payload );
		}

		case types.DUPLICATE_PAGE: {
			return reducers.duplicatePage( state, payload );
		}

		case types.MOVE_PAGE: {
			return reducers.movePage( state, payload );
		}

		case types.SET_CURRENT_PAGE: {
			return reducers.setCurrentPage( state, payload );
		}

		case types.ADD_ELEMENTS: {
			return reducers.addElements( state, payload );
		}

		case types.DELETE_ELEMENTS: {
			return reducers.deleteElements( state, payload );
		}

		case types.UPDATE_ELEMENTS: {
			return reducers.updateElements( state, payload );
		}

		case types.DUPLICATE_ELEMENTS: {
			return reducers.duplicateElements( state, payload );
		}

		case types.MOVE_ELEMENT: {
			return reducers.moveElement( state, payload );
		}

		case types.SET_SELECTED_ELEMENTS: {
			return reducers.setSelectedElements( state, payload );
		}

		case types.SELECT_ELEMENT: {
			return reducers.selectElement( state, payload );
		}

		case types.UNSELECT_ELEMENT: {
			return reducers.unselectElement( state, payload );
		}

		case types.TOGGLE_ELEMENT_IN_SELECTION: {
			return reducers.toggleElement( state, payload );
		}

		case types.RESTORE_FROM_HISTORY: {
			return reducers.restore( state, payload );
		}

		default:
			return state;
	}
}

export default reducer;
