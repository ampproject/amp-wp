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
import { SimplePanel } from './panel';
import { InputGroup } from './components';
import getCommonValue from './utils/getCommonValue';

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
		<SimplePanel name="color" title={ __( 'Color', 'amp' ) } onSubmit={ handleSubmit }>
			<InputGroup
				type="color"
				label={ __( 'Color', 'amp' ) }
				value={ state.color }
				isMultiple={ color === '' }
				onChange={ ( value ) => setState( { ...state, color: value } ) }
			/>
		</SimplePanel>
	);
}

ColorPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default ColorPanel;
