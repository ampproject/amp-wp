/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { InspectorControls } from '@wordpress/editor';
import { Fragment } from '@wordpress/element';
import { PanelBody, SelectControl, ToggleControl } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { ALLOWED_BLOCKS, AMP_STORY_FONTS, AMP_STORY_POSITION_OPTIONS, GRID_BLOCKS } from '../helpers';
import { AnimationControls, withParentBlock } from './';

export default createHigherOrderComponent(
	( BlockEdit ) => {
		return withParentBlock( ( props ) => {
			const { attributes, setAttributes, name, parentBlock } = props;

			if ( -1 === ALLOWED_BLOCKS.indexOf( name ) ) {
				return <BlockEdit { ...props } />;
			}

			if ( ! parentBlock || ( -1 === GRID_BLOCKS.indexOf( parentBlock.name ) && 'amp/amp-story-page' !== parentBlock.name ) ) {
				return <BlockEdit { ...props } />;
			}

			const { ampFontFamily, ampShowImageCaption } = attributes;

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
								( 'core/paragraph' === name || 'core/heading' === name ) && (
									<SelectControl
										key={ 'font-family' }
										label={ __( 'Font family', 'amp' ) }
										value={ ampFontFamily }
										options={ AMP_STORY_FONTS }
										onChange={ function( value ) {
											props.setAttributes( { ampFontFamily: value } );
										} }
									/>
								)
							}
							{
								( 'core/image' === name && ( parentBlock && 'amp/amp-story-grid-layer-background-image' !== parentBlock.name ) ) && (
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
