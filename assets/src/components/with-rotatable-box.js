/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS } from '../constants';
import { RotatableBox } from './';

/**
 * Higher-order component that adds animation controls to a block.
 *
 * @return {Function} Higher-order component.
 */
export default createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => {
			const {
				attributes,
				name,
				setAttributes,
				isSelected,
				toggleSelection,
			} = props;

			const { rotationAngle } = attributes;

			if ( ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
				return <BlockEdit { ...props } />;
			}

			return (
				<RotatableBox
					className={ classnames(
						'amp-story-text__rotate-container',
						{ 'is-selected': isSelected }
					) }
					angle={ rotationAngle }
					onRotateStop={ ( event, element, angle ) => {
						setAttributes( {
							rotationAngle: angle,
						} );
						toggleSelection( true );
					} }
					onRotateStart={ () => {
						toggleSelection( false );
					} }>
					<BlockEdit { ...props } />
				</RotatableBox>
			);
		};
	},
	'withRotatableBox'
);
