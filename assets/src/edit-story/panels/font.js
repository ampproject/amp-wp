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
import { Panel, Title, Save, InputGroup, getCommonValue } from './shared';

function FontPanel( { selectedElements, onSetProperties } ) {
	const fontFamily = getCommonValue( selectedElements, 'fontFamily' );
	const fontSize = getCommonValue( selectedElements, 'fontSize' );
	const fontWeight = getCommonValue( selectedElements, 'fontWeight' );
	const fontStyle = getCommonValue( selectedElements, 'fontStyle' );
	const [ state, setState ] = useState( { fontFamily, fontStyle, fontSize, fontWeight } );
	useEffect( () => {
		setState( { fontFamily, fontStyle, fontSize, fontWeight } );
	}, [ fontFamily, fontStyle, fontSize, fontWeight ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};
	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Font' }
			</Title>
			<InputGroup
				label="Font family"
				value={ state.fontFamily }
				isMultiple={ fontFamily === '' }
				onChange={ ( value ) => setState( { ...state, fontFamily: value } ) }
			/>
			<InputGroup
				label="Font style"
				value={ state.fontStyle }
				isMultiple={ fontStyle === '' }
				onChange={ ( value ) => setState( { ...state, fontStyle: value } ) }
			/>
			<InputGroup
				label="Font weight"
				value={ state.fontWeight }
				isMultiple={ fontWeight === '' }
				onChange={ ( value ) => setState( { ...state, fontWeight: value } ) }
			/>
			<InputGroup
				label="Font size"
				value={ state.fontSize }
				isMultiple={ fontSize === '' }
				onChange={ ( value ) => setState( { ...state, fontSize: value } ) }
			/>
			<Save>
				{ 'Save' }
			</Save>
		</Panel>
	);
}

FontPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default FontPanel;
