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

function PositionPanel( { selectedElements, onSetProperties } ) {
	const x = getCommonValue( selectedElements, 'x' );
	const y = getCommonValue( selectedElements, 'y' );
	const [ state, setState ] = useState( { x, y } );
	useEffect( () => {
		setState( { x, y } );
	}, [ x, y ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};
	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Position' }
			</Title>
			<InputGroup
				label="X"
				value={ state.x }
				isMultiple={ x === '' }
				onChange={ ( value ) => setState( { ...state, x: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix="%"
			/>
			<InputGroup
				label="Y"
				value={ state.y }
				isMultiple={ y === '' }
				onChange={ ( value ) => setState( { ...state, y: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix="%"
			/>
		</Panel>
	);
}

PositionPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default PositionPanel;
