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
import { useFont } from '../app';
import { MIN_FONT_SIZE, MAX_FONT_SIZE } from '../constants';
import { SimplePanel } from './panel';
import { InputGroup, SelectMenu } from './components';
import getCommonValue from './utils/getCommonValue';

function FontPanel( { selectedElements, onSetProperties } ) {
	const fontFamily = getCommonValue( selectedElements, 'fontFamily' );
	const fontSize = getCommonValue( selectedElements, 'fontSize' );
	const fontWeight = getCommonValue( selectedElements, 'fontWeight' );
	const fontWeights = getCommonValue( selectedElements, 'fontWeights' );
	const fontStyle = getCommonValue( selectedElements, 'fontStyle' );
	const fontFallback = getCommonValue( selectedElements, 'fontFallback' );

	const { state: { fonts }, actions: { getFontWeight, getFontFallback } } = useFont();
	const [ state, setState ] = useState( { fontFamily, fontStyle, fontSize, fontWeight, fontFallback, fontWeights } );
	useEffect( () => {
		const currentFontWeights = getFontWeight( fontFamily );
		const currentFontFallback = getFontFallback( fontFamily );
		setState( { fontFamily, fontStyle, fontSize, fontWeight, fontWeights: currentFontWeights, fontFallback: currentFontFallback } );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ fontFamily, fontStyle, fontSize, fontWeight, getFontWeight ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	const fontStyles = [
		{ name: __( 'Normal', 'amp' ), slug: 'normal', thisValue: 'normal' },
		{ name: __( 'Italic', 'amp' ), slug: 'italic', thisValue: 'italic' },
	];

	return (
		<SimplePanel title={ __( 'Font', 'amp' ) } onSubmit={ handleSubmit }>
			{ fonts && <SelectMenu
				label={ __( 'Font family', 'amp' ) }
				options={ fonts }
				value={ state.fontFamily }
				isMultiple={ fontFamily === '' }
				onChange={ ( value ) => {
					const currentFontWeights = getFontWeight( value );
					const currentFontFallback = getFontFallback( value );
					const fontWeightsArr = currentFontWeights.map( ( { thisValue } ) => thisValue );
					const newFontWeight = ( fontWeightsArr && fontWeightsArr.includes( state.fontWeight ) ) ? state.fontWeight : 400;
					setState( { ...state, fontFamily: value, fontWeight: parseInt( newFontWeight ), fontWeights: currentFontWeights, fontFallback: currentFontFallback } );
				} }
			/> }
			<SelectMenu
				label={ __( 'Font style', 'amp' ) }
				options={ fontStyles }
				isMultiple={ fontStyle === '' }
				value={ state.fontStyle }
				onChange={ ( value ) => setState( { ...state, fontStyle: value } ) }
			/>
			{ state.fontWeights && <SelectMenu
				label={ __( 'Font weight', 'amp' ) }
				options={ state.fontWeights }
				value={ state.fontWeight }
				isMultiple={ fontWeight === '' }
				onChange={ ( value ) => setState( { ...state, fontWeight: parseInt( value ) } ) }
			/> }
			<InputGroup
				type="number"
				label={ __( 'Font size', 'amp' ) }
				value={ state.fontSize }
				isMultiple={ fontSize === '' }
				postfix={ 'px' }
				min={ MIN_FONT_SIZE }
				max={ MAX_FONT_SIZE }
				onChange={ ( value ) => setState( { ...state, fontSize: parseInt( value ) } ) }
			/>
		</SimplePanel>
	);
}

FontPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default FontPanel;
