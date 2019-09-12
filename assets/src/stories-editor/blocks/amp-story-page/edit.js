/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import {
	InnerBlocks,
	InspectorControls,
} from '@wordpress/block-editor';
import { Component, createRef } from '@wordpress/element';
import {
	withSelect,
	withDispatch,
} from '@wordpress/data';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import {
	addBackgroundColorToOverlay,
	getCallToActionBlock,
	getPageAttachmentBlock,
	getUniqueId,
	metaToAttributeNames,
} from '../../helpers';

import CopyPasteHandler from './copy-paste-handler';

import {
	ALLOWED_MOVABLE_BLOCKS,
	IMAGE_BACKGROUND_TYPE,
	VIDEO_BACKGROUND_TYPE,
} from '../../constants';
import './edit.css';
import BackgroundColorSettings from './background-color-settings';
import PageSettings from './page-settings';
import BackgroundMediaSettings from './background-media-settings';
import AnimationSettings from './animation-settings';

class PageEdit extends Component {
	shouldComponentUpdate() {
		this.ensureCorrectBlockOrder();
		return true;
	}

	constructor( props ) {
		super( props );

		if ( ! props.attributes.anchor ) {
			this.props.setAttributes( { anchor: getUniqueId() } );
		}

		if ( props.storySettingsAttributes ) {
			Object.entries( props.storySettingsAttributes ).forEach( ( [ key, value ] ) => {
				if ( ! props.attributes.hasOwnProperty( key ) ) {
					this.props.setAttributes( { [ key ]: value } );
				}
			} );
		}

		this.state = {
			extractingPoster: false,
		};

		this.videoPlayer = createRef();
	}

	componentDidUpdate( prevProps ) {
		const { attributes } = this.props;
		const { mediaType, mediaUrl } = attributes;

		if ( VIDEO_BACKGROUND_TYPE !== mediaType ) {
			return;
		}

		if ( prevProps.attributes.mediaUrl !== mediaUrl && this.videoPlayer.current ) {
			this.videoPlayer.current.load();
		}
	}

	ensureCorrectBlockOrder() {
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
		const attachmentBlock = getPageAttachmentBlock( clientId );

		let blockToMove = null;

		if ( ctaBlock ) {
			blockToMove = ctaBlock;
		} else if ( attachmentBlock ) {
			blockToMove = attachmentBlock;
		}

		if ( blockToMove ) {
			// If the either CTA or Attachment is not the last block, move it there.
			if ( order[ order.length - 1 ] !== blockToMove.clientId ) {
				moveBlockToPosition( blockToMove.clientId, clientId, clientId, order.length - 1 );
			}
		}
	}

	render() {
		const {
			attributes,
			autoAdvanceAfterOptions,
			clientId,
			isSelected,
			media,
			setAttributes,
			allowedBlocks,
			allowedBackgroundMediaTypes,
			videoFeaturedImage,
		} = this.props;

		const {
			mediaId,
			mediaType,
			mediaUrl,
			mediaAlt,
			focalPoint = { x: 0.5, y: 0.5 },
			overlayOpacity,
			poster,
			autoAdvanceAfter,
			autoAdvanceAfterDuration,
		} = attributes;

		const style = {
			backgroundImage: IMAGE_BACKGROUND_TYPE === mediaType && mediaUrl ? `url(${ mediaUrl })` : undefined,
			backgroundPosition: IMAGE_BACKGROUND_TYPE === mediaType ? `${ focalPoint.x * 100 }% ${ focalPoint.y * 100 }%` : undefined,
			backgroundRepeat: 'no-repeat',
			backgroundSize: 'cover',
		};

		if ( VIDEO_BACKGROUND_TYPE === mediaType && poster ) {
			style.backgroundImage = `url(${ poster })`;
		}

		let overlayStyle = {
			width: '100%',
			height: '100%',
			position: 'absolute',
		};

		const backgroundColors = JSON.parse( attributes.backgroundColors );

		overlayStyle = addBackgroundColorToOverlay( overlayStyle, backgroundColors );
		overlayStyle.opacity = overlayOpacity / 100;

		return (
			<>
				<InspectorControls>
					<BackgroundColorSettings
						backgroundColors={ JSON.parse( attributes.backgroundColors ) }
						setAttributes={ setAttributes }
						overlayOpacity={ overlayOpacity }
					/>
					<BackgroundMediaSettings
						allowedBackgroundMediaTypes={ allowedBackgroundMediaTypes }
						media={ media }
						mediaId={ mediaId }
						mediaType={ mediaType }
						mediaAlt={ mediaAlt }
						mediaUrl={ mediaUrl }
						poster={ poster }
						focalPoint={ focalPoint }
						videoFeaturedImage={ videoFeaturedImage }
						setAttributes={ setAttributes }
					/>
					<PageSettings
						autoAdvanceAfter={ autoAdvanceAfter }
						autoAdvanceAfterDuration={ autoAdvanceAfterDuration }
						autoAdvanceAfterOptions={ autoAdvanceAfterOptions }
						clientId={ clientId }
						setAttributes={ setAttributes }
					/>
					<AnimationSettings clientId={ clientId } />
				</InspectorControls>
				<CopyPasteHandler clientId={ clientId } isSelected={ isSelected }>
					<div
						style={ style }
					>
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
				</CopyPasteHandler>
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
		mediaAlt: PropTypes.string,
	} ).isRequired,
	isSelected: PropTypes.bool.isRequired,
	setAttributes: PropTypes.func.isRequired,
	media: PropTypes.object,
	allowedBlocks: PropTypes.arrayOf( PropTypes.string ).isRequired,
	getBlockOrder: PropTypes.func.isRequired,
	moveBlockToPosition: PropTypes.func.isRequired,
	videoFeaturedImage: PropTypes.shape( {
		source_url: PropTypes.string,
	} ),
	storySettingsAttributes: PropTypes.shape( {
		autoAdvanceAfter: PropTypes.string,
		autoAdvanceAfterDuration: PropTypes.number,
	} ),
	autoAdvanceAfterOptions: PropTypes.array,
	allowedBackgroundMediaTypes: PropTypes.arrayOf( PropTypes.string ).isRequired,
};

export default compose(
	withDispatch( ( dispatch ) => {
		const { moveBlockToPosition } = dispatch( 'core/block-editor' );
		return {
			moveBlockToPosition,
		};
	} ),
	withSelect( ( select, { clientId, attributes } ) => {
		const { getMedia } = select( 'core' );
		const { getBlockOrder } = select( 'core/block-editor' );
		const { getSettings } = select( 'amp/story' );

		const isFirstPage = getBlockOrder().indexOf( clientId ) === 0;
		const isCallToActionAllowed = ! isFirstPage && ! getCallToActionBlock( clientId ) && ! getPageAttachmentBlock( clientId );
		const isPageAttachmentAllowed = ! getCallToActionBlock( clientId ) && ! getPageAttachmentBlock( clientId );

		const { mediaType, mediaId, poster } = attributes;

		const media = mediaId ? getMedia( mediaId ) : undefined;

		let videoFeaturedImage;

		if ( VIDEO_BACKGROUND_TYPE === mediaType && media && media.featured_media && ! poster ) {
			videoFeaturedImage = getMedia( media.featured_media );
		}

		let allowedBlocks = ALLOWED_MOVABLE_BLOCKS;

		if ( isCallToActionAllowed ) {
			allowedBlocks = [
				...allowedBlocks,
				'amp/amp-story-cta',
			];
		}
		if ( isPageAttachmentAllowed ) {
			allowedBlocks = [
				...allowedBlocks,
				'amp/amp-story-page-attachment',
			];
		}

		const { getEditedPostAttribute } = select( 'core/editor' );
		const postMeta = getEditedPostAttribute( 'meta' ) || {};
		const storySettingsAttributes = metaToAttributeNames( postMeta );
		const { allowedVideoMimeTypes, storySettings } = getSettings();
		const { autoAdvanceAfterOptions } = storySettings || {};

		return {
			media,
			videoFeaturedImage,
			allowedBlocks,
			getBlockOrder,
			storySettingsAttributes,
			autoAdvanceAfterOptions,
			allowedBackgroundMediaTypes: [ IMAGE_BACKGROUND_TYPE, ...allowedVideoMimeTypes ],
		};
	} ),
)( PageEdit );
