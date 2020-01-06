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
	const defaultFontWeights = [
		{ name: 400, slug: 400 },
	];
	const { state: { fonts }, actions: { getFont } } = useFont();
	const [ state, setState ] = useState( { fontFamily, fontStyle, fontSize, fontWeight, fontWeights: defaultFontWeights } );
	useEffect( () => {
		const currentFont = getFont( fontFamily );
		let fontWeights = defaultFontWeights;
		if ( currentFont ) {
			const { weights } = currentFont;
			if ( weights ) {
				fontWeights = weights.map( ( weight ) => ( { name: weight, slug: weight } ) );
			}
		}
		setState( { fontFamily, fontStyle, fontSize, fontWeight, fontWeights } );
		// eslint-disable-next-line react-hooks/exhaustive-deps
	}, [ fontFamily, fontStyle, fontSize, fontWeight, getFont ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	const fontStyles = [
		{ name: 'normal', slug: 'normal' },
		{ name: 'italic', slug: 'italic' },
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
				onChange={ ( value ) => setState( { ...state, fontWeight: value } ) }
			/>
			<InputGroup
				type="number"
				label="Font size"
				value={ state.fontSize }
				isMultiple={ fontSize === '' }
				onChange={ ( value ) => setState( { ...state, fontSize: value } ) }
			/>
		</Panel>
	);
}

FontPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default FontPanel;
