/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SimplePanel } from './panel';
import { InputGroup } from './components';
import getCommonValue from './utils/getCommonValue';

function ScalePanel( { selectedElements, onSetProperties } ) {
	const scale = getCommonValue( selectedElements, 'scale' );
	const focalX = getCommonValue( selectedElements, 'focalX' );
	const focalY = getCommonValue( selectedElements, 'focalY' );
	const [ state, setState ] = useState( { scale, focalX, focalY } );
	useEffect( () => {
		setState( { scale, focalX, focalY } );
	}, [ scale, focalX, focalY ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( {
			scale: typeof state.scale === 'string' ? null : state.scale,
			focalX: typeof state.focalX === 'string' ? null : state.focalX,
			focalY: typeof state.focalY === 'string' ? null : state.focalY,
		} );
		evt.preventDefault();
	};
	return (
		<SimplePanel title={ __( 'Image actual size', 'amp' ) } onSubmit={ handleSubmit }>
			<InputGroup
				label={ __( 'Scale', 'amp' ) }
				value={ typeof state.scale === 'number' ? state.scale : '(auto)' }
				isMultiple={ scale === '' }
				onChange={ ( value ) => setState( { ...state, scale: isNaN( value ) || value === '' ? '(auto)' : parseFloat( value ) } ) }
				postfix={ _x( '%', 'Percentage', 'amp' ) }
			/>
			<InputGroup
				label={ __( 'Focal X', 'amp' ) }
				value={ typeof state.focalX === 'number' ? state.focalX : '(auto)' }
				isMultiple={ focalX === '' }
				onChange={ ( value ) => setState( { ...state, focalX: isNaN( value ) || value === '' ? '(auto)' : parseFloat( value ) } ) }
				postfix={ _x( '%', 'Percentage', 'amp' ) }
			/>
			<InputGroup
				label={ __( 'Focal Y', 'amp' ) }
				value={ typeof state.focalY === 'number' ? state.focalY : '(auto)' }
				isMultiple={ focalY === '' }
				onChange={ ( value ) => setState( { ...state, focalY: isNaN( value ) || value === '' ? '(auto)' : parseFloat( value ) } ) }
				postfix={ _x( '%', 'Percentage', 'amp' ) }
			/>
		</SimplePanel>
	);
}

ScalePanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default ScalePanel;
