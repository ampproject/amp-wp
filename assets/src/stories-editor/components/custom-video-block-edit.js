/**
 * External dependencies
 */
import PropTypes from 'prop-types';

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
import { withSelect, withDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { getFirstFrameOfVideo } from '../helpers';
import { getContentLengthFromUrl, isVideoSizeExcessive } from '../../common/helpers';
import { MEGABYTE_IN_BYTES, VIDEO_ALLOWED_MEGABYTES_PER_SECOND } from '../../common/constants';

const ALLOWED_MEDIA_TYPES = [ 'video' ];
const VIDEO_POSTER_ALLOWED_MEDIA_TYPES = [ 'image' ];
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
	constructor() {
		super( ...arguments );

		this.state = {
			editing: ! this.props.attributes.src,
		};

		this.videoPlayer = createRef();
		this.posterImageButton = createRef();
		this.toggleAttribute = this.toggleAttribute.bind( this );
		this.onSelectURL = this.onSelectURL.bind( this );
		this.onSelectPoster = this.onSelectPoster.bind( this );
		this.onUploadError = this.onUploadError.bind( this );
		this.onLoadedMetadata = this.onLoadedMetadata.bind( this );
	}

	componentDidMount() {
		const {
			attributes,
			mediaUpload,
			noticeOperations,
			setAttributes,
			uploadVideoFrame,
			videoFeaturedImage,
		} = this.props;
		const { id, src = '', poster } = attributes;
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
					allowedTypes: ALLOWED_MEDIA_TYPES,
				} );
			}
		}

		if ( src ) {
			getContentLengthFromUrl( src ).then( ( videoSize ) => {
				this.setState( { videoSize } );
			} );

			if ( ! poster ) {
				if ( videoFeaturedImage ) {
					setAttributes( { poster: videoFeaturedImage.source_url } );
				} else {
					uploadVideoFrame( src );
				}
			}
		}
	}

	componentDidUpdate( prevProps ) {
		const { uploadVideoFrame } = this.props;
		const { poster, src } = this.props.attributes;
		if ( poster !== prevProps.attributes.poster ) {
			this.videoPlayer.current.load();
		}

		if ( ! poster && this.props.videoFeaturedImage ) {
			this.props.setAttributes( { poster: this.props.videoFeaturedImage.source_url } );
		}

		if ( prevProps.attributes.src !== src ) {
			getContentLengthFromUrl( src ).then( ( videoSize ) => {
				this.setState( { videoSize } );
			} );

			if ( ! poster ) {
				uploadVideoFrame( src );
			}
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
			// Omit the embed block logic, as that didn't seem to work.
			setAttributes( { src: newSrc, id: undefined } );
		}

		this.setState( { editing: false, duration: null, videoSize: null } );
	}

	onSelectPoster( image ) {
		const { setAttributes } = this.props;
		setAttributes( { poster: image.url } );
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
			caption,
			loop,
			poster,
			src,
		} = this.props.attributes;
		const {
			className,
			instanceId,
			isSelected,
			noticeUI,
			setAttributes,
		} = this.props;
		const { editing } = this.state;
		const switchToEditing = () => {
			this.setState( { editing: true } );
		};
		const onSelectVideo = ( media ) => {
			if ( ! media || ! media.url ) {
				// in this case there was an error and we should continue in the editing state
				// previous attributes should be removed because they may be temporary blob urls
				setAttributes( { src: undefined, id: undefined } );
				switchToEditing();
				return;
			}

			// sets the block's attribute and updates the edit component from the
			// selected media, then switches off the editing UI
			setAttributes( { src: media.url, id: media.id } );
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
					allowedTypes={ ALLOWED_MEDIA_TYPES }
					value={ this.props.attributes }
					notices={ noticeUI }
					onError={ this.onUploadError }
				/>
			);
		}

		const videoPosterDescription = `video-block__poster-image-description-${ instanceId }`;

		const videoBytesPerSecond = this.state.duration && this.state.videoSize ? this.state.videoSize / this.state.duration : 0;
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
						<MediaUploadCheck>
							<BaseControl
								className="editor-video-poster-control"
							>
								<BaseControl.VisualLabel>
									{ __( 'Poster Image', 'amp' ) }
								</BaseControl.VisualLabel>
								<MediaUpload
									title={ __( 'Select Poster Image', 'amp' ) }
									onSelect={ this.onSelectPoster }
									allowedTypes={ VIDEO_POSTER_ALLOWED_MEDIA_TYPES }
									render={ ( { open } ) => (
										<Button
											isDefault
											onClick={ open }
											ref={ this.posterImageButton }
											aria-describedby={ videoPosterDescription }
										>
											{ ! poster ? __( 'Select Poster Image', 'amp' ) : __( 'Replace image', 'amp' ) }
										</Button>
									) }
								/>
								{ poster && (
									<p
										id={ videoPosterDescription }
										hidden
									>
										{
											/* translators: %s: the poster image URL. */
											sprintf( __( 'The current poster image url is %s', 'amp' ), this.props.attributes.poster )
										}
									</p>
								) }
								{ ! poster && (
									<Notice
										status="error"
										isDismissible={ false }
									>
										{ __( 'A poster is required for videos in stories.', 'amp' ) }
									</Notice>
								) }
							</BaseControl>
						</MediaUploadCheck>
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
	} ),
	className: PropTypes.string,
	instanceId: PropTypes.number,
	isSelected: PropTypes.bool,
	mediaUpload: PropTypes.func,
	noticeUI: PropTypes.oneOfType( [ PropTypes.func, PropTypes.bool ] ),
	noticeOperations: PropTypes.object,
	setAttributes: PropTypes.func,
	videoFeaturedImage: PropTypes.shape( {
		source_url: PropTypes.string,
	} ),
	uploadVideoFrame: PropTypes.func,
};

export default compose( [
	withDispatch( ( dispatch ) => {
		const { saveMedia } = dispatch( 'core' );

		return {
			saveMedia,
		};
	} ),
	withSelect( ( select, { attributes, setAttributes, saveMedia } ) => {
		const { getMedia } = select( 'core' );
		const { getSettings } = select( 'core/block-editor' );
		const { __experimentalMediaUpload: mediaUpload } = getSettings();

		let videoFeaturedImage;

		const { id, poster } = attributes;

		if ( id && ! poster ) {
			const media = getMedia( id );
			videoFeaturedImage = media && media.featured_media > 0 && getMedia( media.featured_media );
		}

		/**
		 * Uploads the video's first frame as an attachment.
		 *
		 * @param {string} src Video URL.
		 */
		const uploadVideoFrame = async ( src ) => {
			const img = await getFirstFrameOfVideo( src );

			mediaUpload( {
				filesList: [ img ],
				onFileChange: ( [ { id: posterId, url: posterUrl } ] ) => {
					if ( ! isBlobURL( posterUrl ) ) {
						setAttributes( { poster: posterUrl } );
					}

					if ( id && posterId ) {
						saveMedia( {
							id,
							featured_media: posterId,
						} );
					}
				},
			} );
		};

		return {
			mediaUpload,
			videoFeaturedImage,
			uploadVideoFrame,
		};
	} ),
	withNotices,
	withInstanceId,
] )( CustomVideoBlockEdit );
