/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { SimplePanel } from './panel';
import { InputGroup, getCommonValue } from './elements';

function ColorPanel( { selectedElements, onSetProperties } ) {
	const color = getCommonValue( selectedElements, 'color' );
	const [ state, setState ] = useState( { color } );
	useEffect( () => {
		setState( { color } );
	}, [ color ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};
	return (
		<SimplePanel title="Color" onSubmit={ handleSubmit }>
			<InputGroup
				type="color"
				label="Color"
				value={ state.color }
				isMultiple={ color === '' }
				onChange={ ( value ) => setState( { ...state, color: value } ) }
			/>
		</SimplePanel>
	);
}

ColorPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default ColorPanel;
