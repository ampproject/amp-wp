/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import { PanelBody, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ALLOWED_BLOCKS } from '../helpers';
import { AnimationControls, withParentBlock } from './';

export default createHigherOrderComponent(
	( BlockEdit ) => {
		return withParentBlock( ( props ) => {
			const { attributes, setAttributes, name, parentBlock } = props;

			if ( -1 === ALLOWED_BLOCKS.indexOf( name ) || ! parentBlock || 'amp/amp-story-page' !== parentBlock.name ) {
				return <BlockEdit { ...props } />;
			}

			const { ampShowImageCaption } = attributes;

			return (
				<Fragment>
					<BlockEdit { ...props } />
					<InspectorControls>
						<PanelBody
							title={ __( 'AMP Story Settings', 'amp' ) }
						>
							<AnimationControls
								setAttributes={ setAttributes }
								attributes={ attributes }
							/>
							{
								( 'core/image' === name ) && (
									<ToggleControl
										key={ 'position' }
										label={ __( 'Show or hide the caption', 'amp' ) }
										checked={ ampShowImageCaption }
										onChange={
											function() {
												props.setAttributes( { ampShowImageCaption: ! attributes.ampShowImageCaption } );
												if ( ! attributes.ampShowImageCaption ) {
													props.setAttributes( { caption: '' } );
												}
											}
										}
										help={ __( 'Toggle on to show image caption. If you turn this off the current caption text will be deleted.', 'amp' ) }
									/>
								)
							}
						</PanelBody>
					</InspectorControls>
				</Fragment>
			);
		} );
	},
	'filterBlocksEdit'
);
