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
		<Panel>
			<PanelTitle>
				{ 'Color' }
			</PanelTitle>
			<PanelContent onSubmit={ handleSubmit }>
				<InputGroup
					type="color"
					label="Color"
					value={ state.color }
					isMultiple={ color === '' }
					onChange={ ( value ) => setState( { ...state, color: value } ) }
				/>
			</PanelContent>
		</Panel>
	);
}

ColorPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default ColorPanel;
