/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { __, _x } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { SimplePanel } from './panel';
import { SelectMenu, InputGroup, getCommonValue } from './elements';

function StylePanel( { selectedElements, onSetProperties } ) {
	const textAlign = getCommonValue( selectedElements, 'textAlign' );
	const letterSpacing = getCommonValue( selectedElements, 'letterSpacing' );
	const lineHeight = getCommonValue( selectedElements, 'lineHeight' );
	const padding = getCommonValue( selectedElements, 'padding' );
	const [ state, setState ] = useState( { textAlign, letterSpacing, lineHeight, padding } );
	useEffect( () => {
		setState( { textAlign, letterSpacing, lineHeight, padding } );
	}, [ textAlign, letterSpacing, lineHeight, padding ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	const alignmentOptions = [
		{ name: __( 'Default', 'amp' ), slug: '', thisValue: '' },
		{ name: __( 'Left', 'amp' ), slug: 'left', thisValue: 'left' },
		{ name: __( 'Right', 'amp' ), slug: 'right', thisValue: 'right' },
		{ name: __( 'Center', 'amp' ), slug: 'center', thisValue: 'center' },
		{ name: __( 'Justify', 'amp' ), slug: 'justify', thisValue: 'justify' },
	];

	return (
		<SimplePanel title={ __( 'Style', 'amp' ) } onSubmit={ handleSubmit }>
			<SelectMenu
				label={ __( 'Alignment', 'amp' ) }
				options={ alignmentOptions }
				isMultiple={ '' === textAlign }
				value={ state.textAlign }
				onChange={ ( value ) => setState( { ...state, textAlign: value } ) }
			/>
			<InputGroup
				label={ __( 'Line height', 'amp' ) }
				value={ state.lineHeight }
				isMultiple={ '' === lineHeight }
				onChange={ ( value ) => setState( { ...state, lineHeight: isNaN( value ) ? '' : parseFloat( value ) } ) }
				step="0.1"
			/>
			<InputGroup
				label={ __( 'Letter-spacing', 'amp' ) }
				value={ state.letterSpacing }
				isMultiple={ '' === letterSpacing }
				onChange={ ( value ) => setState( { ...state, letterSpacing: isNaN( value ) ? '' : value } ) }
				postfix={ _x( 'em', 'em, the measurement of size', 'amp' ) }
				step="0.1"
			/>
			<InputGroup
				label={ __( 'Padding', 'amp' ) }
				value={ state.padding }
				isMultiple={ '' === padding }
				onChange={ ( value ) => setState( { ...state, padding: isNaN( value ) ? '' : value } ) }
				postfix={ _x( '%', 'Percentage', 'amp' ) }
			/>
		</SimplePanel>
	);
}

StylePanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default StylePanel;
