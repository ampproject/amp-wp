
const { __, sprintf } = wp.i18n;
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
			let className = 'component-editor__selector';
			if ( isBlockSelected( block.clientId ) || hasSelectedInnerBlock( block.clientId ) ) {
				className += ' is-selected';
			}

			let title = sprintf( __( 'Layer %d ', 'amp' ), index + 1 );
			if ( 'amp/amp-story-cta-layer' === block.name ) {
				title = __( 'CTA Layer', 'amp' );
				hasCtaLayer = true;
			}
			links.push(
				<li className={ className } key={ 'selector-' + index }>
					<Button id={ block.clientId } onClick={ ( e ) => {
						e.stopPropagation();

						// @todo This selects the first inner child instead for some reason. Note that this also creates a new paragraph as the first child is it doesn't exist.
						selectBlock( block.clientId );

						// @todo This is a temporary workaround for selecting the correct block. Remove when possible.
						let timeout = 50;
						setTimeout( function() {
							selectBlock( block.clientId );
						}, timeout );
					}}>
						{ title }
					</Button>
				</li>
			);
		} );

		let className = 'component-editor__selector';
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
