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
		<SimplePanel name="position" title={ __( 'Position', 'amp' ) } onSubmit={ handleSubmit }>
			<InputGroup
				label={ _x( 'X', 'The X axis', 'amp' ) }
				value={ state.x }
				isMultiple={ x === '' }
				onChange={ ( value ) => setState( { ...state, x: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix={ _x( 'px', 'pixels, the measurement of size', 'amp' ) }
				disabled={ isFullbleed }
			/>
			<InputGroup
				label={ _x( 'Y', 'The Y axis', 'amp' ) }
				value={ state.y }
				isMultiple={ y === '' }
				onChange={ ( value ) => setState( { ...state, y: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix={ _x( 'px', 'pixels, the measurement of size', 'amp' ) }
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
