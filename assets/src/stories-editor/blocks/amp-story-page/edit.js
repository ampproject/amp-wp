/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';
import { has } from 'lodash';

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import {
	InnerBlocks,
	PanelColorSettings,
	InspectorControls,
	MediaUpload,
	MediaUploadCheck,
} from '@wordpress/block-editor';
import { Component, createRef } from '@wordpress/element';
import {
	PanelBody,
	Button,
	BaseControl,
	FocalPointPicker,
	Notice,
	SelectControl,
	RangeControl,
	ResponsiveWrapper,
} from '@wordpress/components';
import {
	withSelect,
	withDispatch,
	dispatch,
} from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import {
	getTotalAnimationDuration,
	addBackgroundColorToOverlay,
	getCallToActionBlock,
	isVideoSizeExcessive,
	getVideoBytesPerSecond,
	getUniqueId,
} from '../../helpers';
import {
	ALLOWED_CHILD_BLOCKS,
	ALLOWED_MEDIA_TYPES,
	ALLOWED_MOVABLE_BLOCKS,
	IMAGE_BACKGROUND_TYPE,
	VIDEO_BACKGROUND_TYPE,
	POSTER_ALLOWED_MEDIA_TYPES,
	MAX_IMAGE_SIZE_SLUG,
	VIDEO_ALLOWED_MEGABYTES_PER_SECOND,
	MEGABYTE_IN_BYTES,
} from '../../constants';
import './edit.css';

class PageEdit extends Component {
	shouldComponentUpdate() {
		this.ensureCTABeingLast();
		return true;
	}

	constructor( props ) {
		super( ...arguments );

		if ( ! props.attributes.anchor ) {
			this.props.setAttributes( { anchor: getUniqueId() } );
		}

		this.videoPlayer = createRef();
		this.onSelectMedia = this.onSelectMedia.bind( this );
	}

	/**
	 * Media selection callback.
	 *
	 * @param {Object} media            Media object.
	 * @param {string} media.icon       Media icon.
	 * @param {string} media.url        Media URL.
	 * @param {string} media.media_type Media type.
	 * @param {string} media.type       Media type if it was an existing attachment.
	 * @param {number} media.id         Attachment ID.
	 * @param {Object} media.image      Media image object.
	 * @param {string} media.image.src  Media image URL
	 */
	onSelectMedia( media ) {
		if ( ! media || ! media.url ) {
			this.props.setAttributes(
				{
					mediaUrl: undefined,
					mediaId: undefined,
					mediaType: undefined,
					poster: undefined,
				}
			);
			return;
		}

		let mediaType;

		// For media selections originated from a file upload.
		if ( media.media_type ) {
			if ( media.media_type === VIDEO_BACKGROUND_TYPE ) {
				mediaType = VIDEO_BACKGROUND_TYPE;
			} else {
				mediaType = IMAGE_BACKGROUND_TYPE;
			}
		} else {
			// For media selections originated from existing files in the media library.
			if (
				media.type !== IMAGE_BACKGROUND_TYPE &&
				media.type !== VIDEO_BACKGROUND_TYPE
			) {
				return;
			}

			mediaType = media.type;
		}

		const mediaUrl = has( media, [ 'sizes', MAX_IMAGE_SIZE_SLUG, 'url' ] ) ? media.sizes[ MAX_IMAGE_SIZE_SLUG ].url : media.url;

		this.props.setAttributes( {
			mediaUrl,
			mediaId: media.id,
			mediaType,
			poster: VIDEO_BACKGROUND_TYPE === mediaType && media.image && media.image.src !== media.icon ? media.image.src : undefined,
		} );
	}

	componentDidUpdate( prevProps ) {
		if (
			VIDEO_BACKGROUND_TYPE === this.props.attributes.mediaType &&
			this.props.attributes.mediaUrl !== prevProps.attributes.mediaUrl &&
			this.videoPlayer.current
		) {
			this.videoPlayer.current.load();
		}
	}

	removeBackgroundColor( index ) {
		const { attributes, setAttributes } = this.props;
		const backgroundColors = JSON.parse( attributes.backgroundColors );
		backgroundColors.splice( index, 1 );
		setAttributes( { backgroundColors: JSON.stringify( backgroundColors ) } );
	}

	setBackgroundColors( value, index ) {
		const { attributes, setAttributes } = this.props;
		const backgroundColors = JSON.parse( attributes.backgroundColors );
		backgroundColors[ index ] = {
			color: value,
		};
		setAttributes( { backgroundColors: JSON.stringify( backgroundColors ) } );
	}

	getOverlayColorSettings() {
		const { attributes } = this.props;
		const backgroundColors = JSON.parse( attributes.backgroundColors );

		if ( ! backgroundColors.length ) {
			return [
				{
					value: undefined,
					onChange: ( value ) => {
						this.setBackgroundColors( value, 0 );
					},
					label: __( 'Color', 'amp' ),
				},
			];
		}

		const backgroundColorSettings = [];
		const useNumberedLabels = backgroundColors.length > 1;

		backgroundColors.forEach( ( color, index ) => {
			backgroundColorSettings[ index ] = {
				value: color ? color.color : undefined,
				onChange: ( value ) => {
					this.setBackgroundColors( value, index );
				},
				/* translators: %s: color number */
				label: useNumberedLabels ? sprintf( __( 'Color %s', 'amp' ), index + 1 ) : __( 'Color', 'amp' ),
			};
		} );

		return backgroundColorSettings;
	}

	ensureCTABeingLast() {
		const {
			getBlockOrder,
			moveBlockToPosition,
			clientId,
		} = this.props;
		const order = getBlockOrder( clientId );
		if ( 1 >= order.length ) {
			return;
		}
		const ctaBlock = getCallToActionBlock( clientId );
		if ( ctaBlock ) {
			// If the CTA is not the last block, move it there.
			if ( order[ order.length - 1 ] !== ctaBlock.clientId ) {
				moveBlockToPosition( ctaBlock.clientId, clientId, clientId, order.length - 1 );
			}
		}
	}

	render() {
		const { attributes, media, setAttributes, totalAnimationDuration, allowedBlocks } = this.props;

		const {
			mediaId,
			mediaType,
			mediaUrl,
			focalPoint = { x: .5, y: .5 },
			overlayOpacity,
			poster,
			autoAdvanceAfter,
			autoAdvanceAfterDuration,
		} = attributes;

		const instructions = <p>{ __( 'To edit the background image or video, you need permission to upload media.', 'amp' ) }</p>;

		const style = {
			backgroundImage: IMAGE_BACKGROUND_TYPE === mediaType && mediaUrl ? `url(${ mediaUrl })` : undefined,
			backgroundPosition: IMAGE_BACKGROUND_TYPE === mediaType ? `${ focalPoint.x * 100 }% ${ focalPoint.y * 100 }%` : undefined,
			backgroundRepeat: 'no-repeat',
			backgroundSize: 'cover',
		};

		if ( VIDEO_BACKGROUND_TYPE === mediaType && poster ) {
			style.backgroundImage = `url(${ poster })`;
		}

		const autoAdvanceAfterOptions = [
			{ value: '', label: __( 'Manual', 'amp' ) },
			{ value: 'auto', label: __( 'Automatic', 'amp' ) },
			{ value: 'time', label: __( 'After a certain time', 'amp' ) },
			{ value: 'media', label: __( 'After media has played', 'amp' ) },
		];

		let autoAdvanceAfterHelp;

		if ( 'media' === autoAdvanceAfter ) {
			autoAdvanceAfterHelp = __( 'Based on the first media block encountered on the page', 'amp' );
		} else if ( 'auto' === autoAdvanceAfter ) {
			autoAdvanceAfterHelp = __( 'Based on the duration of all animated blocks on the page', 'amp' );
		}

		let overlayStyle = {
			width: '100%',
			height: '100%',
			position: 'absolute',
		};

		const backgroundColors = JSON.parse( attributes.backgroundColors );

		overlayStyle = addBackgroundColorToOverlay( overlayStyle, backgroundColors );
		overlayStyle.opacity = overlayOpacity / 100;

		const colorSettings = this.getOverlayColorSettings();
		const isExcessiveVideoSize = VIDEO_BACKGROUND_TYPE === mediaType && isVideoSizeExcessive( media );
		const videoBytesPerSecond = VIDEO_BACKGROUND_TYPE === mediaType ? getVideoBytesPerSecond( media ) : null;

		return (
			<>
				<InspectorControls>
					<PanelColorSettings
						title={ __( 'Background Color', 'amp' ) }
						initialOpen={ false }
						colorSettings={ colorSettings }
					>
						<p>
							{ backgroundColors.length < 2 &&
							<Button
								onClick={ () => this.setBackgroundColors( null, 1 ) }
								isSmall>
								{ __( 'Add Gradient', 'amp' ) }
							</Button>
							}
							{ backgroundColors.length > 1 &&
							<Button
								onClick={ () => this.removeBackgroundColor( backgroundColors.length - 1 ) }
								isLink
								isDestructive>
								{ __( 'Remove Gradient', 'amp' ) }
							</Button>
							}
						</p>
						<RangeControl
							label={ __( 'Opacity', 'amp' ) }
							value={ overlayOpacity }
							onChange={ ( value ) => setAttributes( { overlayOpacity: value } ) }
							min={ 0 }
							max={ 100 }
							step={ 5 }
							required
						/>
					</PanelColorSettings>
					<PanelBody title={ __( 'Background Media', 'amp' ) }>
						<>
							{
								isExcessiveVideoSize &&
								<Notice status="warning" isDismissible={ false } >
									{
										sprintf(
											/* translators: %d: the number of recommended megabytes per second */
											__( 'A video size of less than %d MB per second is recommended.', 'amp' ),
											VIDEO_ALLOWED_MEGABYTES_PER_SECOND
										)
									}
									{
										videoBytesPerSecond && ' ' + sprintf(
											/* translators: %d: the number of actual megabytes per second */
											__( 'The selected video is %d MB per second.', 'amp' ),
											Math.round( videoBytesPerSecond / MEGABYTE_IN_BYTES )
										)
									}
								</Notice>
							}
							<BaseControl>
								<MediaUploadCheck fallback={ instructions }>
									<MediaUpload
										onSelect={ this.onSelectMedia }
										allowedTypes={ ALLOWED_MEDIA_TYPES }
										value={ mediaId }
										render={ ( { open } ) => (
											<Button isDefault isLarge onClick={ open } className="editor-amp-story-page-background">
												{ mediaUrl ? __( 'Change Media', 'amp' ) : __( 'Select Media', 'amp' ) }
											</Button>
										) }
									/>
								</MediaUploadCheck>
								{ !! mediaId &&
								<MediaUploadCheck>
									<Button onClick={ () => setAttributes( { mediaUrl: undefined, mediaId: undefined, mediaType: undefined } ) } isLink isDestructive>
										{ VIDEO_BACKGROUND_TYPE === mediaType ? __( 'Remove Video', 'amp' ) : __( 'Remove image', 'amp' ) }
									</Button>
								</MediaUploadCheck>
								}
							</BaseControl>
							{ VIDEO_BACKGROUND_TYPE === mediaType && (
								<MediaUploadCheck>
									<BaseControl
										id="editor-amp-story-page-poster"
										label={ __( 'Poster Image', 'amp' ) }
										help={ sprintf(
											/* translators: 1: 720p. 2: 720w. 3: 1280h */
											__( 'The recommended dimensions for a poster image are: %1$s (%2$s x %3$s)', 'amp' ),
											'720p',
											'720w',
											'1080h',
										) }
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
												const imageUrl = has( image, [ 'sizes', MAX_IMAGE_SIZE_SLUG, 'url' ] ) ? image.sizes[ MAX_IMAGE_SIZE_SLUG ].url : image.url;
												setAttributes( { poster: imageUrl } );
											} }
											allowedTypes={ POSTER_ALLOWED_MEDIA_TYPES }
											modalClass="editor-amp-story-background-video-poster__media-modal"
											render={ ( { open } ) => (
												<Button
													className={ classnames(
														'editor-amp-story-page-background',
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
															naturalWidth={ 960 }
															naturalHeight={ 1280 }
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
										{
											poster && (
												<Button onClick={ () => setAttributes( { poster: undefined } ) } isLink isDestructive>
													{ __( 'Remove Poster Image', 'amp' ) }
												</Button>
											)
										}
									</BaseControl>
								</MediaUploadCheck>
							) }
							{ mediaUrl && (
								<>
									{ /* Note: FocalPointPicker is only available in Gutenberg 5.1+ */ }
									{ IMAGE_BACKGROUND_TYPE === mediaType && FocalPointPicker && (
										<FocalPointPicker
											label={ __( 'Focal Point Picker', 'amp' ) }
											url={ mediaUrl }
											value={ focalPoint }
											onChange={ ( value ) => setAttributes( { focalPoint: value } ) }
										/>
									) }
								</>
							) }
						</>
					</PanelBody>
					<PanelBody title={ __( 'Page Settings', 'amp' ) }>
						<SelectControl
							label={ __( 'Advance to next page', 'amp' ) }
							help={ autoAdvanceAfterHelp }
							value={ autoAdvanceAfter }
							options={ autoAdvanceAfterOptions }
							onChange={ ( value ) => {
								setAttributes( { autoAdvanceAfter: value } );
								setAttributes( { autoAdvanceAfterDuration: totalAnimationDuration } );
							} }
						/>
						{ 'time' === autoAdvanceAfter && (
							<RangeControl
								label={ __( 'Time in seconds', 'amp' ) }
								value={ autoAdvanceAfterDuration ? parseInt( autoAdvanceAfterDuration ) : 0 }
								onChange={ ( value ) => setAttributes( { autoAdvanceAfterDuration: value } ) }
								min={ Math.max( totalAnimationDuration, 1 ) }
								initialPosition={ totalAnimationDuration }
								help={ totalAnimationDuration > 1 ? __( 'A minimum time is enforced because there are animated blocks on this page', 'amp' ) : undefined }
							/>
						) }
					</PanelBody>
				</InspectorControls>
				<div style={ style }>
					{ /* todo: show poster image as background-image instead */ }
					{ VIDEO_BACKGROUND_TYPE === mediaType && media && (
						<div className="editor-amp-story-page-video-wrap">
							<video autoPlay muted loop className="editor-amp-story-page-video" poster={ poster } ref={ this.videoPlayer }>
								<source src={ mediaUrl } type={ media.mime_type } />
							</video>
						</div>
					) }
					{ backgroundColors.length > 0 && (
						<div style={ overlayStyle } />
					) }
					<InnerBlocks allowedBlocks={ allowedBlocks } />
				</div>
			</>
		);
	}
}

PageEdit.propTypes = {
	clientId: PropTypes.string.isRequired,
	attributes: PropTypes.shape( {
		anchor: PropTypes.string,
		backgroundColors: PropTypes.string,
		mediaId: PropTypes.number,
		mediaType: PropTypes.string,
		mediaUrl: PropTypes.string,
		focalPoint: PropTypes.shape( {
			x: PropTypes.number.isRequired,
			y: PropTypes.number.isRequired,
		} ),
		overlayOpacity: PropTypes.number,
		poster: PropTypes.string,
		autoAdvanceAfter: PropTypes.string,
		autoAdvanceAfterDuration: PropTypes.number,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
	media: PropTypes.object,
	allowedBlocks: PropTypes.arrayOf( PropTypes.string ).isRequired,
	totalAnimationDuration: PropTypes.number.isRequired,
	getBlockOrder: PropTypes.func.isRequired,
	moveBlockToPosition: PropTypes.func.isRequired,
};

export default compose(
	withSelect( ( select, { clientId, attributes } ) => {
		const { getMedia } = select( 'core' );
		const { getBlockOrder, getBlockRootClientId } = select( 'core/block-editor' );

		const isFirstPage = getBlockOrder().indexOf( clientId ) === 0;
		const isCallToActionAllowed = ! isFirstPage && ! getCallToActionBlock( clientId );
		const { getAnimatedBlocks } = select( 'amp/story' );

		const { mediaId } = attributes;

		const animatedBlocks = getAnimatedBlocks();
		const animatedBlocksPerPage = ( animatedBlocks[ clientId ] || [] ).filter( ( { id } ) => clientId === getBlockRootClientId( id ) );
		const totalAnimationDuration = getTotalAnimationDuration( animatedBlocksPerPage );
		const totalAnimationDurationInSeconds = Math.ceil( totalAnimationDuration / 1000 );

		return {
			media: mediaId ? getMedia( mediaId ) : null,
			allowedBlocks: isCallToActionAllowed ? ALLOWED_CHILD_BLOCKS : ALLOWED_MOVABLE_BLOCKS,
			totalAnimationDuration: totalAnimationDurationInSeconds,
			getBlockOrder,
		};
	} ),
	withDispatch( () => {
		const {
			moveBlockToPosition,
		} = dispatch( 'core/block-editor' );
		return {
			moveBlockToPosition,
		};
	} )
)( PageEdit );
