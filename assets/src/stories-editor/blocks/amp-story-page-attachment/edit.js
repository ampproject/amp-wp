/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { map } from 'lodash';
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component, RawHTML } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { decodeEntities } from '@wordpress/html-entities';
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

class PageAttachmentEdit extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			suggestions: [],
			showSuggestions: false,
			selectedPost: null,
			isOpen: false,
		};

		this.toggleAttachment = this.toggleAttachment.bind( this );
	}

	/*async fetchPostSuggestions( search ) {
		const posts = await apiFetch( {
			path: addQueryArgs( '/wp/v2/search', {
				search,
				per_page: 20,
				type: 'post',
			} ),
		} );

		return map( posts, ( post ) => ( {
			id: post.id,
			title: decodeEntities( post.title ) || __( '(no title)', 'amp' ),
		} ) );
	}*/

	componentDidMount() {
		const { postId } = this.props.attributes;
		this.isStillMounted = true;
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

	componentWillUnmount() {
		this.isStillMounted = false;
	}

	componentDidUpdate( prevProps ) {
		const {
			isSelected,
			backgroundColor,
			customBackgroundColor,
			textColor,
			setAttributes,
		} = this.props;

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
		} = attributes;

		const { selectedPost } = this.state;

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
								/>
							</div>
							<div className={ attachmentClass } style={ wrapperStyle }>
								{ selectedPost && selectedPost.content && (
									<RawHTML>{ selectedPost.content.rendered }</RawHTML>
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
