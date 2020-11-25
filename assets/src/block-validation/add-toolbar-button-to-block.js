/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useSelect, withSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from './store';
import { AMPToolbarButton } from './amp-toolbar-button';

/**
 * Filters blocks edit function of all blocks.
 *
 * @param {Function} BlockEdit function.
 *
 * @return {Function} Edit function.
 */
export function addToolbarButtonToBlock( BlockEdit ) {
	// Filter a class component.
	if ( BlockEdit.prototype.render ) {
		return withSelect(
			( select, ownProps ) => ( {
				count: select( BLOCK_VALIDATION_STORE_KEY ).getBlockValidationErrors( ownProps.clientId )?.length || 0,
			} ) )(
			class BlockEditWithAmpToolBarButtonClass extends BlockEdit {
				render() {
					return (
						<>
							{ this.props.count && (
								<AMPToolbarButton count={ this.props.count } />
							) }
							{ super.render() }
						</>
					);
				}
			} );
	}

	// Filter a function component.
	const BlockEditWithAmpToolBarButtonFunction = ( props ) => {
		const { clientId } = props;

		const count = useSelect(
			( select ) => select( BLOCK_VALIDATION_STORE_KEY ).getBlockValidationErrors( clientId )?.length || 0,
			[ clientId ],
		);

		return (
			<>
				{ count &&
					<AMPToolbarButton count={ count } />
				}
				<BlockEdit { ...props } />

			</>
		);
	};
	BlockEditWithAmpToolBarButtonFunction.propTypes = {
		clientId: PropTypes.string,
	};

	return BlockEditWithAmpToolBarButtonFunction;
}
