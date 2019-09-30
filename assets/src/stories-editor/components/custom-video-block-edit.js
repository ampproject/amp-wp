/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { getBlobByURL, isBlobURL } from '@wordpress/blob';
import { useRef, useState, useEffect } from '@wordpress/element';
import {
	BaseControl,
	Button,
	IconButton,
	Notice,
	PanelBody,
	Path,
	ResponsiveWrapper,
	SVG,
	TextControl,
	ToggleControl,
	Toolbar,
	withNotices,
} from '@wordpress/components';
import {
	BlockControls,
	BlockIcon,
	InspectorControls,
	MediaPlaceholder,
	MediaUpload,
	MediaUploadCheck,
	RichText,
} from '@wordpress/block-editor';
import { compose, withInstanceId } from '@wordpress/compose';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { uploadVideoFrame, getPosterImageFromFileObj } from '../helpers';
import { getContentLengthFromUrl, isVideoSizeExcessive } from '../../common/helpers';
import { MEGABYTE_IN_BYTES, VIDEO_ALLOWED_MEGABYTES_PER_SECOND } from '../../common/constants';
import { POSTER_ALLOWED_MEDIA_TYPES } from '../constants';

const icon = <SVG viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><Path fill="none" d="M0 0h24v24H0V0z" /><Path d="M4 6.47L5.76 10H20v8H4V6.47M22 4h-4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4z" /></SVG>;

/**
 * Mainly forked from the Core Video block edit component, but allows the <video> to play instead of being disabled.
 *
 * There are very few changes from the Core Video block's component.
 * The main change is that in render(), the <video> is not wrapped in <Disabled>, so it can play.
 *
 * Also removes video settings that are not applicable / allowed in an AMP Stories context.
 */
const CustomVideoBlockEdit = ( { instanceId, isSelected, className, attributes, setAttributes, mediaUpload, noticeUI, noticeOperations } ) => {
	const {
		caption,
		loop,
		id,
		poster,
		src,
		width,
		height,
		ampAriaLabel,
	} = attributes;

	const [ isEditing, setIsEditing ] = useState( ! src );
	const [ videoSize, setVideoSize ] = useState( null );
	const [ duration, setDuration ] = useState( null );
	const [ isExtractingPoster, setExtractingPoster ] = useState( false );

	const switchToEditing = () => setIsEditing( true );

	const videoPlayer = useRef( null );

	const {
		media,
		videoFeaturedImage,
		allowedVideoMimeTypes,
	} = useSelect( ( select ) => {
		const { getMedia } = select( 'core' );
		const { getSettings } = select( 'amp/story' );

		let featuredImage;

		const mediaObj = id ? getMedia( id ) : undefined;

		if ( mediaObj && mediaObj.featured_media && ! poster ) {
			featuredImage = getMedia( mediaObj.featured_media );
		}

		return {
			media: mediaObj,
			videoFeaturedImage: featuredImage,
			allowedVideoMimeTypes: getSettings().allowedVideoMimeTypes,
		};
	}, [ id, poster ] );

	useEffect( () => {
		if ( ! id && isBlobURL( src ) ) {
			const file = getBlobByURL( src );
			if ( file ) {
				mediaUpload( {
					filesList: [ file ],
					onFileChange: ( [ { id: mediaId, url } ] ) => {
						setDuration( null );
						setVideoSize( null );
						setAttributes( { id: mediaId, src: url } );
					},
					onError: ( message ) => {
						setIsEditing( true );
						noticeOperations.createErrorNotice( message );
					},
					allowedTypes: allowedVideoMimeTypes,
				} );
			}
		}

		if ( src && ! isBlobURL( src ) ) {
			getContentLengthFromUrl( src ).then( setVideoSize );
		}
	}, [ allowedVideoMimeTypes, id, mediaUpload, noticeOperations, setAttributes, src ] );

	useEffect( () => {
		if ( videoPlayer.current ) {
			videoPlayer.current.load();
		}
	}, [ poster ] );

	useEffect( () => {
		if ( ! isBlobURL( src ) ) {
			getContentLengthFromUrl( src ).then( setVideoSize );
		}
	}, [ src ] );

	useEffect( () => {
		if ( ! ampAriaLabel && media ) {
			/*
			 * New video set from media library and we don't have an aria label already,
			 * use alt text or title from media object.
			 */
			const newAriaLabel = media.alt_text || ( media.title && media.title.raw ) || '';
			setAttributes( { ampAriaLabel: newAriaLabel } );
		}

		if ( videoFeaturedImage ) {
			setAttributes( { poster: videoFeaturedImage.source_url } );
		} else if ( media && ! media.featured_media && ! isExtractingPoster ) {
			/*
			 * The video has changed, and its media object has been loaded already.
			 *
			 * Since it's clear that the video does not have a featured (poster) image,
			 * one can be generated now.
			 */

			setExtractingPoster( true );

			uploadVideoFrame( { id, src } )
				.then( ( fileObj ) => {
					setAttributes( { poster: getPosterImageFromFileObj( fileObj ) } );
					setExtractingPoster( false );
				} )
				.catch( () => setExtractingPoster( false ) );
		}
	}, [ media, ampAriaLabel, id, isExtractingPoster, setAttributes, src, videoFeaturedImage ] );

	/**
	 * Callback to toggle an attribute's value.
	 *
	 * @param {string} attribute Attribute name.
	 * @return {Function} Function that updates the block's attributes.
	 */
	const toggleAttribute = ( attribute ) => ( newValue ) => setAttributes( { [ attribute ]: newValue } );

	/**
	 * URL selection callback.
	 *
	 * @param {string} newSrc New src value.
	 */
	const onSelectURL = ( newSrc ) => {
		// Set the block's src from the edit component's state, and switch off
		// the editing UI.
		if ( newSrc !== src ) {
			setAttributes( { src: newSrc, id: undefined, poster: undefined } );

			setExtractingPoster( true );

			/*
			 * Since the video has been added via URL, there's no attachment object
			 * a poster image could be retrieved from.
			 */
			uploadVideoFrame( { src: newSrc } )
				.then( ( fileObj ) => {
					setAttributes( { poster: getPosterImageFromFileObj( fileObj ) } );
					setExtractingPoster( false );
				} )
				.catch( () => setExtractingPoster( false ) );
		}

		setIsEditing( false );
		setDuration( null );
		setVideoSize( null );
	};

	/**
	 * Upload error callback.
	 *
	 * @param {string} message Error message.
	 */
	const onUploadError = ( message ) => {
		noticeOperations.removeAllNotices();
		noticeOperations.createErrorNotice( message );
	};

	/**
	 * Metadata loaded callback.
	 *
	 * @param {Event} event Event object.
	 */
	const onLoadedMetadata = ( event ) => {
		setDuration( Math.round( event.currentTarget.duration ) );
	};

	const onSelectVideo = ( mediaObj ) => {
		if ( ! mediaObj || ! mediaObj.url ) {
			// in this case there was an error and we should continue in the editing state
			// previous attributes should be removed because they may be temporary blob urls
			setAttributes( { src: undefined, id: undefined, poster: undefined } );
			switchToEditing();
			return;
		}

		// sets the block's attribute and updates the edit component from the
		// selected media, then switches off the editing UI
		setAttributes( { src: mediaObj.url, id: mediaObj.id, poster: undefined } );
		setIsEditing( false );
		setDuration( null );
		setVideoSize( null );
	};

	if ( isEditing ) {
		return (
			<MediaPlaceholder
				icon={ <BlockIcon icon={ icon } /> }
				className={ className }
				onSelect={ onSelectVideo }
				onSelectURL={ onSelectURL }
				accept={ allowedVideoMimeTypes.join( ',' ) }
				allowedTypes={ allowedVideoMimeTypes }
				value={ attributes }
				notices={ noticeUI }
				onError={ onUploadError }
			/>
		);
	}

	const videoBytesPerSecond = duration && videoSize ? videoSize / duration : 0;
	const videoPosterId = `video-block__poster-image-${ instanceId }`;

	const isExcessiveVideoSize = videoBytesPerSecond ? isVideoSizeExcessive( videoBytesPerSecond ) : null;

	return (
		<>
			<BlockControls>
				<Toolbar>
					<IconButton
						className="components-icon-button components-toolbar__control"
						label={ __( 'Edit video', 'amp' ) }
						onClick={ switchToEditing }
						icon="edit"
					/>
				</Toolbar>
			</BlockControls>
			<InspectorControls>
				<PanelBody title={ __( 'Video Settings', 'amp' ) }>
					<ToggleControl
						label={ __( 'Loop', 'amp' ) }
						onChange={ toggleAttribute( 'loop' ) }
						checked={ loop }
					/>
					<TextControl
						label={ __( 'Assistive Text', 'amp' ) }
						help={ __( 'Used to inform visually impaired users about the video content.', 'amp' ) }
						value={ ampAriaLabel }
						onChange={ ( label ) => setAttributes( { ampAriaLabel: label } ) }
					/>
					{ ( ! isExtractingPoster || poster ) && (
						<MediaUploadCheck>
							<BaseControl
								id={ videoPosterId }
								label={ __( 'Poster Image', 'amp' ) }
								className="editor-video-poster-control"
							>
								{
									! poster &&
									<Notice status="error" isDismissible={ false } >
										{ __( 'A poster image must be set.', 'amp' ) }
									</Notice>
								}
								<MediaUpload
									title={ __( 'Select Poster Image', 'amp' ) }
									onSelect={ ( image ) => {
										setAttributes( { poster: image.url } );
									} }
									allowedTypes={ POSTER_ALLOWED_MEDIA_TYPES }
									render={ ( { open } ) => (
										<Button
											id={ videoPosterId }
											className={ classnames(
												'video-block__poster-image',
												{
													'editor-post-featured-image__toggle': ! poster,
													'editor-post-featured-image__preview': poster,
												}
											) }
											onClick={ open }
											aria-label={ ! poster ? null : __( 'Replace Poster Image', 'amp' ) }
										>
											{ poster && (
												<ResponsiveWrapper
													naturalWidth={ width }
													naturalHeight={ height }
												>
													<img src={ poster } alt="" />
												</ResponsiveWrapper>
											) }
											{ ! poster &&
											__( 'Set Poster Image', 'amp' )
											}
										</Button>
									) }
								/>
							</BaseControl>
						</MediaUploadCheck>
					) }
					{
						isExcessiveVideoSize && (
							<Notice status="warning" isDismissible={ false } >
								{
									sprintf(
										/* translators: %d: the number of recommended megabytes per second */
										__( 'A video size of less than %d MB per second is recommended.', 'amp' ),
										VIDEO_ALLOWED_MEGABYTES_PER_SECOND
									)
								}
								{ ' ' }
								{
									sprintf(
										/* translators: %d: the number of actual megabytes per second */
										__( 'The selected video is %d MB per second.', 'amp' ),
										Math.round( videoBytesPerSecond / MEGABYTE_IN_BYTES )
									)
								}
							</Notice>
						)
					}
				</PanelBody>
			</InspectorControls>
			<figure className="wp-block-video">
				<video
					autoPlay
					muted
					aria-label={ ampAriaLabel }
					loop={ loop }
					controls={ ! loop }
					poster={ poster }
					ref={ videoPlayer }
					src={ src }
					onLoadedMetadata={ onLoadedMetadata }
				/>
				{ ( ! RichText.isEmpty( caption ) || isSelected ) && (
					<RichText
						tagName="figcaption"
						placeholder={ __( 'Write caption…', 'amp' ) }
						value={ caption }
						onChange={ ( value ) => setAttributes( { caption: value } ) }
						inlineToolbar
					/>
				) }
			</figure>
		</>
	);
};

CustomVideoBlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		caption: PropTypes.string,
		controls: PropTypes.bool,
		loop: PropTypes.bool,
		ampAriaLabel: PropTypes.string,
		id: PropTypes.number,
		poster: PropTypes.string,
		src: PropTypes.string,
		width: PropTypes.number,
		height: PropTypes.number,
	} ),
	className: PropTypes.string,
	instanceId: PropTypes.number,
	isSelected: PropTypes.bool,
	mediaUpload: PropTypes.func,
	noticeUI: PropTypes.oneOfType( [ PropTypes.func, PropTypes.bool ] ),
	noticeOperations: PropTypes.object,
	setAttributes: PropTypes.func,
};

export default compose( [
	withNotices,
	withInstanceId,
] )( CustomVideoBlockEdit );
