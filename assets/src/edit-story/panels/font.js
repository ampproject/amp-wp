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
import { useFont } from '../app';
import { Panel, Title, InputGroup, getCommonValue, SelectMenu } from './shared';

function FontPanel( { selectedElements, onSetProperties } ) {
	const fontFamily = getCommonValue( selectedElements, 'fontFamily' );
	const fontSize = getCommonValue( selectedElements, 'fontSize' );
	const fontWeight = getCommonValue( selectedElements, 'fontWeight' );
	const fontStyle = getCommonValue( selectedElements, 'fontStyle' );

	const { state: { fonts }, actions: { getFontWeight } } = useFont();
	const [ state, setState ] = useState( { fontFamily, fontStyle, fontSize, fontWeight, fontWeights: [] } );
	useEffect( () => {
		const fontWeights = getFontWeight( fontFamily );
		setState( { fontFamily, fontStyle, fontSize, fontWeight, fontWeights } );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ fontFamily, fontStyle, fontSize, fontWeight, getFontWeight ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	const fontStyles = [
		{ name: 'Normal', slug: 'normal', thisValue: 'normal' },
		{ name: 'Italic', slug: 'italic', thisValue: 'italic' },
	];

	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Font' }
			</Title>
			<SelectMenu
				label="Font family"
				options={ fonts }
				value={ state.fontFamily }
				onChange={ ( value ) => {
					const fontWeights = getFontWeight( value );
					const fontWeightsArr = fontWeights.map( ( { thisValue } ) => thisValue );
					const newFontWeight = ( fontWeightsArr && fontWeightsArr.includes( state.fontWeight ) ) ? state.fontWeight : 400;
					setState( { ...state, fontWeights, fontFamily: value, fontWeight: parseInt( newFontWeight ) } );
				} }
			/>
			<SelectMenu
				label="Font style"
				options={ fontStyles }
				value={ state.fontStyle }
				onChange={ ( value ) => setState( { ...state, fontStyle: value } ) }
			/>
			<SelectMenu
				label="Font weight"
				options={ state.fontWeights }
				value={ state.fontWeight }
				onChange={ ( value ) => setState( { ...state, fontWeight: parseInt( value ) } ) }
			/>
			<InputGroup
				type="number"
				label="Font size"
				value={ state.fontSize }
				isMultiple={ fontSize === '' }
				onChange={ ( value ) => setState( { ...state, fontSize: parseInt( value ) } ) }
			/>
		</Panel>
	);
}

FontPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default FontPanel;
