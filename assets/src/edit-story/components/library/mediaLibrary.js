/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled, { css } from 'styled-components';

/**
 * WordPress dependencies
 */
import { Spinner, Dashicon } from '@wordpress/components';
import { useEffect } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import UploadButton from '../uploadButton';
import useLibrary from './useLibrary';

const Container = styled.div`
	display: grid;
    grid-gap: 10px;
    grid-template-columns: 1fr 1fr;
`;

const Column = styled.div``;

export const StyledTiles = css`
	width: 100%;
	border-radius: 10px;
	margin-bottom: 10px;
`;

const Image = styled.img`
	${ StyledTiles }
`;

const Video = styled.video`
	${ StyledTiles }
`;

const Title = styled.h3`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	margin: 0px;
	font-size: 19px;
	line-height: 20px;
	line-height: 1.4em;
	flex: 3 0 0;
`;

const Header = styled.div`
	display: flex;
	margin: 0px 0px 25px;
`;

const Message = styled.div`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	font-size: 19px;
`;

const FilterButtons = styled.div`
	border-bottom:  2px solid ${ ( { theme } ) => theme.colors.mg.v1 };
	padding: 18px 0px;
	margin: 10px 0px 15px;
`;

const FilterButton = styled.button`
	border: 0px;
	background: none;
	padding: 0px;
	margin: 0px 28px 0px 0px;
	color: ${ ( { theme, active } ) => ( active ? theme.colors.fg.v1 : theme.colors.mg.v1 ) };
	font-weight: ${ ( { active } ) => ( active ? 'bold' : 'normal' ) };
	font-size: 13px;

`;

const SearchField = styled.div`
	position: relative;
`;

const Search = styled.input.attrs( { type: 'text' } )`
	width: 100%;
	background: ${ ( { theme } ) => theme.colors.mg.v1 } !important;
	border-color: ${ ( { theme } ) => theme.colors.mg.v1 } !important;
	color: ${ ( { theme } ) => theme.colors.mg.v2 } !important;
	padding: 2px 10px 2px 33px !important;
	&::placeholder {
	  color: ${ ( { theme } ) => theme.colors.mg.v2 };
	}
`;

const Icon = styled( Dashicon )`
	position: absolute;
	top: 8px;
	left: 10px;
	fill: ${ ( { theme } ) => theme.colors.mg.v2 };
`;

const ButtonCSS = css`
	 background: none;
	 color: ${ ( { theme } ) => theme.colors.fg.v1 };
	 padding: 5px;
	 font-weight: bold;
	 flex: 1 0 0;
	 text-align: center;
	 border: 1px solid ${ ( { theme } ) => theme.colors.mg.v1 };
	 border-radius: 3px;
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
	{ filter: '', name: __( 'All', 'amp' ) },
	{ filter: 'image', name: __( 'Images', 'amp' ) },
	{ filter: 'video', name: __( 'Video', 'amp' ) },
];

const DEFAULT_WIDTH = 150;

function MediaLibrary( { onInsert } ) {
	const {
		state: { media, isMediaLoading, isMediaLoaded, mediaType, searchTerm },
		actions: { loadMedia, setIsMediaLoading, setIsMediaLoaded, setMediaType, setSearchTerm },
	} = useLibrary();

	useEffect( loadMedia );

	/**
	 * Check if number is odd or even.
	 *
	 * @param {number} n Number
	 * @return {boolean} Is even.
	 */
	const isEven = ( n ) => {
		return n % 2 === 0;
	};

	/**
	 * Handle search term changes.
	 *
	 * @param {Object} evt Doc Event
	 */
	const onSearch = ( evt ) => {
		setSearchTerm( evt.target.value );
		setIsMediaLoading( false );
		setIsMediaLoaded( false );
	};

	/**
	 * Filter REST API calls and re-request API.
	 *
	 * @param {string} filter Value that is passed to rest api to filter.
	 */
	const onFilter = ( filter ) => {
		if ( filter !== mediaType ) {
			setMediaType( filter );
			setIsMediaLoading( false );
			setIsMediaLoaded( false );
		}
	};

	const onClose = () => {
		setIsMediaLoading( false );
		setIsMediaLoaded( false );
	};

	/**
	 * Callback of select in media picker to insert media element.
	 *
	 * @param {Object} attachment Attachment object from backbone media picker.
	 */
	const onSelect = ( attachment ) => {
		const { url: src, mime: mimeType, width: oWidth, height: oHeight } = attachment;
		const mediaEl = { src, mimeType, oWidth, oHeight };
		insertMediaElement( mediaEl, DEFAULT_WIDTH );
	};

	/**
	 * Insert element such image, video and audio into the editor.
	 *
	 * @param {Object} attachment Attachment object
	 * @param {number} width      Width that element is inserted into editor.
	 * @return {null|*}          Return onInsert or null.
	 */
	const insertMediaElement = ( attachment, width ) => {
		const { src, mimeType, oWidth, oHeight, id, featuredMedia, featuredMediaSrc } = attachment;
		const origRatio = oWidth / oHeight;
		const height = width / origRatio;
		if ( SUPPORTED_IMAGE_TYPES.includes( mimeType ) ) {
			return onInsert( 'image', {
				src,
				width,
				height,
				x: 5,
				y: 5,
				rotationAngle: 0,
				origRatio,
				origWidth: oWidth,
				origHeight: oHeight,
			} );
		} else if ( SUPPORTED_VIDEO_TYPES.includes( mimeType ) ) {
			return onInsert( 'video', {
				src,
				width,
				height,
				x: 5,
				y: 5,
				rotationAngle: 0,
				origRatio,
				origWidth: oWidth,
				origHeight: oHeight,
				mimeType,
				videoId: id,
				featuredMedia,
				featuredMediaSrc,
			} );
		}
		return null;
	};

	/**
	 * Get a formatted element for different media types.
	 *
	 * @param {Object} mediaEl Attachment object
	 * @param {number} width      Width that element is inserted into editor.
	 * @return {null|*}          Element or null if does not map to video/image.
	 */
	const getMediaElement = ( mediaEl, width ) => {
		const { src, oWidth, oHeight, mimeType } = mediaEl;
		const origRatio = oWidth / oHeight;
		const height = width / origRatio;
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
				width={ width }
				height={ height }
				onClick={ () => insertMediaElement( mediaEl, width ) }
				onMouseEnter={ ( evt ) => {
					evt.target.play();
				} }
				onMouseLeave={ ( evt ) => {
					evt.target.pause();
					evt.target.currentTime = 0;
				} }
			>
				<source src={ src } type={ mimeType } />
			</Video> );
			/* eslint-enable react/jsx-closing-tag-location */
		}
		return null;
	};

	return (
		<>
			<Header>
				<Title>
					{ __( 'Media', 'amp' ) }
					{ ( ! isMediaLoaded || isMediaLoading ) &&
						<Spinner />
					}
				</Title>
				<UploadButton
					onClose={ onClose }
					onSelect={ onSelect }
					buttonCSS={ ButtonCSS }
				/>
			</Header>

			<SearchField>
				<Icon icon="search" />
				<Search
					value={ searchTerm }
					placeholder={ __( 'Search Media', 'amp' ) }
					onChange={ onSearch } />
			</SearchField>

			<FilterButtons>
				{
					FILTERS.map( ( { filter, name }, index ) => (
						<FilterButton
							key={ index }
							active={ filter === mediaType }
							onClick={ () => onFilter( filter ) }>
							{ name }
						</FilterButton>
					) )
				}
			</FilterButtons>

			{ ( isMediaLoaded && ! media.length ) ? (
				<Message>
					{ __( 'No media found', 'amp' ) }
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

		</>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;
