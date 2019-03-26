/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Dropdown, IconButton } from '@wordpress/components';
import { Component } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import { parse, createBlock } from '@wordpress/blocks';
const { apiFetch } = wp;

const blocksRestBase = 'blocks';

/**
 * Internal dependencies
 */
import BlockPreview from './block-preview';

class TemplateInserter extends Component {
	constructor() {
		super( ...arguments );

		this.onToggle = this.onToggle.bind( this );

		this.state = {
			reusableBlocks: null,
		};
	}

	componentDidMount() {
		this.getReusableBlocks();
	}

	onToggle( isOpen ) {
		const { onToggle } = this.props;

		// Surface toggle callback to parent component
		if ( onToggle ) {
			onToggle( isOpen );
		}
	}

	getReusableBlocks() {
		// @todo We only need reusable blocks that can be used by AMP Stories.
		return apiFetch( {
			path: `/wp/v2/${ blocksRestBase }`,
		} )
			.then( ( response ) => {
				if ( response ) {
					this.setState( { reusableBlocks: response } );
				}
			} )
			.catch( ( error ) => {
				debugger;
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
					const pageIcon = <svg width="86" height="96" fill="none" xmlns="http://www.w3.org/2000/svg"><g clipPath="url(#a)"><path d="M71.115 91.034H1.654V1.655h52.923l16.538 16.552v72.828z" fill="#fff" /><path d="M54.577 1.655v16.552h16.538L54.577 1.655z" fill="#A9A9A9" /><path d="M71.115 19.862H54.577a1.66 1.66 0 0 1-1.654-1.655V1.655c0-.91.744-1.655 1.654-1.655.447 0 .86.182 1.174.48L72.29 17.032a1.65 1.65 0 0 1 0 2.334 1.652 1.652 0 0 1-1.175.496zm-14.884-3.31H67.13L56.23 5.644v10.908z" fill="#686868" /><path d="M38.038 92.69H1.654A1.66 1.66 0 0 1 0 91.034V1.655C0 .745.744 0 1.654 0h52.923c.447 0 .86.182 1.174.48L72.29 17.032c.297.314.48.728.48 1.175V48c0 .91-.745 1.655-1.655 1.655S69.462 48.91 69.462 48V18.886L53.898 3.31H3.308v86.07h34.73c.91 0 1.654.744 1.654 1.655a1.66 1.66 0 0 1-1.654 1.655z" fill="#686868" /><path d="M64.5 94.345c10.96 0 19.846-8.893 19.846-19.862 0-10.97-8.885-19.862-19.846-19.862-10.96 0-19.846 8.892-19.846 19.862 0 10.97 8.885 19.862 19.846 19.862z" fill="#A9A9A9" /><path d="M64.5 96C52.625 96 43 86.367 43 74.483s9.625-21.517 21.5-21.517S86 62.599 86 74.483c-.017 11.884-9.625 21.5-21.5 21.517zm0-39.724c-10.055 0-18.192 8.143-18.192 18.207 0 10.063 8.137 18.207 18.192 18.207s18.192-8.144 18.192-18.207c-.016-10.047-8.153-18.19-18.192-18.207z" fill="#686868" /><path d="M64.5 86.069a1.66 1.66 0 0 1-1.654-1.655V64.552c0-.91.744-1.655 1.654-1.655.91 0 1.654.744 1.654 1.655v19.862a1.66 1.66 0 0 1-1.654 1.655z" fill="#fff" /><path d="M74.423 76.138H54.577a1.66 1.66 0 0 1-1.654-1.655c0-.91.744-1.655 1.654-1.655h19.846c.91 0 1.654.745 1.654 1.655a1.66 1.66 0 0 1-1.654 1.655z" fill="#fff" /></g><defs><clipPath id="a"><path fill="#fff" d="M0 0h86v96H0z" /></clipPath></defs></svg>;
					if ( ! reusableBlocks ) {
						// @todo Add spinner.
						return (
							<div>Loading</div>
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
