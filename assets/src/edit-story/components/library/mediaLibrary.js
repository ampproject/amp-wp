/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import UploadButton from '../uploadButton';
import useLibrary from './useLibrary';

const Image = styled.img`
	height: 150px;
	width: 48%;
	padding: 3px;
	margin: 3px;
	border: 1px solid white;
`;

const Title = styled.h3`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	margin: 0px;
	font-size: 19px;
	line-height: 20px;
	line-height: 1.4em;
	flex: 2 0 0;
`;

const Header = styled.div`
	display: flex;
	margin: 0px 4px 10px;
`;

const Message = styled.div`
	 color: ${ ( { theme } ) => theme.colors.fg.v1 };
	font-size: 19px;
`;

function MediaLibrary( { onInsert } ) {
	const {
		state: { media, isMediaLoading, isMediaLoaded, mediaType },
		actions: { loadMedia, setIsMediaLoading, setIsMediaLoaded },
	} = useLibrary();

	useEffect( loadMedia );

	const onClose = () => {
		setIsMediaLoading( false );
		setIsMediaLoaded( false );
	};

	const onSelect = ( { url } ) => {
		onInsert( 'image', {
			src: url,
			width: 100,
			height: 100,
			x: 5,
			y: 5,
			rotationAngle: 0,
		} );
	};

	return (
		<div>
			<Header>
				<Title>
					{ 'Media' }
					{ ( ! isMediaLoaded || isMediaLoading ) &&
						<Spinner />
					}
				</Title>
				<UploadButton mediaType={ mediaType } onClose={ onClose } onSelect={ onSelect } />
			</Header>

			{ ( isMediaLoaded && ! media.length ) ? (
				<Message>
					{ 'No media found' }
				</Message>
			) : (

				media.map( ( { src } ) => (
					<Image
						key={ src }
						src={ src }
						width={ 150 }
						height={ 150 }
						loading={ 'lazy' }
						onClick={ () => onInsert( 'image', {
							src,
							width: 200,
							height: 100,
							x: 5,
							y: 5,
							rotationAngle: 0,
						} ) }
					/>
				) ) )
			}
		</div>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;
