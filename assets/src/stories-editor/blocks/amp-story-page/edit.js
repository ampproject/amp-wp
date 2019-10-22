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
import { useEffect, useRef } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { _n, sprintf } from '@wordpress/i18n';

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

import {
	ALLOWED_MOVABLE_BLOCKS,
	IMAGE_BACKGROUND_TYPE,
	VIDEO_BACKGROUND_TYPE,
} from '../../constants';
import CopyPasteHandler from './copy-paste-handler';
import './edit.css';
import BackgroundColorSettings from './background-color-settings';
import PageSettings from './page-settings';
import BackgroundMediaSettings from './background-media-settings';
import AnimationSettings from './animation-settings';
import RemoveButton from './remove-button';

const PageEdit = ( {
	attributes,
	clientId,
	isSelected,
	setAttributes,
} ) => {
	const {
		anchor,
		mediaId,
		mediaType,
		mediaUrl,
		mediaAlt,
		focalPoint = { x: 0.5, y: 0.5 },
		overlayOpacity,
		poster,
		autoAdvanceAfter,
		autoAdvanceAfterDuration,
		backgroundColors,
	} = attributes;

	const { moveBlockToPosition, removeBlocks } = useDispatch( 'core/block-editor' );
	const { createErrorNotice } = useDispatch( 'core/notices' );

	const {
		media,
		videoFeaturedImage,
		pagesOrder,
		childrenOrder,
		immovableBlocks,
		storySettingsAttributes,
		autoAdvanceAfterOptions,
		allowedVideoMimeTypes,
	} = useSelect( ( select ) => {
		const { getMedia } = select( 'core' );
		const { getBlockOrder, getBlocksByClientId } = select( 'core/block-editor' );
		const { getSettings } = select( 'amp/story' );

		const mediaObject = mediaId ? getMedia( mediaId ) : undefined;

		let videoThumbnail;

		if ( VIDEO_BACKGROUND_TYPE === mediaType && mediaObject && mediaObject.featured_media && ! poster ) {
			videoThumbnail = getMedia( mediaObject.featured_media );
		}

		const { getEditedPostAttribute } = select( 'core/editor' );
		const postMeta = getEditedPostAttribute( 'meta' ) || {};
		const { storySettings } = getSettings();
		const innerBlocksIds = getBlockOrder( clientId );
		const innerBlocks = getBlocksByClientId( innerBlocksIds );

		return {
			media: mediaObject,
			videoFeaturedImage: videoThumbnail,
			immovableBlocks: innerBlocks.filter( ( { name } ) => ! ALLOWED_MOVABLE_BLOCKS.includes( name ) ),
			pagesOrder: getBlockOrder(),
			childrenOrder: innerBlocksIds,
			storySettingsAttributes: metaToAttributeNames( postMeta ),
			autoAdvanceAfterOptions: storySettings.autoAdvanceAfterOptions,
			allowedVideoMimeTypes: getSettings().allowedVideoMimeTypes,
		};
	}, [ mediaId, mediaType, poster, clientId ] );

	const allowedBackgroundMediaTypes = [ IMAGE_BACKGROUND_TYPE, ...allowedVideoMimeTypes ];

	const isFirstPage = pagesOrder.indexOf( clientId ) === 0;
	const isCallToActionAllowed = ! isFirstPage && ! getCallToActionBlock( clientId ) && ! getPageAttachmentBlock( clientId );
	const isPageAttachmentAllowed = ! getCallToActionBlock( clientId ) && ! getPageAttachmentBlock( clientId );

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

	useEffect( () => {
		if ( ! anchor ) {
			setAttributes( { anchor: getUniqueId() } );
		}
	}, [ anchor, setAttributes ] );

	useEffect( () => {
		if ( storySettingsAttributes ) {
			Object.entries( storySettingsAttributes ).forEach( ( [ key, value ] ) => {
				if ( ! attributes.hasOwnProperty( key ) ) {
					setAttributes( { [ key ]: value } );
				}
			} );
		}
	}, [ storySettingsAttributes, setAttributes, attributes ] );

	const videoPlayer = useRef();

	useEffect( () => {
		if ( videoPlayer.current ) {
			videoPlayer.current.load();
		}
	}, [ mediaType, mediaUrl ] );

	// If there is more than one immovable block, leave only the last and remove the others.
	useEffect( () => {
		if ( immovableBlocks.length > 1 ) {
			const blocksToRemove = immovableBlocks.slice( 0, -1 ).map( ( { clientId: blockId } ) => blockId );
			removeBlocks( blocksToRemove );
			const removeMessage = sprintf(
				_n(
					'%d block removed. Only one CTA/Attachment block allowed per Page.',
					'%d blocks removed. Only one CTA/Attachment block allowed per Page.',
					blocksToRemove.length,
					'amp'
				),
				blocksToRemove.length
			);
			createErrorNotice(
				removeMessage,
				{
					type: 'snackbar',
					isDismissible: true,
				}
			);
		}
	}, [ childrenOrder, clientId, createErrorNotice, immovableBlocks, removeBlocks ] );

	useEffect( () => {
		if ( childrenOrder.length <= 1 ) {
			return;
		}
		// If there is an illegal number of immovable blocks (more than 1).
		if ( 1 !== immovableBlocks.length ) {
			return;
		}
		const blockToMove = immovableBlocks[ 0 ];

		if ( blockToMove ) {
			// If the either CTA or Attachment is not the last block, move it there.
			if ( childrenOrder[ childrenOrder.length - 1 ] !== blockToMove.clientId ) {
				moveBlockToPosition( blockToMove.clientId, clientId, clientId, childrenOrder.length - 1 );
			}
		}
	}, [ childrenOrder, clientId, moveBlockToPosition, immovableBlocks ] );

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

	const bgColors = JSON.parse( backgroundColors );

	overlayStyle = addBackgroundColorToOverlay( overlayStyle, bgColors );
	overlayStyle.opacity = overlayOpacity / 100;

	return (
		<>
			<InspectorControls>
				<RemoveButton clientId={ clientId } />
				<BackgroundColorSettings
					backgroundColors={ bgColors }
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
							<video autoPlay muted loop className="editor-amp-story-page-video" poster={ poster } ref={ videoPlayer }>
								<source src={ mediaUrl } type={ media.mime_type } />
							</video>
						</div>
					) }
					{ bgColors.length > 0 && (
						<div style={ overlayStyle } />
					) }
					<InnerBlocks allowedBlocks={ allowedBlocks } />
				</div>
			</CopyPasteHandler>
		</>
	);
};

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
	isSelected: PropTypes.bool,
	setAttributes: PropTypes.func.isRequired,
};

export default PageEdit;
