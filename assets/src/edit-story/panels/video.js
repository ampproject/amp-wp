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
import { Panel, Title, InputGroup, getCommonValue } from './shared';

function VideoPanel( { selectedElements, onSetProperties } ) {
	const videoCaption = getCommonValue( selectedElements, 'videoCaption' );
	const ampAriaLabel = getCommonValue( selectedElements, 'ampAriaLabel' );
	const [ state, setState ] = useState( { videoCaption, ampAriaLabel } );
	useEffect( () => {
		setState( { videoCaption, ampAriaLabel } );
	}, [ videoCaption, ampAriaLabel ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Video options' }
			</Title>
			<InputGroup
				label="Video Caption"
				value={ state.videoCaption }
				isMultiple={ false }
				onChange={ ( value ) => setState( { ...state, videoCaption: value } ) }
			/>
			<InputGroup
				label="Assistive Text"
				value={ state.ampAriaLabel }
				isMultiple={ false }
				onChange={ ( value ) => setState( { ...state, ampAriaLabel: value } ) }
			/>
		</Panel>
	);
}

VideoPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default VideoPanel;
