const { __ } = wp.i18n;
const { IconButton } = wp.components;
const { Component } = wp.element;
const {
	createBlock
} = wp.blocks;

const {
	dispatch,
	select
} = wp.data;
const {
	getBlock
} = select( 'core/editor' );
const {
	insertBlock
} = dispatch( 'core/editor' );

class LayerInserter extends Component {
	constructor() {
		super( ...arguments );
	}

	render() {
		const {
			rootClientId
		} = this.props;

		const {
			getInserterItems
		} = wp.data.select( 'core/editor' );
		let items = getInserterItems( rootClientId );

		if ( items.length === 0 ) {
			return null;
		}

		return (
			<IconButton
				icon="insert"
				label={ __( 'Add Grid Layer' ) }
				onClick={ () => {
					// @todo This should actually probably open inserter menu with the choice for Grid and CTA Layer.
					const newBlock = createBlock( 'amp/amp-story-grid-layer' );
					const rootBlock = getBlock( rootClientId );
					const index = rootBlock.innerBlocks.length ? rootBlock.innerBlocks.length : 0;
					insertBlock( newBlock, index, rootClientId );
				} }
				className="editor-inserter__amp-inserter"
			>
			</IconButton>
		);
	}
}

export default LayerInserter;
