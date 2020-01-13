/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { ReactElement } from 'react';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { RichText } from '@wordpress/block-editor';
import { Button, Spinner } from '@wordpress/components';
import { RawHTML, useEffect, useRef, useState, useCallback } from '@wordpress/element';
import { ENTER, SPACE } from '@wordpress/keycodes';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { PostSelector } from '../../components';
import useElementClickDetector from './element-click-detector';

/**
 * Attachment content that is displayed when the attachment has been opened.
 *
 * Displays a form to select a page/post to be displayed as the actual page attachment content.
 *
 * Once a post has been selected, it displays the post's title and content and offers an option
 * to remove the selection again.
 *
 * @param {Object} props Component props.
 * @param {Function} props.attributes Block attributes.
 * @param {Function} props.setAttributes setAttributes callback.
 * @param {Function} props.toggleAttachment Callback to toggle attachment open/closed state.
 *
 * @return {ReactElement} Element.
 */
const AttachmentContent = ( props ) => {
	const [ selectedPost, setSelectedPost ] = useState( null );
	const [ failedToFetch, setFailedToFetch ] = useState( false );
	const [ searchValue, setSearchValue ] = useState( '' );
	const [ isFetching, setIsFetching ] = useState( false );
	const fetchRequest = useRef( null );
	const isStillMounted = useRef( true );

	const {
		attributes,
		setAttributes,
		toggleAttachment,
	} = props;

	const {
		attachmentClass,
		postId,
		postType,
		title,
		wrapperStyle,
	} = attributes;

	const allowedPostTypes = useSelect( ( select ) => {
		const { getSettings } = select( 'amp/story' );
		const { allowedPageAttachmentPostTypes } = getSettings();
		return allowedPageAttachmentPostTypes;
	}, [] );

	const fetchSelectedPost = useCallback( () => {
		const restBase = allowedPostTypes[ postType ] || `${ postType }s`;

		isStillMounted.current = true;
		if ( postId ) {
			setIsFetching( true );
			const currentFetchRequest = fetchRequest.current = apiFetch( {
				path: `/wp/v2/${ restBase }/${ postId }`,
			} ).then(
				( post ) => {
					if ( isStillMounted.current && fetchRequest.current === currentFetchRequest ) {
						setSelectedPost( post );
						setFailedToFetch( false );
						setIsFetching( false );
					}
				},
			).catch(
				() => {
					if ( isStillMounted.current && fetchRequest.current === currentFetchRequest ) {
						setSelectedPost( null );
						setFailedToFetch( true );
						setIsFetching( false );
					}
				},
			);
		}
	}, [ postId, postType, allowedPostTypes ] );

	useEffect( () => {
		return () => {
			isStillMounted.current = false;
		};
	}, [] );

	useEffect( () => {
		fetchSelectedPost();
	}, [ fetchSelectedPost ] );

	const removePost = () => {
		setFailedToFetch( false );
		setAttributes( { postId: null } );
		setSelectedPost( null );
	};

	// Since the Page Attachment content is "out of it's borders" then clicking
	// on the links might sometimes lose focus instead. Using refs for workaround.
	const removeLinkRef = useRef( null );
	const closeButtonRef = useRef( null );

	useElementClickDetector( closeButtonRef, () => {
		toggleAttachment( false );
	} );

	useElementClickDetector( removeLinkRef, null, true );

	return (
		<div className="attachment-container">
			<div className="attachment-wrapper">
				<div className="attachment-header">
					{ /* This does not use a Button as it replicates the close button on the frontend */ }
					<span
						tabIndex="0"
						className="amp-story-page-attachment-close-button"
						role="button"
						onKeyDown={ ( event ) => {
							if ( ENTER === event.keyCode || SPACE === event.keyCode ) {
								toggleAttachment( false );
								event.stopPropagation();
							}
						} }
						ref={ closeButtonRef }
					/>
					<div className="amp-story-page-attachment-title">
						<RichText
							value={ title }
							tagName="div"
							onChange={ ( value ) => setAttributes( { title: value } ) }
							placeholder={ __( 'Write Title', 'amp' ) }
							onClick={ ( event ) => event.stopPropagation() }
						/>
					</div>
					{ postId && (
						<Button
							className="remove-attachment-post"
							onClick={ ( event ) => {
								event.stopPropagation();
								removePost();
							} }
							isLink
							isDestructive
							ref={ removeLinkRef }
						>
							{ __( 'Remove Post', 'amp' ) }
						</Button>
					) }
				</div>
				<div className={ attachmentClass } style={ wrapperStyle }>
					{ isFetching && <Spinner /> }
					{ ! isFetching && selectedPost && selectedPost.content && (
						<RawHTML>
							{ `<h2>${ selectedPost.title.rendered }</h2>${ selectedPost.content.rendered }` }
						</RawHTML>
					) }
					{ ! isFetching && ( ! postId || failedToFetch ) && (
						<>
							{ failedToFetch && (
								<div>
									<p className="failed-message">
										{ __( 'The selected post failed to load, please select a new post or try loading again.', 'amp' ) }
										<Button
											className="refetch-attachment-post"
											onClick={ ( event ) => {
												event.stopPropagation();
												fetchSelectedPost();
											} }
											isLink
										>
											{ __( ' Try again', 'amp' ) }
										</Button>
									</p>
								</div>
							) }
							<PostSelector
								labelText={ __( 'Attachment Content', 'amp' ) }
								placeholder={ __( 'Search for content ...', 'amp' ) }
								value={ searchValue }
								onSelect={ ( id, type ) => {
									setAttributes( {
										postId: id,
										postType: type,
									} );
									setSearchValue( '' );
								} }
								onChange={ ( value ) => setSearchValue( value ) }
								searchablePostTypes={ Object.keys( allowedPostTypes ) }
							/>
						</>
					) }
				</div>
			</div>
		</div>
	);
};

AttachmentContent.propTypes = {
	attributes: PropTypes.shape( {
		attachmentClass: PropTypes.string,
		postId: PropTypes.number,
		postType: PropTypes.string.isRequired,
		title: PropTypes.string,
		wrapperStyle: PropTypes.object,
		openText: PropTypes.string,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
	toggleAttachment: PropTypes.func.isRequired,
};

export default AttachmentContent;
