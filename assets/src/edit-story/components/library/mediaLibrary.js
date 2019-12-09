/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { Spinner, Dashicon } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useLibrary from './useLibrary';

const Container = styled.div`
	display: grid;
    grid-gap: 10px;
    grid-template-columns: 1fr 1fr;
`;

const Column = styled.div`

`;
const Image = styled.img`
	width: 100%;
	border-radius: 10px;
	margin-bottom: 10px;
`;

const Video = styled.video`
	width: 100%;
	border-radius: 10px;
	margin-bottom: 10px;
`;

const Title = styled.h3`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	margin: 0px;
	font-size: 19px;
	line-height: 20px;
	line-height: 1.4em;
	flex: 3 0 0;
`;

const Button = styled.button`
	 background: none;
	 color: #ffffff;
	 padding: 5px;
	 font-weight: bold;
	 flex: 1 0 0;
	 text-align: center;
	 border: 1px solid #616877;;
	 border-radius: 3px;
`;

const Header = styled.div`
	display: flex;
	margin: 0px 0px 25px;
`;

const Message = styled.div`
	color: #ffffff;
	font-size: 19px;
`;

const FilterButtons = styled.div`
	border-bottom:  2px solid #606877;
	padding: 18px 0px;
	margin: 10px 0px 15px;
`;

const FilterButton = styled.button`
	border: 0px;
	background: none;
	padding: 0px;
	margin: 0px 28px 0px 0px;
	color: rgba(255, 255, 255, 0.34);
	font-size: 13px;
	${ ( { active } ) => active && `
    	color: #FFFFFF;
  	` }
`;

const Search = styled.input`
	width: 100%;
	background: #616877 !important;
	border-color: #616877 !important;
	color: #DADADA !important;
	padding: 2px 10px 2px 33px !important;
	&::placeholder {
	  color: #DADADA;
	}
`;

const SUPPORTED_IMAGE_TYPES = [
	'image/png',
	'image/jpeg',
	'image/jpg',
	'image/gif',
];

const SUPPORTED_VIDEO_TYPES = [
	'video/mp4',
];

const FILTERS = [
	{ filter: '', name: 'All' },
	{ filter: 'image', name: 'Images' },
	{ filter: 'video', name: 'Video' },
];

const DEFAULT_WIDTH = 150;

function MediaLibrary( { onInsert } ) {
	const {
		state: { media, isMediaLoading, isMediaLoaded, mediaType, searchTerm },
		actions: { loadMedia, setIsMediaLoading, setIsMediaLoaded, setMediaType, setSearchTerm },
	} = useLibrary();

	useEffect( () => {
		loadMedia();
		// Work around that forces default tab as upload tab.
		wp.media.controller.Library.prototype.defaults.contentUserSetting = false;
	} );

	const mediaPicker = () => {
		// Create the media frame.
		const fileFrame = wp.media( {
			title: 'Upload to Story',
			button: {
				text: 'Insert into page',
			},
			multiple: false,
		} );
		let attachment;

		// When an image is selected, run a callback.
		fileFrame.on( 'select', () => {
			attachment = fileFrame.state().get( 'selection' ).first().toJSON();
			const { url: src, mime: mimeType, width: oWidth, height: oHeight } = attachment;
			const mediaEl = { src, mimeType, oWidth, oHeight };
			insertMediaElement( mediaEl, DEFAULT_WIDTH );
		} );

		fileFrame.on( 'close', () => {
			setMediaType( '' );
			setSearchTerm( '' );
			setIsMediaLoading( false );
			setIsMediaLoaded( false );
		} );

		// Finally, open the modal
		fileFrame.open();
	};

	const uploadMedia = () => {
		mediaPicker();
	};

	const isEven = ( n ) => {
		return n % 2 === 0;
	};

	const getRelativeHeight = ( oWidth, oHeight, width ) => {
		const racio = oWidth / width;
		const height = Math.round( oHeight / racio );
		return height;
	};

	const insertMediaElement = ( attachment, width ) => {
		const { src, mimeType, oWidth, oHeight } = attachment;
		const height = getRelativeHeight( oWidth, oHeight, DEFAULT_WIDTH );
		if ( SUPPORTED_IMAGE_TYPES.includes( mimeType ) ) {
			onInsert( 'image', {
				src,
				width,
				height,
				x: 5,
				y: 5,
				rotationAngle: 0,
			} );
		} else if ( SUPPORTED_VIDEO_TYPES.includes( mimeType ) ) {
			onInsert( 'video', {
				src,
				width,
				height,
				mimeType,
				x: 5,
				y: 5,
				rotationAngle: 0,
			} );
		}
	};

	const getMediaElement = ( mediaEl, width ) => {
		const { src, oWidth, oHeight, mimeType } = mediaEl;
		const height = getRelativeHeight( oWidth, oHeight, width );
		if ( SUPPORTED_IMAGE_TYPES.includes( mimeType ) ) {
			return ( <Image
				key={ src }
				src={ src }
				width={ width }
				height={ height }
				loading={ 'lazy' }
				onClick={ () => insertMediaElement( mediaEl, width ) }
			/> );
		} else if ( SUPPORTED_VIDEO_TYPES.includes( mimeType ) ) {
			/* eslint-disable react/jsx-closing-tag-location */
			return ( <Video
				key={ src }
				controls={ true }
				width={ width }
				height={ height }
				onClick={ () => insertMediaElement( mediaEl, width ) }
			>
				<source src={ src } type={ mimeType } />
			</Video> );
			/* eslint-enable react/jsx-closing-tag-location */
		}
		return null;
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
				<Button onClick={ uploadMedia }>
					{ 'Upload' }
				</Button>
			</Header>

			<div style={ {
				position: `relative`,
			} }>
				<Dashicon
					icon="search"
					style={ {
						position: `absolute`,
						top: `8px`,
						left: `10px`,
						fill: `#DADADA`,
					} } />
				<Search
					type={ 'text' }
					value={ searchTerm }
					placeholder={ 'Search Media' }
					onChange={ ( evt ) => {
						setSearchTerm( evt.target.value );
						setIsMediaLoading( false );
						setIsMediaLoaded( false );
					} } />
			</div>

			<FilterButtons>
				{
					FILTERS.map( ( { filter, name }, index ) => (
						<FilterButton
							key={ index }
							active={ filter === mediaType }
							onClick={ () => {
								setMediaType( filter );
								setIsMediaLoading( false );
								setIsMediaLoaded( false );
							} }>
							{ name }
						</FilterButton>
					) )
				}
			</FilterButtons>

			{ ( isMediaLoaded && ! media.length ) ? (
				<Message>
					{ 'No media found' }
				</Message>
			) : (
				<Container>
					<Column>
						{ media.map( ( mediaEl, index ) => {
							return ( isEven( index ) ) ? getMediaElement( mediaEl, DEFAULT_WIDTH ) : null;
						} ) }
					</Column>
					<Column>
						{ media.map( ( mediaEl, index ) => {
							return ( ! isEven( index ) ) ? getMediaElement( mediaEl, DEFAULT_WIDTH ) : null;
						} ) }
					</Column>
				</Container>

			)
			}

		</div>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;
