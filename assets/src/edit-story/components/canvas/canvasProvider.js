/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import useFunctionState from '../../utils/useFunctionState';
import useEditingElement from './useEditingElement';
import Context from './context';

function CanvasProvider( { children } ) {
	const {
		editingElement,
		editingElementState,
		setEditingElementWithState,
		setEditingElementWithoutState,
		clearEditing,
		addNodeForElement,
	} = useEditingElement();

	const [
		backgroundClickHandler,
		setBackgroundClickHandler,
		clearBackgroundClickHandler,
	] = useFunctionState();

	const state = {
		state: {
			editingElement,
			editingElementState,
			isEditing: Boolean( editingElement ),
			backgroundClickHandler,
		},
		actions: {
			addNodeForElement,
			setBackgroundClickHandler,
			clearBackgroundClickHandler,
			setEditingElement: setEditingElementWithoutState,
			setEditingElementWithState,
			clearEditing,
		},
	};

	return (
		<Context.Provider value={ state }>
			{ children }
		</Context.Provider>
	);
}

CanvasProvider.propTypes = {
	children: PropTypes.oneOfType( [
		PropTypes.arrayOf( PropTypes.node ),
		PropTypes.node,
	] ).isRequired,
};

export default CanvasProvider;
