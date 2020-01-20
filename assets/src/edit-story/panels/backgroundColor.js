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
import { SimplePanel } from './panel';
import { InputGroup, getCommonValue } from './elements';

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
		<SimplePanel title="Background color" onSubmit={ handleSubmit }>
			<InputGroup
				type="color"
				label="Background color"
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
