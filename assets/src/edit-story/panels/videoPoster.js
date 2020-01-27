/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled, { css } from 'styled-components';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import UploadButton from '../components/uploadButton';
import { Panel, Title, getCommonValue } from './shared';

const ButtonCSS = css`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	font-size: 11px;
`;
const Img = styled.img`
	width: 100%;
	max-height: 300px
`;

function VideoPosterPanel( { selectedElements, onSetProperties } ) {
	const featuredMedia = getCommonValue( selectedElements, 'featuredMedia' );
	const featuredMediaSrc = getCommonValue( selectedElements, 'featuredMediaSrc' );
	const [ state, setState ] = useState( { featuredMedia, featuredMediaSrc } );
	useEffect( () => {
		setState( { featuredMedia, featuredMediaSrc } );
	}, [ featuredMedia, featuredMediaSrc ] );

	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};

	const handleChangeImage = ( image ) => {
		const newState = { featuredMedia: image.id, featuredMediaSrc: ( image.sizes && image.sizes.medium ) ? image.sizes.medium.url : image.url };
		setState( { ...state, ...newState } );
		onSetProperties( newState );
	};

	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ __( 'Poster image', 'amp' ) }
			</Title>
			<div>
				{ state.featuredMediaSrc && <Img src={ state.featuredMediaSrc } /> }

				<UploadButton
					onSelect={ handleChangeImage }
					title={ __( 'Select as video poster', 'amp' ) }
					type={ 'image' }
					buttonInsertText={ __( 'Set as video poster', 'amp' ) }
					buttonText={ state.featuredMediaSrc ? __( 'Replace poster image', 'amp' ) : __( 'Set poster image', 'amp' ) }
					buttonCSS={ ButtonCSS }
				/>
			</div>
		</Panel>
	);
}

VideoPosterPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default VideoPosterPanel;
