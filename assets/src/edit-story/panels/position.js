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

function PositionPanel( { selectedElements, onSetProperties } ) {
	const x = getCommonValue( selectedElements, 'x' );
	const y = getCommonValue( selectedElements, 'y' );
	const isFullbleed = getCommonValue( selectedElements, 'isFullbleed' );
	const [ state, setState ] = useState( { x, y } );
	useEffect( () => {
		setState( { x, y } );
	}, [ x, y ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};
	return (
		<SimplePanel title="Position" onSubmit={ handleSubmit }>
			<InputGroup
				label="X"
				value={ state.x }
				isMultiple={ x === '' }
				onChange={ ( value ) => setState( { ...state, x: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix="px"
				disabled={ isFullbleed }
			/>
			<InputGroup
				label="Y"
				value={ state.y }
				isMultiple={ y === '' }
				onChange={ ( value ) => setState( { ...state, y: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix="px"
				disabled={ isFullbleed }
			/>
		</SimplePanel>
	);
}

PositionPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default PositionPanel;
