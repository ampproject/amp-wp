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
import { Panel, PanelTitle, PanelContent } from './panel';
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
		<Panel onSubmit={ handleSubmit }>
			<PanelTitle>
				{ 'Background color' }
			</PanelTitle>
			<PanelContent>
				<InputGroup
					type="text"
					label="Background color"
					value={ state.backgroundColor }
					isMultiple={ backgroundColor === '' }
					onChange={ ( value ) => setState( { ...state, backgroundColor: value } ) }
				/>
			</PanelContent>
		</Panel>
	);
}

BackgroundColorPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default BackgroundColorPanel;
