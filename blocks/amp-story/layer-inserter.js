const { __ } = wp.i18n;
const { IconButton } = wp.components;
const { Component } = wp.element;
const { BlockIcon } = wp.editor;
const {
	createBlock,
	getBlockType,
	getBlockMenuDefaultClassName
} = wp.blocks;

const {
	Dropdown
} = wp.components;

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

		this.onToggle = this.onToggle.bind( this );
	}

	onInsertBlock( item, rootClientId ) {
		const { name } = item;
		const insertedBlock = createBlock( name );
		const rootBlock = getBlock( rootClientId );
		const index = rootBlock.innerBlocks.length ? rootBlock.innerBlocks.length : 0;

		insertBlock( insertedBlock, index, rootClientId );
	}

	onToggle( isOpen ) {
		const { onToggle } = this.props;

		// Surface toggle callback to parent component
		if ( onToggle ) {
			onToggle( isOpen );
		}
	}

	render() {
		const {
			rootClientId,
			hasCtaLayer
		} = this.props;

		const {
			getInserterItems
		} = wp.data.select( 'core/editor' );
		let items = getInserterItems( rootClientId );

		if ( items.length === 0 ) {
			return null;
		}

		const onInsertBlock = this.onInsertBlock;

		return (
			<Dropdown
				className="editor-inserter"
				contentClassName="editor-inserter__popover editor-inserter__amp"
				onToggle={ this.onToggle }
				expandOnMobile
				renderToggle={ ( { onToggle, isOpen } ) => (
					<IconButton
						icon="insert"
						label={ __( 'Add new layer' ) }
						onClick={ onToggle }
						className="editor-inserter__amp-inserter"
						aria-haspopup="true"
						aria-expanded={ isOpen }
					>
					</IconButton>
				) }
				renderContent={ ( { onClose } ) => {
					const onSelect = ( item ) => {
						if ( ! hasCtaLayer || 'amp/amp-story-grid-layer' === item.name ) {
							onInsertBlock( item, rootClientId );
							onClose();
						}
					};

					// @todo If CTA layer is already added, don't display it here.
					items = [
						getBlockType( 'amp/amp-story-grid-layer' ),
						getBlockType( 'amp/amp-story-cta-layer' )
					];

					const listClassName = 'editor-block-types-list' + ( hasCtaLayer ? ' amp-story-has-cta-layer' : '' );

					return (
						<div className="editor-inserter__menu">
							<div
								className="editor-inserter__results"
								tabIndex="0"
								role="region"
								aria-label={ __( 'Available block types' ) }
							>
								<ul role="list" className={ listClassName }>
									{ items.map( ( item ) => {
										const itemIconStyle = item.icon ? {
											backgroundColor: item.icon.background,
											color: item.icon.foreground
										} : {};
										const itemIconStackStyle = item.icon && item.icon.shadowColor ? {
											backgroundColor: item.icon.shadowColor
										} : {};

										const className = 'editor-block-types-list__item ' + getBlockMenuDefaultClassName( item.name );

										return (
											<li className="editor-block-types-list__list-item" key={ item.name }>
												<button
													className={ className }
													onClick={ () => {
														onSelect( item );
													} }
													aria-label={ item.title } // Fix for IE11 and JAWS 2018.
												>
													<span
														className="editor-block-types-list__item-icon"
														style={ itemIconStyle }
													>
														<BlockIcon icon={ item.icon && item.icon.src } showColors />
														{ item.hasChildBlocks &&
														<span
															className="editor-block-types-list__item-icon-stack"
															style={ itemIconStackStyle }
														/>
														}
													</span>

													<span className="editor-block-types-list__item-title">
														{ item.title }
													</span>
												</button>
											</li>
										);
									} ) }
								</ul>

							</div>
						</div>
					);
				} }
			/>
		);
	}
}

export default LayerInserter;
