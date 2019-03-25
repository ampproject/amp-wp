/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { dispatch } from '@wordpress/data';
import { Component } from '@wordpress/element';

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
		return class extends Component {
			constructor() {
				super( ...arguments );

				this.state = {
					isRotating: false,
				};
			}

			render() {
				const {
					attributes,
					name,
					setAttributes,
					toggleSelection,
				} = this.props;

				const { rotationAngle } = attributes;
				const { clearSelectedBlock } = dispatch( 'core/editor' );

				if ( ! ALLOWED_CHILD_BLOCKS.includes( name ) ) {
					return <BlockEdit { ...this.props } />;
				}

				return (
					<RotatableBox
						className={ classnames(
							'amp-story-editor__rotate-container',
							{ 'is-rotating': this.state.isRotating }
						) }
						angle={ rotationAngle }
						onRotateStart={ () => {
							this.setState( { isRotating: true } );
							setAttributes( {
								rotationAngle: 0,
							} );
							toggleSelection( false );
						} }
						onRotateStop={ ( event, element, angle ) => {
							this.setState( { isRotating: false } );
							setAttributes( {
								rotationAngle: angle,
							} );
							toggleSelection( true );
							clearSelectedBlock();
						} }
					>
						<BlockEdit { ...this.props } />
					</RotatableBox>
				);
			}
		};
	},
	'withRotatableBox'
);
