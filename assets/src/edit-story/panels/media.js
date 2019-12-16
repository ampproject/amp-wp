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
import { Panel, Title, Checkbox, getCommonValue } from './shared';

function MediaPanel( { selectedElements, onSetProperties } ) {
	const loop = getCommonValue( selectedElements, 'loop' );
	const [ state, setState ] = useState( { loop } );
	useEffect( () => {
		setState( { loop } );
	}, [ loop ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Media' }
			</Title>
			<Checkbox
				label="Loop"
				checked={ state.loop }
				onChange={ ( checked ) => setState( { ...state, loop: checked } ) }
			/>
		</Panel>
	);
}

MediaPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default MediaPanel;
