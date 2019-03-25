/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Dropdown, IconButton } from '@wordpress/components';
import { Component, RawHTML } from '@wordpress/element';
import { dispatch } from '@wordpress/data';
import uuid from 'uuid/v4';
const { apiFetch } = wp;

const blocksRestBase = 'blocks';

/**
 * Internal dependencies
 */
import BlockPreview from './block-preview';

const renderToggle = ( { onToggle, disabled, isOpen } ) => (
	<IconButton
		icon="insert"
		label={ __( 'Add New Page', 'amp' ) }
		labelPosition="bottom left"
		onClick={ onToggle }
		className="editor-inserter__toggle"
		aria-haspopup="true"
		aria-expanded={ isOpen }
		disabled={ disabled }
	/>
);

/**
 * Temporary static function for displaying template contents.
 *
 * @param {number} templateId Template ID.
 */
const getTemplateBlocks = ( templateId ) => {
	switch ( templateId ) {
		case 0:
			return `<!-- wp:amp/amp-story-page --><amp-story-page id=${ uuid() } style="background-color:#ffffff" class="wp-block-amp-amp-story-page"><amp-story-grid-layer template="vertical"><!-- wp:amp/amp-story-text {"tagName":"h1","autoFontSize":28,"positionTop":10} --><h1 id=${ uuid() } style="font-size:28px;width:76%;height:9%;position:absolute;top:10%;left:5%" class=""><amp-fit-text layout="fill" class="amp-text-content"></amp-fit-text></h1> <!-- /wp:amp/amp-story-text --></amp-story-grid-layer></amp-story-page> <!-- /wp:amp/amp-story-page -->`;
		case 1:
			return `<!-- wp:amp/amp-story-page {"backgroundColor":"#0693e3"} --> <amp-story-page id=${ uuid() } style="background-color:#0693e3" class="wp-block-amp-amp-story-page"><amp-story-grid-layer template="vertical"><!-- wp:amp/amp-story-text {"tagName":"h2","autoFontSize":30,"textColor":"very-light-gray","height":108,"width":323,"positionTop":24,"positionLeft":2} --> <h2 id=${ uuid() } style="font-size:30px;width:98%;height:20%;position:absolute;top:24%;left:2%" class="has-text-color has-very-light-gray-color"><amp-fit-text layout="fill" class="amp-text-content">Hello, this is a sample.</amp-fit-text></h2> <!-- /wp:amp/amp-story-text --></amp-story-grid-layer></amp-story-page> <!-- /wp:amp/amp-story-page -->`;
		default:
			return '';
	}
};

class TemplateInserter extends Component {
	constructor() {
		super( ...arguments );

		this.onToggle = this.onToggle.bind( this );
		this.renderToggle = this.renderToggle.bind( this );

		this.state = {
			reusableBlocks: null,
		};
	}

	componentDidMount() {
		debugger;
		this.getReusableBlocks();
		// Only debounce once the initial fetch occurs to ensure that the first
		// renders show data as soon as possible.
		// this.getReusableBlocks = debounce( this.getReusableBlocks(), 500 );
	}

	onToggle( isOpen ) {
		const { onToggle } = this.props;

		// Surface toggle callback to parent component
		if ( onToggle ) {
			onToggle( isOpen );
		}
	}

	/**
	 * Render callback to display Dropdown toggle element.
	 *
	 * @param {Function} options.onToggle Callback to invoke when toggle is
	 *                                    pressed.
	 * @param {boolean}  options.isOpen   Whether dropdown is currently open.
	 *
	 * @return {WPElement} Dropdown toggle element.
	 */
	renderToggle( { onToggle, isOpen } ) {
		const {
			disabled,
		} = this.props;

		return renderToggle( { onToggle, isOpen, disabled } );
	}

	getReusableBlocks() {
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
		const { insertBlocks } = dispatch('core/block-editor');
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
					>
						{ __( 'Insert Template', 'amp' ) }
					</IconButton>
				) }
				renderContent={ ( { onClose } ) => {
					const onSelect = ( templateId ) => {
						const templateHTML = getTemplateBlocks( templateId );

						const blocks = wp.blocks.parse( templateHTML );
						insertBlocks( blocks );
						onClose();
					};

					const reusableBlocks = this.state.reusableBlocks;
					if ( ! reusableBlocks ) {
						return (
							<div>DmmDmm</div>
						);
					} else {
						debugger;
						return (
							<div className="amp-stories__editor-inserter__menu editor-inserter__menu">
								<div
									className="amp-stories__editor-inserter__results"
									tabIndex="0"
									role="region"
									aria-label={ __( 'Available templates', 'amp' ) }
								>
									<ul role="list" className="editor-block-types-list block-editor-block-types-list">
										{ reusableBlocks && reusableBlocks.map( ( item ) =>
											<BlockPreview
												name={ 'core/template' }
												attributes={ { content: item.content.raw } }
												onClick={ () => {
													onSelect( item );
												} }
											/>
										) }
									</ul>
								</div>
							</div>
						);
					}
				} }
			/>
		);
	}
}

export default TemplateInserter;
