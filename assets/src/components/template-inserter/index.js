/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n';
import { Dropdown, IconButton, Spinner } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import { parse, createBlock } from '@wordpress/blocks';
const { apiFetch } = wp;

const blocksRestBase = 'blocks';

/**
 * Internal dependencies
 */
import BlockPreview from './block-preview';
import pageIcon from './icon';

class TemplateInserter extends Component {
	constructor() {
		super( ...arguments );

		this.onToggle = this.onToggle.bind( this );

		this.state = {
			reusableBlocks: null,
		};
	}

	componentDidMount() {
		// This is used for making sure that once the response finishes the component actually still exists.
		this.isComponentMounted = true;
		this.getReusableBlocks();
	}

	componentWillUnmount() {
		this.isComponentMounted = false;
	}

	onToggle( isOpen ) {
		const { onToggle } = this.props;

		// Surface toggle callback to parent component
		if ( onToggle ) {
			onToggle( isOpen );
		}
	}

	getReusableBlocks() {
		if ( ! this.isComponentMounted ) {
			return;
		}

		if ( null !== this.state.reusableBlocks ) {
			this.setState( { reusableBlocks: null } );
		}

		// @todo We only need reusable blocks that can be used by AMP Stories.
		const blocksRequest = this.lastRequest = apiFetch( {
			path: `/wp/v2/${ blocksRestBase }?search=amp-story-page`,
		} )
			.then( ( response ) => {
				// Check if it's the result of the last request.
				if ( this.isComponentMounted && blocksRequest === this.lastRequest && response ) {
					this.setState( { reusableBlocks: response } );
				}
			} )
			.catch( ( error ) => {
				if ( this.isComponentMounted && blocksRequest === this.lastRequest ) {
					this.setState( { reusableBlocks: {
						error: true,
						message: error.message,
					} } );
				}
			} );
	}

	render() {
		const { insertBlocks, insertBlock } = dispatch( 'core/block-editor' );
		return (
			<Dropdown
				className="editor-inserter block-editor-inserter"
				contentClassName="amp-stories__template-inserter__popover is-bottom editor-inserter__popover block-editor-inserter__popover"
				onToggle={ this.onToggle }
				expandOnMobile
				renderToggle={ ( { onToggle, isOpen } ) => (
					<IconButton
						icon="insert"
						label={ __( 'Insert Template', 'amp' ) }
						onClick={ onToggle }
						className="editor-inserter__amp-inserter"
						aria-haspopup="true"
						aria-expanded={ isOpen }
					/>
				) }
				renderContent={ ( { onClose } ) => {
					const onSelect = ( name, content ) => {
						if ( 'core/block' === name ) {
							const blocks = parse( content );
							insertBlocks( blocks );
						} else {
							const block = createBlock( name );
							insertBlock( block );
						}
						onClose();
					};

					const reusableBlocks = this.state.reusableBlocks;
					if ( ! reusableBlocks ) {
						return (
							<Spinner />
						);
					}

					if ( reusableBlocks.error ) {
						const errorMessage = sprintf( __( 'Loading templates failed: %s', 'amp' ), reusableBlocks.error.message );
						return (
							<div>
								{ errorMessage }
							</div>
						);
					}

					return (
						<div key="template-list" className="amp-stories__editor-inserter__menu">
							<div
								className="amp-stories__editor-inserter__results"
								tabIndex="0"
								role="region"
								aria-label={ __( 'Available templates', 'amp' ) }
							>
								<div role="list" className="editor-block-types-list block-editor-block-types-list">
									<div className="editor-block-preview block-editor-block-preview">
										<IconButton
											icon={ pageIcon }
											label={ __( 'Blank Page', 'amp' ) }
											onClick={ () => {
												onSelect( 'amp/amp-story-page' );
											} }
											className="amp-stories__blank-page-inserter editor-block-preview__content block-editor-block-preview__content editor-styles-wrapper"
										/>
									</div>
									{ reusableBlocks && reusableBlocks.map( ( item ) =>
										<BlockPreview
											key="template-preview"
											name="core/block"
											attributes={ { ref: item.id } }
											onClick={ () => {
												onSelect( 'core/block', item.content.raw );
											} }
										/>
									) }
								</div>
							</div>
						</div>
					);
				} }
			/>
		);
	}
}

export default TemplateInserter;
