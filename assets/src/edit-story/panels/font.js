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

	const fontNames = {
		100: 'Hairline',
		200: 'Thin',
		300: 'Light',
		400: 'Normal',
		500: 'Medium',
		600: 'Semi bold',
		700: 'Bold',
		800: 'Extra bold',
		900: 'Super bold',
	};

	const defaultFontWeights = [
		{ name: fontNames[ 400 ], slug: 400, thisValue: 400 },
	];
	const { state: { fonts }, actions: { getFontByName } } = useFont();
	const [ state, setState ] = useState( { fontFamily, fontStyle, fontSize, fontWeight, fontWeights: defaultFontWeights } );
	useEffect( () => {
		const currentFont = getFontByName( fontFamily );
		let fontWeights = defaultFontWeights;
		if ( currentFont ) {
			const { weights } = currentFont;
			if ( weights ) {
				fontWeights = weights.map( ( weight ) => ( { name: fontNames[ weight ], slug: weight, thisValue: weight } ) );
			}
		}
		setState( { fontFamily, fontStyle, fontSize, fontWeight, fontWeights } );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ fontFamily, fontStyle, fontSize, fontWeight, getFontByName ] );
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
					setState( { ...state, fontFamily: value, fontWeight: 400 } );
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
