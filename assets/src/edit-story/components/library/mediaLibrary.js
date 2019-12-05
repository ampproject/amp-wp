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
	flex: 2 0 0;
`;

const Button = styled.button`
	 background: #242A3B;
	 color: #ffffff;
	 padding: 5px;
	 font-weight: bold;
	 flex: 1 0 0;
	 text-align: center;
	 border: 0px none;
`;

const Header = styled.div`
	display: flex;
	margin: 0px 0px 10px;
`;

const Message = styled.div`
	color: #ffffff;
	font-size: 19px;
`;

const FilterButtons = styled.div`
	border-bottom:  2px solid #606877;
	padding: 18px 0px;
	margin: 0px 0px 15px;
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
	color: rgba(255, 255, 255, 0.34)  !important;
	padding: 2px 10px !important;
`;

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
			const { url } = attachment;
			onInsert( 'image', {
				src: url,
				width: 100,
				height: 100,
				x: 5,
				y: 5,
				rotationAngle: 0,
			} );
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

	const filters = [
		{ filter: '', name: 'All' },
		{ filter: 'image', name: 'Images' },
		{ filter: 'video', name: 'Video' },
	];

	const getMediaElement = ( mediaEl, width ) => {
		const { src, oWidth, oHeight, mimeType } = mediaEl;
		const racio = oWidth / width;
		const height = Math.round( oHeight / racio );
		if ( [ 'image/png', 'image/jpeg' ].includes( mimeType ) ) {
			return ( <Image
				key={ src }
				src={ src }
				width={ width }
				height={ height }
				loading={ 'lazy' }
				onClick={ () => onInsert( 'image', {
					src,
					width: 200,
					height: 100,
					x: 5,
					y: 5,
					rotationAngle: 0,
				} ) }
			/> );
		} else if ( [ 'video/mp4' ].includes( mimeType ) ) {
			return ( <Video
				key={ src }
				controls={ true }
				width={ width }
				height={ height }
				onClick={ () => {} }
			>
				<source src={ src } type={ mimeType } />
			</Video> );
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

			<div>
				<Search
					type={ 'text' }
					value={ searchTerm }
					placeholder={ 'Search Media' }
					onChange={ ( evt ) => {
						setSearchTerm( evt.target.value ); setIsMediaLoading( false );
						setIsMediaLoaded( false );
					} } />
			</div>

			<FilterButtons>
				{
					filters.map( ( { filter, name }, index ) => (
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
							return ( isEven( index ) ) ? getMediaElement( mediaEl, 150 ) : null;
						} ) }
					</Column>
					<Column>
						{ media.map( ( mediaEl, index ) => {
							return ( ! isEven( index ) ) ? getMediaElement( mediaEl, 150 ) : null;
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
