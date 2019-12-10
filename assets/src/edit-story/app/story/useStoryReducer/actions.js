/**
 * Internal dependencies
 */
import * as types from './types';

const addPage = ( dispatch ) => ( { properties } ) =>
	dispatch( { type: types.ADD_PAGE, payload: { properties } } );

const deletePage = ( dispatch ) => ( { pageIndex } ) =>
	dispatch( { type: types.DELETE_PAGE, payload: { pageIndex } } );

const deleteCurrentPage = ( dispatch ) => () =>
	dispatch( { type: types.DELETE_PAGE, payload: { pageIndex: null } } );

const updatePageProperties = ( dispatch ) => ( { pageIndex, properties } ) =>
	dispatch( { type: types.UPDATE_PAGE, payload: { pageIndex, properties } } );

const updateCurrentPageProperties = ( dispatch ) => ( { properties } ) =>
	dispatch( { type: types.UPDATE_PAGE, payload: { pageIndex: null, properties } } );

const duplicatePage = ( dispatch ) => ( { pageIndex } ) =>
	dispatch( { type: types.DUPLICATE_PAGE, payload: { pageIndex } } );

const duplicateCurrentPage = ( dispatch ) => () =>
	dispatch( { type: types.DUPLICATE_PAGE, payload: { pageIndex: null } } );

const movePage = ( dispatch ) => ( { pageIndex, position } ) =>
	dispatch( { type: types.MOVE_PAGE, payload: { pageIndex, position } } );

const setCurrentPage = ( dispatch ) => ( { pageIndex } ) =>
	dispatch( { type: types.SET_CURRENT_PAGE, payload: { pageIndex } } );

const addElements = ( dispatch ) => ( { elements } ) =>
	dispatch( { type: types.ADD_ELEMENTS, payload: { pageIndex: null, elements } } );

const addElement = ( dispatch ) => ( { element } ) =>
	dispatch( { type: types.ADD_ELEMENTS, payload: { pageIndex: null, elements: [ element ] } } );

const addElementsToPage = ( dispatch ) => ( { pageIndex, elements } ) =>
	dispatch( { type: types.ADD_ELEMENTS, payload: { pageIndex, elements } } );

const addElementToPage = ( dispatch ) => ( { pageIndex, element } ) =>
	dispatch( { type: types.ADD_ELEMENTS, payload: { pageIndex, elements: [ element ] } } );

const deleteElementsById = ( dispatch ) => ( { elementIds } ) =>
	dispatch( { type: types.DELETE_ELEMENTS, payload: { elementIds } } );

const deleteSelectedElements = ( dispatch ) => () =>
	dispatch( { type: types.DELETE_ELEMENTS, payload: { elementIds: null } } );

const deleteElementById = ( dispatch ) => ( { elementId } ) =>
	dispatch( { type: types.DELETE_ELEMENTS, payload: { elementIds: [ elementId ] } } );

const duplicateElementsById = ( dispatch ) => ( { elementIds } ) =>
	dispatch( { type: types.DUPLICATE_ELEMENTS, payload: { elementIds } } );

const updateElementsById = ( dispatch ) => ( { elementIds, properties } ) =>
	dispatch( { type: types.UPDATE_ELEMENTS, payload: { elementIds, properties } } );

const updateElementById = ( dispatch ) => ( { elementId, properties } ) =>
	dispatch( { type: types.UPDATE_ELEMENTS, payload: { elementIds: [ elementId ], properties } } );

const updateSelectedElements = ( dispatch ) => ( { properties } ) =>
	dispatch( { type: types.UPDATE_ELEMENTS, payload: { elementIds: null, properties } } );

const moveElement = ( dispatch ) => ( { elementId, position } ) =>
	dispatch( { type: types.MOVE_ELEMENT, payload: { pageIndex: null, elementId, position } } );

const moveSelectedElement = ( dispatch ) => ( { position } ) =>
	dispatch( { type: types.MOVE_ELEMENT, payload: { pageIndex: null, elementId: null, position } } );

const moveElementOnPage = ( dispatch ) => ( { pageIndex, elementId, position } ) =>
	dispatch( { type: types.MOVE_ELEMENT, payload: { pageIndex, elementId, position } } );

const setSelectedElementsById = ( dispatch ) => ( { selectedElementIds } ) =>
	dispatch( { type: types.SET_SELECTED_ELEMENTS, payload: { selectedElementIds } } );

const clearSelection = ( dispatch ) => () =>
	dispatch( { type: types.SET_SELECTED_ELEMENTS, payload: { selectedElementIds: [] } } );

const addElementToSelection = ( dispatch ) => ( { elementId } ) =>
	dispatch( { type: types.SELECT_ELEMENT, payload: { elementId } } );

const removeElementFromSelection = ( dispatch ) => ( { elementId } ) =>
	dispatch( { type: types.UNSELECT_ELEMENT, payload: { elementId } } );

const toggleElementInSelection = ( dispatch ) => ( { elementId } ) =>
	dispatch( { type: types.TOGGLE_ELEMENT_IN_SELECTION, payload: { elementId } } );

export default {
	addPage,
	deletePage,
	deleteCurrentPage,
	updatePageProperties,
	updateCurrentPageProperties,
	duplicatePage,
	duplicateCurrentPage,
	movePage,
	setCurrentPage,
	addElements,
	addElement,
	addElementsToPage,
	addElementToPage,
	deleteElementsById,
	deleteElementById,
	deleteSelectedElements,
	duplicateElementsById,
	updateElementsById,
	updateElementById,
	updateSelectedElements,
	moveElement,
	moveSelectedElement
	moveElementOnPage,
	setSelectedElementsById,
	clearSelection,
	addElementToSelection,
	removeElementFromSelection,
	toggleElementInSelection,
};
