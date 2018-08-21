import forEachRight from 'lodash'; // eslint-disable-line no-unused-vars

const { Component } = wp.element;
const { getBlockType } = wp.blocks;
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

		_.forEachRight( rootBlock.innerBlocks, function( block, index ) {
			let className = 'component-editor__selector';
			if ( isBlockSelected( block.clientId ) || hasSelectedInnerBlock( block.clientId ) ) {
				className += ' is-selected';
			}
			let blockType = getBlockType( block.name );
			links.push(
				<li className={ className } key={ 'selector-' + index }>
					<a onClick={ ( e ) => {
						e.stopPropagation();
						if ( getSelectedBlock.clientId !== block.clientId ) {
							// @todo This selects the first inner child instead for some reason.
							selectBlock( block.clientId );
						}
					}}>{ blockType.title }</a>
				</li>
			);
		} );

		let className = 'component-editor__selector';
		if ( isBlockSelected( this.props.rootClientId ) ) {
			className += ' is-selected';
		}

		links.push(
			<li className={ className } key='page-selector'>
				<a onClick={ () => {
					selectBlock( this.props.rootClientId );
				}}>Page</a>
			</li>
		);

		return (
			<ul className="editor-selectors">
				{ links }
			</ul>
		);
	}
}

export default BlockSelector;
