/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, RawHTML } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { RichText } from '@wordpress/block-editor';
import { select } from '@wordpress/data';
import { Button } from '@wordpress/components';

/**
 * Internal dependencies
 */
import './edit.css';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import { PostSelector } from '../../components';
import AttachmentOpener from './attachment-opener';

class PageAttachmentEdit extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			selectedPost: null,
			isOpen: false,
			searchValue: '',
			failedToFetch: false,
		};

		this.toggleAttachment = this.toggleAttachment.bind( this );
		this.removePost = this.removePost.bind( this );
	}

	componentDidMount() {
		this.fetchSelectedPost();
	}

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	componentDidUpdate( prevProps ) {
		const {
			attributes,
			backgroundColor,
			customBackgroundColor,
			textColor,
			setAttributes,
		} = this.props;

		if ( attributes.postId !== prevProps.attributes.postId ) {
			this.fetchSelectedPost();
		}

		if (
			backgroundColor !== prevProps.backgroundColor ||
			customBackgroundColor !== prevProps.customBackgroundColor ||
			textColor !== prevProps.textColor
		) {
			const { style, attachmentClass } = this.getWrapperAttributes();
			const newAttributes = { wrapperStyle: style };
			if ( textColor !== prevProps.textColor ) {
				newAttributes.attachmentClass = attachmentClass;
			}
			setAttributes( newAttributes );
		}
	}

	fetchSelectedPost() {
		const { postId, postType } = this.props.attributes;
		this.isStillMounted = true;
		if ( postId ) {
			const fetchRequest = this.fetchRequest = apiFetch( {
				path: `/wp/v2/${ postType }s/${ postId }`,
			} ).then(
				( post ) => {
					if ( this.isStillMounted && this.fetchRequest === fetchRequest ) {
						this.setState( {
							selectedPost: post,
							failedToFetch: false,
						} );
					}
				}
			).catch(
				() => {
					if ( this.isStillMounted && this.fetchRequest === fetchRequest ) {
						this.setState( {
							selectedPost: null,
							failedToFetch: true,
						} );
					}
				}
			);
		}
	}

	toggleAttachment( open ) {
		if ( open !== this.state.isOpen ) {
			this.setState( { isOpen: open } );
		}
	}

	getWrapperAttributes() {
		const {
			attributes,
			backgroundColor,
			customBackgroundColor,
			textColor,
		} = this.props;

		const {
			opacity,
		} = attributes;
		const { colors } = select( 'core/block-editor' ).getSettings();
		const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

		const attachmentClass = classnames( 'amp-page-attachment-content', {
			'has-text-color': textColor.color,
			[ textColor.class ]: textColor.class,
		} );
		const attachmentStyle = {
			color: textColor.color,
			backgroundColor: appliedBackgroundColor,
		};
		return {
			style: attachmentStyle,
			attachmentClass,
		};
	}

	removePost() {
		this.props.setAttributes( { postId: null } );
		this.setState( {
			selectedPost: null,
			failedToFetch: false,
		} );
	}

	render() {
		const {
			attributes,
			setAttributes,
		} = this.props;

		const {
			openText,
			title,
			wrapperStyle,
			attachmentClass,
			postId,
		} = attributes;

		const { selectedPost, searchValue, failedToFetch } = this.state;

		return (
			<>
				{ this.state.isOpen &&
					<div className="attachment-container">
						<div className="attachment-wrapper">
							<div className="attachment-header">
								<span
									onClick={ () => {
										this.toggleAttachment( false );
									} }
									tabIndex="0"
									className="amp-story-page-attachment-close-button"
									role="button"
									onKeyDown={ () => {
										// @todo
									} }
								/>
								<RichText
									value={ title }
									tagName="span"
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
											this.removePost();
										} }
										isLink
										isDestructive>
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
											placeholder={ __( 'Search & select a post or page to embed content.', 'amp' ) }
											value={ searchValue }
											onSelect={ ( id, postType ) => {
												setAttributes( {
													postId: id,
													postType,
												} );
												this.setState( { searchValue: '' } );
											} }
											onChange={ ( value ) => this.setState( { searchValue: value } ) }
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
				}
				{ ! this.state.isOpen &&
					<AttachmentOpener
						setAttributes={ setAttributes }
						toggleAttachment={ this.toggleAttachment }
						openText={ openText }
					/>
				}
			</>
		);
	}
}

PageAttachmentEdit.propTypes = {
	attributes: PropTypes.shape( {
		opacity: PropTypes.number,
		postId: PropTypes.number,
		postType: PropTypes.string.isRequired,
		wrapperStyle: PropTypes.object,
		openText: PropTypes.string,
		title: PropTypes.string,
		attachmentClass: PropTypes.string,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
	backgroundColor: PropTypes.shape( {
		color: PropTypes.string,
		name: PropTypes.string,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
	customBackgroundColor: PropTypes.string,
	textColor: PropTypes.shape( {
		color: PropTypes.string,
		name: PropTypes.string,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
};

export default PageAttachmentEdit;
