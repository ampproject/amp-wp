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
import { InspectorControls, RichText } from '@wordpress/block-editor';
import { select } from '@wordpress/data';
import {
	SelectControl,
	PanelBody,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import './edit.css';
import { getBackgroundColorWithOpacity } from '../../../common/helpers';
import { PostSelector } from '../../components';

class PageAttachmentEdit extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			selectedPost: null,
			isOpen: false,
			searchValue: '',
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
			isSelected,
			backgroundColor,
			customBackgroundColor,
			textColor,
			setAttributes,
		} = this.props;

		if ( attributes.postId !== prevProps.attributes.postId ) {
			this.fetchSelectedPost();
		}

		if ( ! isSelected && prevProps.isSelected ) {
			this.toggleAttachment( false );
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
		const { postId } = this.props.attributes;
		this.isStillMounted = true;
		if ( postId ) {
			const fetchRequest = this.fetchRequest = apiFetch( {
				path: `/wp/v2/posts/${ postId }`,
			} ).then(
				( post ) => {
					if ( this.isStillMounted && this.fetchRequest === fetchRequest ) {
						this.setState( { selectedPost: post } );
					}
				}
			).catch(
				() => {
					if ( this.isStillMounted && this.fetchRequest === fetchRequest ) {
						this.setState( { selectedPost: null } );
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
		this.setState( { selectedPost: null } );
	}

	render() {
		const {
			attributes,
			setAttributes,
		} = this.props;

		const {
			theme,
			text,
			title,
			wrapperStyle,
			attachmentClass,
			postId,
		} = attributes;

		const { selectedPost, searchValue } = this.state;

		const themeOptions = [
			{ value: 'light', label: __( 'Light', 'amp' ) },
			{ value: 'dark', label: __( 'Dark', 'amp' ) },
		];

		return (
			<>
				<InspectorControls>
					<PanelBody title={ __( 'Page Attachment Settings', 'amp' ) }>
						<SelectControl
							label={ __( 'Theme', 'amp' ) }
							value={ theme }
							options={ themeOptions }
							onChange={ ( value ) => {
								setAttributes( { theme: value } );
							} }
						/>
					</PanelBody>
				</InspectorControls>
				{ this.state.isOpen &&
					<div className={ classnames( 'attachment-container', {
						'theme-dark': 'dark' === theme,
					} ) }>
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
									<RawHTML>{ selectedPost.content.rendered }</RawHTML>
								) }
								{ ! postId && (
									<PostSelector
										placeholder={ __( 'Search & select a post to embed content.', 'amp' ) }
										value={ searchValue }
										onSelect={ ( value ) => {
											setAttributes( { postId: value } );
											this.setState( { searchValue: '' } );
										} }
										onChange={ ( value ) => this.setState( { searchValue: value } ) }
									/>
								) }
							</div>
						</div>
					</div>
				}
				{ ! this.state.isOpen &&
				<div className="open-attachment-wrapper">
					<span
						role="button"
						tabIndex="0"
						onClick={ () => {
							this.toggleAttachment( true );
						} }
						onKeyDown={ () => {
							// @todo
						} }
						className="amp-story-page-open-attachment-icon"
					>
						<span className="amp-story-page-open-attachment-bar amp-story-page-open-attachment-bar-left" />
						<span className="amp-story-page-open-attachment-bar amp-story-page-open-attachment-bar-right" />
					</span>
					<RichText
						value={ text }
						tagName="span"
						wrapperClassName="amp-story-page-attachment__text"
						onChange={ ( value ) => setAttributes( { text: value } ) }
						placeholder={ __( 'Write CTA Text', 'amp' ) }
					/>
				</div>
				}
			</>
		);
	}
}

PageAttachmentEdit.propTypes = {
	attributes: PropTypes.shape( {
		opacity: PropTypes.number,
		postId: PropTypes.number,
		wrapperStyle: PropTypes.object,
		text: PropTypes.string,
		theme: PropTypes.string,
		title: PropTypes.string,
		attachmentClass: PropTypes.string,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
	isSelected: PropTypes.bool,
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
