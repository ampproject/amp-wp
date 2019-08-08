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
import { Component, createRef } from '@wordpress/element';
import {
	BaseControl,
	Button,
	IconButton,
	Notice,
	PanelBody,
	Path,
	ResponsiveWrapper,
	SVG,
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
import { withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { uploadVideoFrame, getPosterImageFromFileObj } from '../helpers';
import { getContentLengthFromUrl, isVideoSizeExcessive } from '../../common/helpers';
import { MEGABYTE_IN_BYTES, VIDEO_ALLOWED_MEGABYTES_PER_SECOND } from '../../common/constants';
import { POSTER_ALLOWED_MEDIA_TYPES, ALLOWED_VIDEO_TYPES } from '../constants';

const icon = <SVG viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><Path fill="none" d="M0 0h24v24H0V0z" /><Path d="M4 6.47L5.76 10H20v8H4V6.47M22 4h-4l2 4h-3l-2-4h-2l2 4h-3l-2-4H8l2 4H7L5 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V4z" /></SVG>;

/**
 * Mainly forked from the Core Video block edit component, but allows the <video> to play instead of being disabled.
 *
 * There are very few changes from the Core Video block's component.
 * The main change is that in render(), the <video> is not wrapped in <Disabled>, so it can play.
 *
 * Also removes video settings that are not applicable / allowed in an AMP Stories context.
 *
 * @class
 */
class CustomVideoBlockEdit extends Component {
	constructor( ...args ) {
		super( ...args );

		this.state = {
			editing: ! this.props.attributes.src,
			extractingPoster: false,
		};

		this.videoPlayer = createRef();
		this.toggleAttribute = this.toggleAttribute.bind( this );
		this.onSelectURL = this.onSelectURL.bind( this );
		this.onUploadError = this.onUploadError.bind( this );
		this.onLoadedMetadata = this.onLoadedMetadata.bind( this );
	}

	componentDidMount() {
		const {
			attributes,
			mediaUpload,
			noticeOperations,
			setAttributes,
		} = this.props;
		const { id, src = '' } = attributes;

		if ( ! id && isBlobURL( src ) ) {
			const file = getBlobByURL( src );
			if ( file ) {
				mediaUpload( {
					filesList: [ file ],
					onFileChange: ( [ { id: mediaId, url } ] ) => {
						this.setState( { duration: null, videoSize: null } );
						setAttributes( { id: mediaId, src: url } );
					},
					onError: ( message ) => {
						this.setState( { editing: true } );
						noticeOperations.createErrorNotice( message );
					},
					allowedTypes: ALLOWED_VIDEO_TYPES,
				} );
			}
		}

		if ( src && ! isBlobURL( src ) ) {
			getContentLengthFromUrl( src ).then( ( videoSize ) => {
				this.setState( { videoSize } );
			} );
		}
	}

	componentDidUpdate( prevProps ) {
		const { attributes, setAttributes, videoFeaturedImage, media } = this.props;
		const { poster, src, id } = attributes;

		if ( poster !== prevProps.attributes.poster && this.videoPlayer.current ) {
			this.videoPlayer.current.load();
		}

		if ( src !== prevProps.attributes.src && ! isBlobURL( src ) ) {
			getContentLengthFromUrl( src )
				.then( ( videoSize ) => {
					this.setState( { videoSize } );
				} );
		}

		if ( poster ) {
			return;
		}

		if ( videoFeaturedImage ) {
			setAttributes( { poster: videoFeaturedImage.source_url } );
		} else if ( media && media !== prevProps.media && ! media.featured_media && ! this.state.extractingPoster ) {
			/*
			 * The video has changed, and its media object has been loaded already.
			 *
			 * Since it's clear that the video does not have a featured (poster) image,
			 * one can be generated now.
			 */

			this.setState( { extractingPoster: true } );

			uploadVideoFrame( { id, src } )
				.then( ( fileObj ) => {
					setAttributes( { poster: getPosterImageFromFileObj( fileObj ) } );
					this.setState( { extractingPoster: false } );
				} )
				.catch( () => this.setState( { extractingPoster: false } ) );
		}
	}

	toggleAttribute( attribute ) {
		return ( newValue ) => {
			this.props.setAttributes( { [ attribute ]: newValue } );
		};
	}

	onSelectURL( newSrc ) {
		const { attributes, setAttributes } = this.props;
		const { src } = attributes;

		// Set the block's src from the edit component's state, and switch off
		// the editing UI.
		if ( newSrc !== src ) {
			setAttributes( { src: newSrc, id: undefined, poster: undefined } );

			this.setState( { extractingPoster: true } );

			/*
			 * Since the video has been added via URL, there's no attachment object
			 * a poster image could be retrieved from.
			 */
			uploadVideoFrame( { src: newSrc } )
				.then( ( fileObj ) => {
					setAttributes( { poster: getPosterImageFromFileObj( fileObj ) } );
					this.setState( { extractingPoster: false } );
				} )
				.catch( () => this.setState( { extractingPoster: false } ) );
		}

		this.setState( { editing: false, duration: null, videoSize: null } );
	}

	onUploadError( message ) {
		const { noticeOperations } = this.props;
		noticeOperations.removeAllNotices();
		noticeOperations.createErrorNotice( message );
	}

	onLoadedMetadata( event ) {
		const duration = Math.round( event.currentTarget.duration );

		this.setState( { duration } );
	}

	render() {
		const {
			className,
			instanceId,
			isSelected,
			noticeUI,
			attributes,
			setAttributes,
		} = this.props;
		const {
			caption,
			loop,
			poster,
			src,
			width,
			height,
		} = attributes;

		const { editing } = this.state;
		const switchToEditing = () => {
			this.setState( { editing: true } );
		};
		const onSelectVideo = ( media ) => {
			if ( ! media || ! media.url ) {
				// in this case there was an error and we should continue in the editing state
				// previous attributes should be removed because they may be temporary blob urls
				setAttributes( { src: undefined, id: undefined, poster: undefined } );
				switchToEditing();
				return;
			}

			// sets the block's attribute and updates the edit component from the
			// selected media, then switches off the editing UI
			setAttributes( { src: media.url, id: media.id, poster: undefined } );
			this.setState( { src: media.url, editing: false, duration: null, videoSize: null } );
		};

		if ( editing ) {
			return (
				<MediaPlaceholder
					icon={ <BlockIcon icon={ icon } /> }
					className={ className }
					onSelect={ onSelectVideo }
					onSelectURL={ this.onSelectURL }
					accept="video/mp4"
					allowedTypes={ ALLOWED_VIDEO_TYPES }
					value={ this.props.attributes }
					notices={ noticeUI }
					onError={ this.onUploadError }
				/>
			);
		}

		const videoBytesPerSecond = this.state.duration && this.state.videoSize ? this.state.videoSize / this.state.duration : 0;
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
							onChange={ this.toggleAttribute( 'loop' ) }
							checked={ loop }
						/>
						{ ( ! this.state.extractingPoster || poster ) && (
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
						loop={ loop }
						controls={ ! loop }
						poster={ poster }
						ref={ this.videoPlayer }
						src={ src }
						onLoadedMetadata={ this.onLoadedMetadata }
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
	}
}

CustomVideoBlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		caption: PropTypes.string,
		controls: PropTypes.bool,
		loop: PropTypes.bool,
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
	media: PropTypes.object,
	setAttributes: PropTypes.func,
	videoFeaturedImage: PropTypes.shape( {
		source_url: PropTypes.string,
	} ),
};

export default compose( [
	withSelect( ( select, { attributes } ) => {
		const { getMedia } = select( 'core' );

		let videoFeaturedImage;

		const { id, poster } = attributes;

		const media = id ? getMedia( id ) : undefined;

		if ( media && media.featured_media && ! poster ) {
			videoFeaturedImage = getMedia( media.featured_media );
		}

		return {
			media,
			videoFeaturedImage,
		};
	} ),
	withNotices,
	withInstanceId,
] )( CustomVideoBlockEdit );
