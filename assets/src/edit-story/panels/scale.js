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

function ScalePanel( { selectedElements, onSetProperties } ) {
	const scale = getCommonValue( selectedElements, 'scale' );
	const offsetX = getCommonValue( selectedElements, 'offsetX' );
	const offsetY = getCommonValue( selectedElements, 'offsetY' );
	const [ state, setState ] = useState( { scale, offsetX, offsetY } );
	useEffect( () => {
		setState( { scale, offsetX, offsetY } );
	}, [ scale, offsetX, offsetY ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( {
			scale: typeof state.scale === 'string' ? null : state.scale,
			offsetX: typeof state.offsetX === 'string' ? null : state.offsetX,
			offsetY: typeof state.offsetY === 'string' ? null : state.offsetY,
		} );
		evt.preventDefault();
	};
	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Image actual size' }
			</Title>
			<InputGroup
				label="Scale"
				value={ typeof state.scale === 'number' ? state.scale : '(auto)' }
				isMultiple={ scale === '' }
				onChange={ ( value ) => setState( { ...state, scale: isNaN( value ) || value === '' ? '(auto)' : parseFloat( value ) } ) }
				postfix="%"
			/>
			<InputGroup
				label="Offset X"
				value={ typeof state.offsetX === 'number' ? state.offsetX : '(auto)' }
				isMultiple={ offsetX === '' }
				onChange={ ( value ) => setState( { ...state, offsetX: isNaN( value ) || value === '' ? '(auto)' : parseFloat( value ) } ) }
				postfix="%"
			/>
			<InputGroup
				label="Offset Y"
				value={ typeof state.offsetY === 'number' ? state.offsetY : '(auto)' }
				isMultiple={ offsetY === '' }
				onChange={ ( value ) => setState( { ...state, offsetY: isNaN( value ) || value === '' ? '(auto)' : parseFloat( value ) } ) }
				postfix="%"
			/>
		</Panel>
	);
}

ScalePanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default ScalePanel;
