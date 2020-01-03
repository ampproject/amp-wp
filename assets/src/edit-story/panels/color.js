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
import { Panel, Title, InputGroup, getCommonValue } from './shared';

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
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Color' }
			</Title>
			<InputGroup
				type="text"
				label="Color"
				value={ state.color }
				isMultiple={ color === '' }
				onChange={ ( value ) => setState( { ...state, color: value } ) }
			/>
		</Panel>
	);
}

ColorPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default ColorPanel;
