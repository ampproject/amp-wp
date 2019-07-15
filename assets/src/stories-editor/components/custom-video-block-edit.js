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
import { withSelect } from '@wordpress/data';

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
		this.onRemovePoster = this.onRemovePoster.bind( this );
		this.onUploadError = this.onUploadError.bind( this );
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
					onFileChange: ( [ { url } ] ) => {
						setAttributes( { src: url } );
					},
					onError: ( message ) => {
						this.setState( { editing: true } );
						noticeOperations.createErrorNotice( message );
					},
					allowedTypes: ALLOWED_MEDIA_TYPES,
				} );
			}
		}
	}

	componentDidUpdate( prevProps ) {
		if ( this.props.attributes.poster !== prevProps.attributes.poster ) {
			this.videoPlayer.current.load();
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

		this.setState( { editing: false } );
	}

	onSelectPoster( image ) {
		const { setAttributes } = this.props;
		setAttributes( { poster: image.url } );
	}

	onRemovePoster() {
		const { setAttributes } = this.props;
		setAttributes( { poster: '' } );

		// Move focus back to the Media Upload button.
		this.posterImageButton.current.focus();
	}

	onUploadError( message ) {
		const { noticeOperations } = this.props;
		noticeOperations.removeAllNotices();
		noticeOperations.createErrorNotice( message );
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
			this.setState( { src: media.url, editing: false } );
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
								<p
									id={ videoPosterDescription }
									hidden
								>
									{ poster ?
										/* translators: %s: the poster image URL. */
										sprintf( __( 'The current poster image url is %s', 'amp' ), this.props.attributes.poster ) :
										__( 'There is no poster image currently selected', 'amp' )
									}
								</p>
								{ !! poster &&
									<Button onClick={ this.onRemovePoster } isLink isDestructive>
										{ __( 'Remove Poster Image', 'amp' ) }
									</Button>
								}
							</BaseControl>
						</MediaUploadCheck>
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
	noticeUI: PropTypes.func,
	noticeOperations: PropTypes.object,
	setAttributes: PropTypes.func,
};

export default compose( [
	withSelect( ( select ) => {
		const { getSettings } = select( 'core/block-editor' );
		const { __experimentalMediaUpload } = getSettings();
		return {
			mediaUpload: __experimentalMediaUpload,
		};
	} ),
	withNotices,
	withInstanceId,
] )( CustomVideoBlockEdit );
