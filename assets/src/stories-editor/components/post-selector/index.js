/**
 * External dependencies
 */
import { map, throttle } from 'lodash';
import classnames from 'classnames';
import scrollIntoView from 'dom-scroll-into-view';

/**
 * WordPress dependencies
 */
import { __, sprintf, _n } from '@wordpress/i18n';
import { Component, createRef } from '@wordpress/element';
import { UP, DOWN, ENTER, TAB } from '@wordpress/keycodes';
import { Spinner, withSpokenMessages, Popover } from '@wordpress/components';
import { withInstanceId, withSafeTimeout, compose } from '@wordpress/compose';
import apiFetch from '@wordpress/api-fetch';
import { addQueryArgs } from '@wordpress/url';
import { decodeEntities } from '@wordpress/html-entities';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './edit.css';

const stopEventPropagation = ( event ) => event.stopPropagation();

/**
 * PostSelector component, largely based on the logic of the Upstream URLInput.
 *
 * Allows searching for a post of a given post type and displays them in a dropdown to select from.
 */
class PostSelector extends Component {
	constructor( props ) {
		super( props );

		const { autocompleteRef } = props;
		this.onChange = this.onChange.bind( this );
		this.onKeyDown = this.onKeyDown.bind( this );
		this.autocompleteRef = autocompleteRef || createRef();
		this.inputRef = createRef();
		this.updateSuggestions = throttle( this.updateSuggestions.bind( this ), 200 );

		this.suggestionNodes = [];

		this.state = {
			suggestions: [],
			showSuggestions: false,
			selectedSuggestion: null,
		};
	}

	componentDidUpdate() {
		const { showSuggestions, selectedSuggestion } = this.state;
		if ( showSuggestions && selectedSuggestion !== null && ! this.scrollingIntoView ) {
			this.scrollingIntoView = true;
			scrollIntoView( this.suggestionNodes[ selectedSuggestion ], this.autocompleteRef.current, {
				onlyScrollIfNeeded: true,
			} );

			this.props.setTimeout( () => {
				this.scrollingIntoView = false;
			}, 100 );
		}
	}

	componentWillUnmount() {
		delete this.suggestionsRequest;
		this.isStillMounted = false;
	}

	componentDidMount() {
		this.isStillMounted = true;
	}

	bindSuggestionNode( index ) {
		return ( ref ) => {
			this.suggestionNodes[ index ] = ref;
		};
	}

	fetchPostSuggestions( search ) {
		const searchablePostTypes = this.props.searchablePostTypes || [ 'post' ];
		const suggestionsRequest = this.suggestionsRequest = apiFetch( {
			path: addQueryArgs( '/wp/v2/search', {
				search,
				per_page: 20,
				subtype: searchablePostTypes.join( ',' ),
			} ),
		} ).then(
			( suggestions ) => {
				if ( this.isStillMounted && this.suggestionsRequest === suggestionsRequest ) {
					this.setState( {
						suggestions: map( suggestions, ( post ) => ( {
							id: post.id,
							title: decodeEntities( post.title ) || __( '(no title)', 'amp' ),
							postType: post.subtype,
						} ) ),
						loading: false,
					} );

					if ( Boolean( suggestions.length ) ) {
						this.props.debouncedSpeak( sprintf( _n(
							'%d result found, use up and down arrow keys to navigate.',
							'%d results found, use up and down arrow keys to navigate.',
							suggestions.length, 'amp'
						), suggestions.length ), 'assertive' );
					} else {
						this.props.debouncedSpeak( __( 'No results.', 'amp' ), 'assertive' );
					}
				}
			}
		).catch(
			() => {
				if ( this.isStillMounted && this.suggestionsRequest === suggestionsRequest ) {
					this.setState( {
						loading: false,
					} );
				}
			}
		);
	}

	updateSuggestions( value ) {
		// Show the suggestions after typing at least 2 characters.
		if ( value.length < 2 ) {
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

		this.fetchPostSuggestions( value );
	}

	onChange( event ) {
		const inputValue = event.target.value;
		this.props.onChange( inputValue );
		this.updateSuggestions( inputValue );
	}

	onKeyDown( event ) {
		const { showSuggestions, selectedSuggestion, suggestions, loading } = this.state;
		// If the suggestions are not shown or loading, we shouldn't handle the arrow keys.
		if ( ! showSuggestions || ! suggestions.length || loading ) {
			switch ( event.keyCode ) {
				// When UP is pressed, if the caret is at the start of the text, move it to the 0
				// position.
				case UP: {
					if ( 0 !== event.target.selectionStart ) {
						event.stopPropagation();
						event.preventDefault();

						// Set the input caret to position 0
						event.target.setSelectionRange( 0, 0 );
					}
					break;
				}
				// When DOWN is pressed, if the caret is not at the end of the text, move it to the
				// last position.
				case DOWN: {
					if ( this.props.value.length !== event.target.selectionStart ) {
						event.stopPropagation();
						event.preventDefault();

						// Set the input caret to the last position
						event.target.setSelectionRange( this.props.value.length, this.props.value.length );
					}
					break;
				}
				default:
					break;
			}

			return;
		}

		const suggestion = this.state.suggestions[ this.state.selectedSuggestion ];

		switch ( event.keyCode ) {
			case UP: {
				event.stopPropagation();
				event.preventDefault();
				const previousIndex = ! selectedSuggestion ? suggestions.length - 1 : selectedSuggestion - 1;
				this.setState( {
					selectedSuggestion: previousIndex,
				} );
				break;
			}
			case DOWN: {
				event.stopPropagation();
				event.preventDefault();
				const nextIndex = selectedSuggestion === null || ( selectedSuggestion === suggestions.length - 1 ) ? 0 : selectedSuggestion + 1;
				this.setState( {
					selectedSuggestion: nextIndex,
				} );
				break;
			}
			case TAB: {
				if ( this.state.selectedSuggestion !== null ) {
					this.selectPost( suggestion );
					this.props.speak( __( 'Post selected.', 'amp' ) );
				}
				break;
			}
			case ENTER: {
				if ( this.state.selectedSuggestion !== null ) {
					event.stopPropagation();
					this.selectPost( suggestion );
				}
				break;
			}
			default:
				break;
		}
	}

	selectPost( suggestion ) {
		this.props.onSelect( suggestion.id, suggestion.postType );
		this.setState( {
			selectedSuggestion: null,
			showSuggestions: false,
		} );
	}

	handleOnClick( suggestion ) {
		this.selectPost( suggestion );
		// Move focus to the input field when a link suggestion is clicked.
		this.inputRef.current.focus();
	}

	render() {
		const { autoFocus = true, value = '', instanceId, className, id = 'post-selector', placeholder, labelText } = this.props;
		const { showSuggestions, suggestions, selectedSuggestion, loading } = this.state;

		const suggestionsListboxId = `block-editor-post-input-suggestions-${ instanceId }`;
		const suggestionOptionIdPrefix = `block-editor-post-input-suggestion-${ instanceId }`;

		/* eslint-disable jsx-a11y/no-autofocus */
		return (
			<div className={ classnames( 'editor-post-input block-editor-post-input', className ) }>
				{ labelText && (
					<label htmlFor={ id } >
						{ labelText }
					</label>
				) }
				<input
					id={ id }
					type="text"
					aria-label={ __( 'Post search', 'amp' ) }
					required
					value={ value }
					onChange={ this.onChange }
					onInput={ stopEventPropagation }
					onClick={ stopEventPropagation }
					placeholder={ placeholder || __( 'Type to search', 'amp' ) }
					onKeyDown={ this.onKeyDown }
					role="combobox"
					aria-expanded={ showSuggestions }
					aria-autocomplete="list"
					aria-owns={ suggestionsListboxId }
					aria-activedescendant={ selectedSuggestion !== null ? `${ suggestionOptionIdPrefix }-${ selectedSuggestion }` : undefined }
					ref={ this.inputRef }
					autoFocus={ autoFocus }
				/>

				{ ( loading ) && <Spinner /> }

				{ showSuggestions && Boolean( suggestions.length ) &&
				<Popover
					position="bottom"
					noArrow
					focusOnMount={ false }
				>
					<div
						className={ classnames(
							'editor-post-input__suggestions',
							'block-editor-post-input__suggestions',
							`${ className }__suggestions`
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
								className={ classnames( 'editor-post-input__suggestion block-editor-post-input__suggestion', {
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
		);
		/* eslint-enable jsx-a11y/no-autofocus */
	}
}

PostSelector.propTypes = {
	autocompleteRef: PropTypes.object,
	autoFocus: PropTypes.bool,
	className: PropTypes.string,
	debouncedSpeak: PropTypes.func,
	id: PropTypes.string,
	instanceId: PropTypes.number.isRequired,
	labelText: PropTypes.string,
	setTimeout: PropTypes.func,
	searchablePostTypes: PropTypes.array,
	onChange: PropTypes.func.isRequired,
	onSelect: PropTypes.func.isRequired,
	placeholder: PropTypes.string,
	speak: PropTypes.func,
	value: PropTypes.string.isRequired,
};

export default compose(
	withSafeTimeout,
	withSpokenMessages,
	withInstanceId,
)( PostSelector );
