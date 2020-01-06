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
	const { state: { fonts } } = useFont();
	const [ state, setState ] = useState( { fontFamily, fontStyle, fontSize, fontWeight } );
	useEffect( () => {
		setState( { fontFamily, fontStyle, fontSize, fontWeight } );
	}, [ fontFamily, fontStyle, fontSize, fontWeight ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	const fontWeights = [
		{ name: 'normal', slug: 'normal' },
		{ name: 'bold', slug: 'bold' },
		{ name: 'bolder', slug: 'bolder' },
		{ name: 'lighter', slug: 'lighter' },
	];

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
					setState( { ...state, fontFamily: value, fontWeight: 'normal' } )
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
				options={ fontWeights }
				value={ state.fontWeight }
				onChange={ ( value ) => setState( { ...state, fontWeight: value } ) }
			/>
			<InputGroup
				type="text"
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
