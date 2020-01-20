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
import { ActionButton, Panel, PanelTitle, PanelContent, getCommonValue } from './panel';

function FullbleedPanel( { selectedElements, onSetProperties } ) {
	// The x/y/w/h/r are kept unchanged so that toggling fullbleed will return
	// the element to the previous non-fullbleed position/size.
	const isFullbleed = getCommonValue( selectedElements, 'isFullbleed' );
	const [ state, setState ] = useState( { isFullbleed } );
	useEffect( () => {
		setState( { isFullbleed } );
	}, [ isFullbleed ] );
	const handleClick = ( ) => {
		const newState = { isFullbleed: ! state.isFullbleed };
		setState( newState );
		onSetProperties( newState );
	};
	return (
		<Panel onSubmit={ ( event ) => event.preventDefault() }>
			<PanelTitle>
				{ 'Fullbleed' }
			</PanelTitle>
			<PanelContent>
				<ActionButton onClick={ handleClick }>
					{ state.isFullbleed ? 'Unset as fullbleed' : 'Set as fullbleed' }
				</ActionButton>
			</PanelContent>
		</Panel>
	);
}

FullbleedPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default FullbleedPanel;
