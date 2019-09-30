/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';
import { has } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __, _x, sprintf } from '@wordpress/i18n';
import {
	BaseControl,
	Button,
	FocalPointPicker,
	Notice,
	PanelBody,
	ResponsiveWrapper,
	TextControl,
} from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { MediaUpload, MediaUploadCheck } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { getVideoBytesPerSecond, isVideoSizeExcessive } from '../../../common/helpers';
import {
	MEGABYTE_IN_BYTES,
	VIDEO_ALLOWED_MEGABYTES_PER_SECOND,
} from '../../../common/constants';
import {
	IMAGE_BACKGROUND_TYPE,
	MAX_IMAGE_SIZE_SLUG,
	POSTER_ALLOWED_MEDIA_TYPES,
	VIDEO_BACKGROUND_TYPE,
} from '../../constants';
import { getPosterImageFromFileObj, processMedia, uploadVideoFrame } from '../../helpers';

/**
 * Displays the page background media settings.
 *
 * @param {Object} props Component props.
 * @param {Array} props.allowedBackgroundMediaTypes
 * @param {Object} props.media Media object.
 * @param {number} props.mediaId Media ID.
 * @param {string} props.mediaType Media type.
 * @param {string} props.mediaAlt Media alt text.
 * @param {string} props.mediaUrl Media URL.
 * @param {string} props.poster Poster URL.
 * @param {Object} props.focalPoint Focal point configuration.
 * @param {Object} props.videoFeaturedImage Video featured image object.
 * @param {Function} props.setAttributes setAttributes callback.
 * @return {ReactElement} Component.
 */
const BackgroundMediaSettings = ( {
	allowedBackgroundMediaTypes,
	media,
	mediaId,
	mediaType,
	mediaAlt,
	mediaUrl,
	poster,
	focalPoint,
	videoFeaturedImage,
	setAttributes,
} ) => {
	const instructions = <p>{ __( 'To edit the background image or video, you need permission to upload media.', 'amp' ) }</p>;

	const isExcessiveVideoSize = VIDEO_BACKGROUND_TYPE === mediaType && isVideoSizeExcessive( getVideoBytesPerSecond( media ) );
	const videoBytesPerSecond = VIDEO_BACKGROUND_TYPE === mediaType ? getVideoBytesPerSecond( media ) : null;

	/**
	 * Media selection callback.
	 *
	 * @param {Object} item            Media object.
	 * @param {string} item.icon       Media icon.
	 * @param {string} item.url        Media URL.
	 * @param {string} item.media_type Media type.
	 * @param {string} item.type       Media type if it was an existing attachment.
	 * @param {number} item.id         Attachment ID.
	 * @param {Object} item.image      Media image object.
	 * @param {string} item.image.src  Media image URL
	 */
	const onSelectMedia = ( item ) => {
		const processed = processMedia( item );
		setAttributes( processed );
	};

	const [ isExtractingPoster, setExtractingPoster ] = useState( false );

	useEffect( () => {
		if ( VIDEO_BACKGROUND_TYPE !== mediaType ) {
			return;
		}

		if ( poster ) {
			return;
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

			uploadVideoFrame( { id: mediaId, src: mediaUrl } )
				.then( ( fileObj ) => {
					setAttributes( { poster: getPosterImageFromFileObj( fileObj ) } );
					setExtractingPoster( false );
				} )
				.catch( () => setExtractingPoster( false ) );
		}
	}, [ media, mediaId, mediaType, mediaUrl, poster, videoFeaturedImage, isExtractingPoster, setAttributes ] );

	return (
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
							onSelect={ onSelectMedia }
							allowedTypes={ allowedBackgroundMediaTypes }
							value={ mediaId }
							render={ ( { open } ) => (
								<Button isDefault isLarge onClick={ open } className="editor-amp-story-page-background">
									{ mediaUrl ? __( 'Change Media', 'amp' ) : __( 'Select Media', 'amp' ) }
								</Button>
							) }
							id="story-background-media"
						/>
						{ mediaUrl && (
							<Button onClick={ () => setAttributes( { mediaUrl: undefined, mediaId: undefined, mediaType: undefined } ) } isLink isDestructive>
								{ _x( 'Remove', 'background media', 'amp' ) }
							</Button>
						) }
					</MediaUploadCheck>
				</BaseControl>
				{ VIDEO_BACKGROUND_TYPE === mediaType && ( ! isExtractingPoster || poster ) && (
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
										id="editor-amp-story-page-poster"
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
						</BaseControl>
					</MediaUploadCheck>
				) }
				{ IMAGE_BACKGROUND_TYPE === mediaType && mediaUrl && FocalPointPicker && (
					<FocalPointPicker
						label={ __( 'Focal Point Picker', 'amp' ) }
						url={ mediaUrl }
						value={ focalPoint }
						onChange={ ( value ) => setAttributes( { focalPoint: value } ) }
					/>
				) }
				{ mediaType && (
					<TextControl
						label={ __( 'Assistive Text', 'amp' ) }
						help={ __( 'This text is used to inform visually impaired users about the background content.', 'amp' ) }
						value={ mediaAlt }
						onChange={ ( label ) => setAttributes( { mediaAlt: label } ) }
					/>
				) }
			</>
		</PanelBody>
	);
};

BackgroundMediaSettings.propTypes = {
	allowedBackgroundMediaTypes: PropTypes.arrayOf( PropTypes.string ).isRequired,
	media: PropTypes.object,
	mediaType: PropTypes.string,
	mediaId: PropTypes.number,
	mediaAlt: PropTypes.string,
	mediaUrl: PropTypes.string,
	poster: PropTypes.string,
	focalPoint: PropTypes.shape( {
		x: PropTypes.number.isRequired,
		y: PropTypes.number.isRequired,
	} ),
	videoFeaturedImage: PropTypes.shape( {
		source_url: PropTypes.string,
	} ),
	setAttributes: PropTypes.func.isRequired,
};

export default BackgroundMediaSettings;
