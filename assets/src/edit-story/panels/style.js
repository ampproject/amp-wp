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
import { Panel, Title, InputGroup, getCommonValue, SelectMenu } from './shared';

function StylePanel( { selectedElements, onSetProperties } ) {
	const textAlign = getCommonValue( selectedElements, 'textAlign' );
	const letterSpacing = getCommonValue( selectedElements, 'letterSpacing' );
	const lineHeight = getCommonValue( selectedElements, 'lineHeight' );
	const [ state, setState ] = useState( { textAlign, letterSpacing, lineHeight } );
	useEffect( () => {
		setState( { textAlign, letterSpacing, lineHeight } );
	}, [ textAlign, letterSpacing, lineHeight ] );
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
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ __( 'Style', 'amp' ) }
			</Title>
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
				onChange={ ( value ) => setState( { ...state, lineHeight: isNaN( value ) || value === '' ? '' : parseFloat( value ) } ) }
				step="0.1"
			/>
			<InputGroup
				label={ __( 'Letter-spacing', 'amp' ) }
				value={ state.letterSpacing }
				isMultiple={ '' === letterSpacing }
				onChange={ ( value ) => setState( { ...state, letterSpacing: isNaN( value ) || value === '' ? '' : value } ) }
				postfix="em"
				step="0.1"
			/>
		</Panel>
	);
}

StylePanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default StylePanel;
