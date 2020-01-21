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
import { ActionButton, Panel, Title, getCommonValue } from './shared';

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
			<Title>
				{ __( 'Fullbleed', 'amp' ) }
			</Title>
			<ActionButton onClick={ handleClick }>
				{ state.isFullbleed ? __( 'Unset as fullbleed', 'amp' ) : __( 'Set as fullbleed', 'amp' ) }
			</ActionButton>
		</Panel>
	);
}

FullbleedPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default FullbleedPanel;
