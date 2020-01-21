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
import { InputGroup, getCommonValue } from './elements';

function RotationPanel( { selectedElements, onSetProperties } ) {
	const rotationAngle = getCommonValue( selectedElements, 'rotationAngle' );
	const isFullbleed = getCommonValue( selectedElements, 'isFullbleed' );
	const [ state, setState ] = useState( { rotationAngle } );
	useEffect( () => {
		setState( { rotationAngle } );
	}, [ rotationAngle ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};
	return (
		<SimplePanel title={ __( 'Rotation', 'amp' ) } onSubmit={ handleSubmit }>
			<InputGroup
				label={ __( 'Rotation angle', 'amp' ) }
				value={ state.rotationAngle }
				isMultiple={ rotationAngle === '' }
				onChange={ ( value ) => setState( { ...state, rotationAngle: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				postfix={ _x( 'deg', 'Degrees, 0 - 360. ', 'amp' ) }
				disabled={ isFullbleed }
			/>
		</SimplePanel>
	);
}

RotationPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default RotationPanel;
