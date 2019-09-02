/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import apiFetch from '@wordpress/api-fetch';
import { RichText } from '@wordpress/block-editor';
import { Button } from '@wordpress/components';
import { RawHTML, useEffect, useRef, useState } from '@wordpress/element';
import { ENTER, SPACE } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import { PostSelector } from '../../components';
import useElementClickDetector from './element-click-detector';

const AttachmentContent = ( props ) => {
	const [ selectedPost, setSelectedPost ] = useState( null );
	const [ failedToFetch, setFailedToFetch ] = useState( false );
	const [ searchValue, setSearchValue ] = useState( '' );
	let fetchRequest, isStillMounted;

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

	const fetchSelectedPost = () => {
		isStillMounted = true;
		if ( postId ) {
			const currentFetchRequest = fetchRequest = apiFetch( {
				path: `/wp/v2/${ postType }s/${ postId }`,
			} ).then(
				( post ) => {
					if ( isStillMounted && fetchRequest === currentFetchRequest ) {
						setSelectedPost( post );
						setFailedToFetch( false );
					}
				}
			).catch(
				() => {
					if ( isStillMounted && fetchRequest === currentFetchRequest ) {
						setSelectedPost( null );
						setFailedToFetch( true );
					}
				}
			);
		}
	};

	useEffect( () => {
		return () => {
			isStillMounted = false;
		};
	}, [] );

	useEffect( () => {
		fetchSelectedPost();
	}, [ postId ] );

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
					<RichText
						value={ title }
						tagName="div"
						wrapperClassName="amp-story-page-attachment-title"
						onChange={ ( value ) => setAttributes( { title: value } ) }
						placeholder={ __( 'Write Title', 'amp' ) }
						onClick={ ( event ) => event.stopPropagation() }
					/>
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
					{ selectedPost && selectedPost.content && (
						<RawHTML>
							{ `<h2>${ selectedPost.title.rendered }</h2>${ selectedPost.content.rendered }` }
						</RawHTML>
					) }
					{ ( ! postId || failedToFetch ) && (
						<>
							{ failedToFetch && (
								<span>{ __( 'The selected post failed to load, please select a new post', 'amp' ) }</span>
							) }
							<PostSelector
								labelText={ __( 'Attachment Content', 'amp' ) }
								placeholder={ __( 'Search for a post or page ...', 'amp' ) }
								value={ searchValue }
								onSelect={ ( id, type ) => {
									setAttributes( {
										postId: id,
										postType: type,
									} );
									setSearchValue( '' );
								} }
								onChange={ ( value ) => setSearchValue( value ) }
								searchablePostTypes={ [
									'page',
									'post',
								] }
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
