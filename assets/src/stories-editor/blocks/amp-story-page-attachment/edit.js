/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Component } from '@wordpress/element';
import { apiFetch } from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';

class PageAttachmentEdit extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			suggestions: [],
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
		} ) );
	};

	render() {
		const {
			attributes,
			setAttributes,
			isSelected,
		} = this.props;

		const {
			postId,
		} = attributes;

		return (
			<>
				<div>test</div>
				{ isSelected && (
					<div className={ classnames( 'editor-url-input block-editor-url-input', 'test' ) }>
						<input
							id={ 'test' }
							type="text"
							aria-label={ __( 'ID' ) }
							required
							value={ postId }
							onChange={ ( value ) => setAttributes( { postId: value } ) }
							onInput={ ( event ) => {
								event.stopPropagation();
							} }
							placeholder={ __( 'Type to search', 'amp' ) }
							onKeyDown={ false }
							role="combobox"
							aria-expanded={ showSuggestions }
							aria-autocomplete="list"
							//aria-owns={ suggestionsListboxId }
							//aria-activedescendant={ selectedSuggestion !== null ? `${ suggestionOptionIdPrefix }-${ selectedSuggestion }` : undefined }
							ref={ this.inputRef }
						/>

						{ ( loading ) && <Spinner /> }
					</div>
				) }
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
