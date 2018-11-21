
const { __ } = wp.i18n;
const {
	getBlockType
} = wp.blocks;
const { Component } = wp.element;
const { Button } = wp.components;
const {
	dispatch,
	select
} = wp.data;
const {
	getBlock,
	isBlockSelected,
	hasSelectedInnerBlock,
	getSelectedBlock
} = select( 'core/editor' );
const {
	selectBlock
} = dispatch( 'core/editor' );

import LayerInserter from './layer-inserter';

class BlockSelector extends Component {
	render() {
		if ( ! this.props.rootClientId ) {
			return null;
		}

		const rootBlock = getBlock( this.props.rootClientId );

		if ( ! rootBlock.innerBlocks.length ) {
			return null;
		}

		let links = [];
		let hasCtaLayer = false;

		window.lodash.forEachRight( rootBlock.innerBlocks, function( block, index ) {
			let template = 'vertical';
			if ( 'amp/amp-story-grid-layer-background-image' === block.name || 'amp/amp-story-grid-layer-background-video' === block.name ) {
				template = 'fill';
			} else if ( 'amp/amp-story-grid-layer-thirds' === block.name ) {
				template = 'thirds';
			} else if ( 'amp/amp-story-grid-layer-horizontal' === block.name ) {
				template = 'horizontal';
			}
			let className = 'component-editor__selector template-' + template;
			if ( isBlockSelected( block.clientId ) || hasSelectedInnerBlock( block.clientId ) ) {
				className += ' is-selected';

				// Do not blur the CTA layer so the user can edit the elements within it.
				if ( 'amp/amp-story-cta-layer' === block.name ) {
					const ampStoryDiv = document.getElementById( 'block-' + block.clientId );
					if ( ampStoryDiv ) {
						ampStoryDiv.classList.add( 'is-selected' );
					}
				}
			}

			let blockType = getBlockType( block.name );

			if ( 'amp/amp-story-cta-layer' === block.name ) {
				hasCtaLayer = true;
			}

			links.push(
				<li className={ className } key={ 'selector-' + index }>
					<Button id={ block.clientId } onClick={ ( e ) => {
						e.stopPropagation();
						selectBlock( block.clientId );
					}}>
						{ blockType.title.replace( 'Layer', '' ).trim() }
					</Button>
				</li>
			);
		} );

		let className = 'component-editor__selector page-selector';
		if ( isBlockSelected( this.props.rootClientId ) ) {
			className += ' is-selected';
		}

		const inserterProps = {
			rootClientId: this.props.rootClientId,
			hasCtaLayer: hasCtaLayer
		};

		links.push(
			<li className={ className } key='page-selector'>
				<Button onClick={ ( e ) => {
					e.stopPropagation();
					if ( getSelectedBlock.clientId !== this.props.rootClientId ) {
						selectBlock( this.props.rootClientId );
					}
				}}>
					{ __( 'Page', 'amp' ) }
				</Button>
			</li>
		);

		// @todo Creating a custom inserter since the default inserter doesn't allow taking the root client ID dynamically. Change if that becomes available.
		return (
			<ul className="editor-selectors">
				<LayerInserter { ...inserterProps }/>
				{ links }
			</ul>
		);
	}
}

export default BlockSelector;
