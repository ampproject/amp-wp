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

function BackgroundColorPanel( { selectedElements, onSetProperties } ) {
	const backgroundColor = getCommonValue( selectedElements, 'backgroundColor' );
	const [ state, setState ] = useState( { backgroundColor } );
	useEffect( () => {
		setState( { backgroundColor } );
	}, [ backgroundColor ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};
	return (
		<SimplePanel title={ __( 'Background color', 'amp' ) } onSubmit={ handleSubmit }>
			<InputGroup
				type="color"
				label={ __( 'Background color', 'amp' ) }
				value={ state.backgroundColor }
				isMultiple={ backgroundColor === '' }
				onChange={ ( value ) => setState( { ...state, backgroundColor: value } ) }
			/>
		</SimplePanel>
	);
}

BackgroundColorPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default BackgroundColorPanel;
