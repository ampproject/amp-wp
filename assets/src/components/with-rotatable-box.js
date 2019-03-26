/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { withDispatch } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS } from '../constants';
import { RotatableBox } from './';

const applyWithDispatch = withDispatch( ( dispatch, { clientId } ) => {
	const { startBlockRotation, stopBlockRotation } = dispatch( 'amp/story' );
	const { clearSelectedBlock } = dispatch( 'core/block-editor' );

	return {
		startBlockRotation: () => startBlockRotation( clientId ),
		stopBlockRotation: () => stopBlockRotation( clientId ),
		clearSelectedBlock,
	};
} );

/**
 * Higher-order component that adds animation controls to a block.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return applyWithDispatch( function( props ) {
			const {
				attributes,
				name,
				setAttributes,
				toggleSelection,
				startBlockRotation,
				stopBlockRotation,
				isSelected,
				clearSelectedBlock,
			} = props;

			const { rotationAngle } = attributes;

			if ( ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<RotatableBox
					initialAngle={ rotationAngle }
					className="amp-story-editor__rotate-container"
					angle={ isSelected ? 0 : rotationAngle }
					onRotateStart={ () => {
						startBlockRotation();
						toggleSelection( false );
					} }
					onRotateStop={ ( event, element, angle ) => {
						stopBlockRotation();
						setAttributes( {
							rotationAngle: angle,
						} );
						toggleSelection( true );

						clearSelectedBlock();
						document.activeElement.blur();
					} }
				>
					<BlockEdit { ...props } />
				</RotatableBox>
			);
		} );
	},
	'withRotatableBox'
);
