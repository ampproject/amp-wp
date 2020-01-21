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
	const align = getCommonValue( selectedElements, 'align' );
	const letterSpacing = getCommonValue( selectedElements, 'letterSpacing' );
	const lineHeight = getCommonValue( selectedElements, 'lineHeight' );
	const [ state, setState ] = useState( { align, letterSpacing, lineHeight } );
	useEffect( () => {
		setState( { align, letterSpacing, lineHeight } );
	}, [ align, letterSpacing, lineHeight ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	const alignmentOptions = [
		{
			name: __( 'Default', 'amp' ),
			slug: 'default',
		},
		{
			name: __( 'Left', 'amp' ),
			slug: 'left',
		},
		{
			name: __( 'Right', 'amp' ),
			slug: 'right',
		},
		{
			name: __( 'Center', 'amp' ),
			slug: 'center',
		},
		{
			name: __( 'Justify', 'amp' ),
			slug: 'justify',
		},
	];

	console.log( state );
	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ __( 'Style', 'amp' ) }
			</Title>
			<SelectMenu
				label={ __( 'Alignment', 'amp' ) }
				options={ alignmentOptions }
				isMultiple={ '' === align }
				value={ state.align ? state.align : 'default' }
				onChange={ ( value ) => setState( { ...state, align: value } ) }
			/>
			<InputGroup
				label={ __( 'Line height', 'amp' ) }
				value={ typeof state.lineHeight === 'number' ? state.lineHeight : '(auto)' }
				isMultiple={ '' === lineHeight }
				onChange={ ( value ) => setState( { ...state, lineHeight: isNaN( value ) || value === '' ? '(auto)' : parseFloat( value ) } ) }
			/>
			<InputGroup
				label={ __( 'Letter-spacing', 'amp' ) }
				value={ typeof state.letterSpacing === 'number' ? state.letterSpacing : '(auto)' }
				isMultiple={ '' === letterSpacing }
				onChange={ ( value ) => setState( { ...state, letterSpacing: isNaN( value ) || value === '' ? '(auto)' : value } ) }
				postfix="em"
			/>
		</Panel>
	);
}

StylePanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default StylePanel;
