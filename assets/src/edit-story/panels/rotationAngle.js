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
import { Panel, PanelTitle, PanelContent } from './panel';
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
		<Panel>
			<PanelTitle>
				{ 'Rotation' }
			</PanelTitle>
			<PanelContent onSubmit={ handleSubmit }>
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
