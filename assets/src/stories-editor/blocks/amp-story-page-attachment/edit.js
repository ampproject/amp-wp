/**
 * External dependencies
 */
import classnames from 'classnames';
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
import { Popover, Spinner, withSpokenMessages } from '@wordpress/components';
import { withInstanceId, compose } from '@wordpress/compose';

class PageAttachmentEdit extends Component {
	constructor( props ) {
		super( props );
		this.state = {
			suggestions: [],
			showSuggestions: false,
			selectedSuggestion: null,
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

	componentWillUnmount() {
		delete this.suggestionsRequest;
	}

	onChange( event ) {
		const { setAttributes } = this.props;
		const inputValue = event.target.value;
		//setAttributes( { postId: inputValue } );
		this.updateSuggestions( inputValue );
	}

	updateSuggestions( value ) {
		// Show the suggestions after typing at least 2 characters
		// and also for URLs
		if ( value.length < 2 || /^https?:/.test( value ) ) {
			this.setState( {
				showSuggestions: false,
				selectedSuggestion: null,
				loading: false,
			} );

			return;
		}

		this.setState( {
			showSuggestions: true,
			selectedSuggestion: null,
			loading: true,
		} );

		const request = this.fetchPostSuggestions( value );

		request.then( ( suggestions ) => {
			// A fetch Promise doesn't have an abort option. It's mimicked by
			// comparing the request reference in on the instance, which is
			// reset or deleted on subsequent requests or unmounting.
			if ( this.suggestionsRequest !== request ) {
				return;
			}

			this.setState( {
				suggestions,
				loading: false,
			} );

			if ( !! suggestions.length ) {
				this.props.debouncedSpeak( sprintf( _n(
					'%d result found, use up and down arrow keys to navigate.',
					'%d results found, use up and down arrow keys to navigate.',
					suggestions.length
				), suggestions.length ), 'assertive' );
			} else {
				this.props.debouncedSpeak( __( 'No results.' ), 'assertive' );
			}
		} ).catch( () => {
			if ( this.suggestionsRequest === request ) {
				this.setState( {
					loading: false,
				} );
			}
		} );

		this.suggestionsRequest = request;
	}

	render() {
		const {
			attributes,
			setAttributes,
			isSelected,
			instanceId,
		} = this.props;

		const {
			postId,
		} = attributes;

		const { showSuggestions, suggestions, selectedSuggestion, loading } = this.state;

		const suggestionsListboxId = `block-editor-url-input-suggestions-${ instanceId }`;
		const suggestionOptionIdPrefix = `block-editor-url-input-suggestion-${ instanceId }`;

		return (
			<>
				<div>test</div>
				{ isSelected && (
					<div className="amp-page-attachment-input">
						<input
							id={ 'test' }
							type="text"
							aria-label={ __( 'ID' ) }
							required
							value={ postId }
							onChange={ this.onChange }
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

						{ showSuggestions && !! suggestions.length &&
						<Popover
							position="bottom"
							noArrow
							focusOnMount={ false }
						>
							<div
								className={ classnames(
									'editor-url-input__suggestions',
									'block-editor-url-input__suggestions',
								) }
								id={ suggestionsListboxId }
								ref={ this.autocompleteRef }
								role="listbox"
							>
								{ suggestions.map( ( suggestion, index ) => (
									<button
										key={ suggestion.id }
										role="option"
										tabIndex="-1"
										id={ `${ suggestionOptionIdPrefix }-${ index }` }
										ref={ this.bindSuggestionNode( index ) }
										className={ classnames( 'editor-url-input__suggestion block-editor-url-input__suggestion', {
											'is-selected': index === selectedSuggestion,
										} ) }
										onClick={ () => this.handleOnClick( suggestion ) }
										aria-selected={ index === selectedSuggestion }
									>
										{ suggestion.title }
									</button>
								) ) }
							</div>
						</Popover>
						}
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

export default compose(
	withSpokenMessages,
	withInstanceId,
)( PageAttachmentEdit );
