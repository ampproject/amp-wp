/**
 * WordPress dependencies
 */
import { withSelect } from '@wordpress/data';

/**
 * Add wrapper props to the blocks.
 *
 * @param {Object} BlockListBlock BlockListBlock element.
 * @return {Function} Handler.
 */
const withActivePageState = ( BlockListBlock ) => {
	return withSelect( ( select, { clientId } ) => {
		const { getBlockRootClientId } = select( 'core/editor' );
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
				'amp-page-active': isActivePage,
				'amp-page-inactive': ! isActivePage,
			},
		};

		return <BlockListBlock { ...newProps } />;
	} );
};

export default withActivePageState;
