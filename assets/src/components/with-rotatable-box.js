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

const applyWithDispatch = withDispatch( ( dispatch, { toggleSelection } ) => {
	const { clearSelectedBlock } = dispatch( 'core/block-editor' );

	return {
		startBlockRotation: () => toggleSelection( false ),
		stopBlockRotation: () => {
			toggleSelection( true );

			clearSelectedBlock();
			document.activeElement.blur();
		},
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
				clientId,
				attributes,
				name,
				setAttributes,
				isSelected,
				startBlockRotation,
				stopBlockRotation,
			} = props;

			const { rotationAngle } = attributes;

			if ( ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<RotatableBox
					blockElementId={ `block-${ clientId }` }
					initialAngle={ rotationAngle }
					className="amp-story-editor__rotate-container"
					angle={ isSelected ? 0 : rotationAngle }
					onRotateStart={ () => {
						startBlockRotation();
					} }
					onRotateStop={ ( event, angle ) => {
						setAttributes( {
							rotationAngle: angle,
						} );

						stopBlockRotation();
					} }
				>
					<BlockEdit { ...props } />
				</RotatableBox>
			);
		} );
	},
	'withRotatableBox'
);
