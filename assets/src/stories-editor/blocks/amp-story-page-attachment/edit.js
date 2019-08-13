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

class PageAttachmentEdit extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			suggestions: [],
			showSuggestions: false,
			selectedSuggestion: null,
			isOpen: false,
		};
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

	render() {
		const {
			attributes,
			setAttributes,
			isSelected,
		} = this.props;

		const {
			postId,
			theme,
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
				<div>
					<span className="amp-story-page-open-attachment-icon">
						<span className="amp-story-page-open-attachment-bar amp-story-page-open-attachment-bar-left"></span>
						<span className="amp-story-page-open-attachment-bar amp-story-page-open-attachment-bar-right"></span>
					</span>
				</div>
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
