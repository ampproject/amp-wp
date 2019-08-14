/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { map } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { apiFetch } from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { decodeEntities } from '@wordpress/html-entities';
import { InspectorControls } from '@wordpress/block-editor';
import {
	SelectControl,
	PanelBody,
} from '@wordpress/components';

/**
 * Internal dependencies
 */
import './edit.css';

class PageAttachmentEdit extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			suggestions: [],
			showSuggestions: false,
			selectedSuggestion: null,
			isOpen: false,
		};

		this.toggleAttachment = this.toggleAttachment.bind( this );
	}

	async fetchPostSuggestions( search ) {
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
	}

	componentDidUpdate( prevProps ) {
		const { isSelected } = this.props;

		if ( ! isSelected && prevProps.isSelected ) {
			this.toggleAttachment( false );
		}
	}

	toggleAttachment( open ) {
		if ( open !== this.state.isOpen ) {
			this.setState( { isOpen: open } );
		}
	}

	render() {
		const {
			attributes,
			setAttributes,
			isSelected,
		} = this.props;

		const {
			postId,
			theme,
			text,
		} = attributes;

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
					<div className="attachment-container">
						<div className="amp-page-attachment-content">
							Content here!
						</div>
					</div>
				}
				{ ! this.state.isOpen && <div className="open-attachment-wrapper"
					onClick={ () => {
						this.toggleAttachment( true );
					} }
				>
					<span
						className="amp-story-page-open-attachment-icon"
					>
						<span className="amp-story-page-open-attachment-bar amp-story-page-open-attachment-bar-left"></span>
						<span className="amp-story-page-open-attachment-bar amp-story-page-open-attachment-bar-right"></span>
					</span>
					<span>{ text }</span>
				</div>
				}
			</>
		);
	}
}

PageAttachmentEdit.propTypes = {
	attributes: PropTypes.shape( {
		text: PropTypes.string,
	} ).isRequired,
};

export default PageAttachmentEdit;
