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
import { Panel, PanelTitle, PanelContent, InputGroup, getCommonValue } from './panel';

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
		<Panel onSubmit={ handleSubmit }>
			<PanelTitle>
				{ 'Rotation' }
			</PanelTitle>
			<PanelContent>
				<InputGroup
					label="Rotation angle"
					value={ state.rotationAngle }
					isMultiple={ rotationAngle === '' }
					onChange={ ( value ) => setState( { ...state, rotationAngle: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
					postfix="deg"
					disabled={ isFullbleed }
				/>
			</PanelContent>
		</Panel>
	);
}

RotationPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default RotationPanel;
