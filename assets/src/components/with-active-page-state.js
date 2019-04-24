/**
 * WordPress dependencies
 */
import { withSelect, dispatch } from '@wordpress/data';

/**
 * Add wrapper props to the blocks.
 *
 * @param {Object} BlockListBlock BlockListBlock element.
 * @return {Function} Handler.
 */
const withActivePageState = ( BlockListBlock ) => {
	return withSelect( ( select, { clientId } ) => {
		const { getBlockRootClientId } = select( 'core/block-editor' );
		const { getCurrentPage } = select( 'amp/story' );

		return {
			isActivePage: getCurrentPage() === clientId,
			isTopLevelBlock: '' === getBlockRootClientId( clientId ),
		};
	} )( ( props ) => {
		const { isTopLevelBlock, isActivePage } = props;

		// If it's not an allowed block then lets return original;
		if ( ! isTopLevelBlock ) {
			return <BlockListBlock { ...props } />;
		}

		const newProps = {
			...props,
			className: {
				...props.className,
				'amp-page-active': isTopLevelBlock && isActivePage,
				'amp-page-inactive': isTopLevelBlock && ! isActivePage,
			},
			isLocked: ! isActivePage,
		};

		const { setCurrentPage } = dispatch( 'amp/story' );
		const { selectBlock } = dispatch( 'core/block-editor' );

		if ( ! isActivePage ) {
			return (
				<BlockListBlock
					{ ...newProps }
					onSelect={ () => {
						setCurrentPage( props.clientId );
						selectBlock( props.clientId );
					} }
				/>
			);
		}

		return <BlockListBlock { ...newProps } />;
	} );
};

export default withActivePageState;
