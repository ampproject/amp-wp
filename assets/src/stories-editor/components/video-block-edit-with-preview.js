/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { getBlobByURL, isBlobURL } from '@wordpress/blob';
import {
	Component,
	createRef,
} from '@wordpress/element';
import {
	BaseControl,
	Button,
	IconButton,
	PanelBody,
	ToggleControl,
	Toolbar,
} from '@wordpress/components';
import {
	BlockControls,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
	RichText,
} from '@wordpress/block-editor';

const ALLOWED_MEDIA_TYPES = [ 'video' ];
const VIDEO_POSTER_ALLOWED_MEDIA_TYPES = [ 'image' ];

/**
 * Mainly forked from the Core Video block Edit component, but allows the <video> to play instead of being disabled.
 *
 * There are very few changes from the Core Video block's components.
 * The main change is that in render(), the <video> is not wrapped in <Disabled>, so it can play.
 *
 * @class
 */
class VideoBlockEditWithPreview extends Component {
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
			controls,
			poster,
			src,
		} = this.props.attributes;
		const {
			instanceId,
			isSelected,
			setAttributes,
		} = this.props;
		const switchToEditing = () => {
			this.setState( { editing: true } );
		};
		const videoPosterDescription = `video-block__poster-image-description-${ instanceId }`;

		return (
			<>
				<BlockControls>
					<Toolbar>
						<IconButton
							className="components-icon-button components-toolbar__control"
							label={ __( 'Edit video' ) }
							onClick={ switchToEditing }
							icon="edit"
						/>
					</Toolbar>
				</BlockControls>
				<InspectorControls>
					<PanelBody title={ __( 'Video Settings' ) }>
						<ToggleControl
							label={ __( 'Playback Controls' ) }
							onChange={ this.toggleAttribute( 'controls' ) }
							checked={ controls }
						/>
						<MediaUploadCheck>
							<BaseControl
								className="editor-video-poster-control"
							>
								<BaseControl.VisualLabel>
									{ __( 'Poster Image' ) }
								</BaseControl.VisualLabel>
								<MediaUpload
									title={ __( 'Select Poster Image' ) }
									onSelect={ this.onSelectPoster }
									allowedTypes={ VIDEO_POSTER_ALLOWED_MEDIA_TYPES }
									render={ ( { open } ) => (
										<Button
											isDefault
											onClick={ open }
											ref={ this.posterImageButton }
											aria-describedby={ videoPosterDescription }
										>
											{ ! poster ? __( 'Select Poster Image' ) : __( 'Replace image' ) }
										</Button>
									) }
								/>
								<p
									id={ videoPosterDescription }
									hidden
								>
									{ poster ?
										/* translators: %s: the poster image URL. */
										sprintf( __( 'The current poster image url is %s' ), this.props.attributes.poster ) :
										__( 'There is no poster image currently selected' )
									}
								</p>
								{ !! poster &&
									<Button onClick={ this.onRemovePoster } isLink isDestructive>
										{ __( 'Remove Poster Image' ) }
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
						loop
						controls={ controls }
						poster={ poster }
						ref={ this.videoPlayer }
						src={ src }
					/>
					{ ( ! RichText.isEmpty( caption ) || isSelected ) && (
						<RichText
							tagName="figcaption"
							placeholder={ __( 'Write caption…' ) }
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

VideoBlockEditWithPreview.propTypes = {
	attributes: PropTypes.shape( {
		caption: PropTypes.string,
		controls: PropTypes.bool,
		id: PropTypes.number,
		poster: PropTypes.string,
		src: PropTypes.string,
	} ),
	instanceId: PropTypes.number,
	isSelected: PropTypes.bool,
	mediaUpload: PropTypes.func,
	noticeOperations: PropTypes.object,
	setAttributes: PropTypes.func,
};

export default VideoBlockEditWithPreview;
