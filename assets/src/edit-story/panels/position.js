/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { Panel, Title, InputGroup, getCommonValue } from './shared';

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
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ __( 'Position', 'amp' ) }
			</Title>
			<InputGroup
				label={ __( 'X', 'amp' ) }
				value={ state.x }
				isMultiple={ x === '' }
				onChange={ ( value ) => setState( { ...state, x: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix={ __( 'px', 'amp' ) }
				disabled={ isFullbleed }
			/>
			<InputGroup
				label={ __( 'Y', 'amp' ) }
				value={ state.y }
				isMultiple={ y === '' }
				onChange={ ( value ) => setState( { ...state, y: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix={ __( 'px', 'amp' ) }
				disabled={ isFullbleed }
			/>
		</Panel>
	);
}

PositionPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default PositionPanel;
