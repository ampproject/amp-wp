// Manipulate pages.
export const ADD_PAGE = 'ADD_PAGE';
export const UPDATE_PAGE = 'UPDATE_PAGE';
export const DELETE_PAGE = 'DELETE_PAGE';
export const ARRANGE_PAGE = 'ARRANGE_PAGE';

// Manipulate elements on a page.
export const DELETE_ELEMENTS = 'DELETE_ELEMENTS';
export const ADD_ELEMENTS = 'ADD_ELEMENTS';
export const UPDATE_ELEMENTS = 'UPDATE_ELEMENTS';
export const ARRANGE_ELEMENT = 'ARRANGE_ELEMENT';

// Manipulate current page.
export const SET_CURRENT_PAGE = 'SET_CURRENT_PAGE';

// Manipulate list of selected elements.
export const SET_SELECTED_ELEMENTS = 'SET_SELECTED_ELEMENTS';
export const SELECT_ELEMENT = 'SELECT_ELEMENT';
export const UNSELECT_ELEMENT = 'UNSELECT_ELEMENT';
export const TOGGLE_ELEMENT_IN_SELECTION = 'TOGGLE_ELEMENT_IN_SELECTION';

// Manipulate entire internal state.
export const RESTORE = 'RESTORE';

// Reserved property names for pages and elements.
export const PAGE_RESERVED_PROPERTIES = [ 'id', 'elements' ];
export const ELEMENT_RESERVED_PROPERTIES = [ 'id' ];
