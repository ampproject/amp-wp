/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import {
	ContrastChecker,
	FontSizePicker,
	InspectorControls,
	PanelColorSettings,
	withColors,
	withFontSizes,
} from '@wordpress/block-editor';
import { getBlockType } from '@wordpress/blocks';
import { withDispatch, withSelect } from '@wordpress/data';
import { Fragment } from '@wordpress/element';
import { compose, createHigherOrderComponent } from '@wordpress/compose';
import { PanelBody, RangeControl, SelectControl, ToggleControl, withFallbackStyles } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { StoryBlockMover, FontFamilyPicker, ResizableBox, AnimationControls, RotatableBox } from '../';
import { ALLOWED_CHILD_BLOCKS, ALLOWED_MOVABLE_BLOCKS, BLOCKS_WITH_TEXT_SETTINGS } from '../../stories-editor/constants';
import { maybeEnqueueFontStyle } from '../../stories-editor/helpers';

const { getComputedStyle } = window;

const applyFallbackStyles = withFallbackStyles( ( node, ownProps ) => {
	const { textColor, backgroundColor, fontSize, customFontSize } = ownProps;
	const editableNode = node.querySelector( '[contenteditable="true"]' );
	const computedStyles = editableNode ? getComputedStyle( editableNode ) : null;

	return {
		fallbackBackgroundColor: backgroundColor || ! computedStyles ? undefined : computedStyles.backgroundColor,
		fallbackTextColor: textColor || ! computedStyles ? undefined : computedStyles.color,
		fallbackFontSize: fontSize || customFontSize || ! computedStyles ? undefined : parseInt( computedStyles.fontSize ) || undefined,
	};
} );

const applyWithSelect = withSelect( ( select, props ) => {
	const { getSelectedBlockClientId, getBlockRootClientId, getBlock } = select( 'core/block-editor' );
	const { getAnimatedBlocks, isValidAnimationPredecessor } = select( 'amp/story' );
	const { getMedia } = select( 'core' );

	const currentBlock = getSelectedBlockClientId();
	const page = getBlockRootClientId( currentBlock );

	const animatedBlocks = getAnimatedBlocks()[ page ] || [];
	const animationOrderEntry = animatedBlocks.find( ( { id } ) => id === props.clientId );

	const isVideoBlock = 'core/video' === props.name;
	let videoFeaturedImage;

	// If we have a video set from an attachment but there is no poster, use the featured image of the video if available.
	if ( isVideoBlock && props.attributes.id && ! props.attributes.poster ) {
		const media = getMedia( props.attributes.id );
		const featuredImage = media && get( media, [ '_links', 'wp:featuredmedia', 0, 'href' ], null );
		videoFeaturedImage = featuredImage && getMedia( Number( featuredImage.split( '/' ).pop() ) );
	}

	return {
		parentBlock: getBlock( getBlockRootClientId( props.clientId ) ),
		// Use parent's clientId instead of anchor attribute.
		// The attribute will be updated via subscribers.
		animationAfter: animationOrderEntry ? animationOrderEntry.parent : undefined,
		getAnimatedBlocks() {
			return ( getAnimatedBlocks()[ page ] || [] )
				.filter( ( { id } ) => id !== currentBlock )
				.filter( ( { id } ) => {
					const block = getBlock( id );

					return block && block.attributes.ampAnimationType && isValidAnimationPredecessor( page, currentBlock, id );
				} )
				.map( ( { id } ) => {
					const block = getBlock( id );
					return {
						value: id,
						label: block.name,
						block,
						blockType: getBlockType( block.name ),
					};
				} );
		},
		videoFeaturedImage,
	};
} );

const applyWithDispatch = withDispatch( ( dispatch, { toggleSelection }, { select } ) => {
	const {
		getSelectedBlockClientId,
		getBlockRootClientId,
	} = select( 'core/block-editor' );

	const item = getSelectedBlockClientId();
	const page = getBlockRootClientId( item );

	const {
		addAnimation,
		changeAnimationType,
		changeAnimationDuration,
		changeAnimationDelay,
	} = dispatch( 'amp/story' );

	return {
		onAnimationTypeChange( type ) {
			changeAnimationType( page, item, type );
		},
		onAnimationOrderChange( predecessor ) {
			addAnimation( page, item, predecessor );
		},
		onAnimationDurationChange( value ) {
			changeAnimationDuration( page, item, value );
		},
		onAnimationDelayChange( value ) {
			changeAnimationDelay( page, item, value );
		},
		startBlockRotation: () => toggleSelection( false ),
		stopBlockRotation: () => {
			toggleSelection( true );
		},
	};
} );

const enhance = compose(
	withColors( 'backgroundColor', { textColor: 'color' } ),
	withFontSizes( 'fontSize' ),
	applyFallbackStyles,
	applyWithSelect,
	applyWithDispatch,
);

export default createHigherOrderComponent(
	( BlockEdit ) => {
		return enhance( ( props ) => {
			const {
				clientId,
				name,
				attributes,
				isSelected,
				toggleSelection,
				fontSize,
				setFontSize,
				setAttributes,
				backgroundColor,
				setBackgroundColor,
				textColor,
				setTextColor,
				fallbackBackgroundColor,
				fallbackTextColor,
				onAnimationTypeChange,
				onAnimationOrderChange,
				onAnimationDurationChange,
				onAnimationDelayChange,
				getAnimatedBlocks,
				animationAfter,
				videoFeaturedImage,
				startBlockRotation,
				stopBlockRotation,
			} = props;

			const isChildBlock = ALLOWED_CHILD_BLOCKS.includes( name );

			if ( ! isChildBlock ) {
				return <BlockEdit { ...props } />;
			}

			const isImageBlock = 'core/image' === name;
			const isVideoBlock = 'core/video' === name;
			const isTextBlock = 'amp/amp-story-text' === name;
			const needsTextSettings = BLOCKS_WITH_TEXT_SETTINGS.includes( name );
			const isMovableBlock = ALLOWED_MOVABLE_BLOCKS.includes( name );

			const {
				ampFontFamily,
				ampFitText,
				height,
				width,
				opacity,
				type: textBlockTextType,
				ampShowImageCaption,
				ampAnimationType,
				ampAnimationDuration,
				ampAnimationDelay,
				rotationAngle,
			} = attributes;

			// If we have a video set from an attachment but there is no poster, use the featured image of the video if available.
			if ( isVideoBlock && videoFeaturedImage ) {
				setAttributes( { poster: videoFeaturedImage.source_url } );
			}

			const minTextHeight = 20;
			const minTextWidth = 30;

			return (
				<Fragment>
					{ isMovableBlock && (
						<StoryBlockMover
							clientId={ props.clientId }
							blockElementId={ `block-${ props.clientId }` }
							isDraggable={ ! props.isPartOfMultiSelection }
						/>
					) }
					{ ! isMovableBlock && ( <BlockEdit { ...props } /> ) }
					{ isImageBlock && (
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
					) }
					{ isMovableBlock && ! isImageBlock && (
						<ResizableBox
							isSelected={ isSelected }
							width={ width }
							height={ height }
							angle={ rotationAngle }
							minHeight={ minTextHeight }
							minWidth={ minTextWidth }
							onResizeStop={ ( value ) => {
								setAttributes( value );
								toggleSelection( true );
							} }
							onResizeStart={ () => {
								toggleSelection( false );
							} }
						>
							<RotatableBox
								blockElementId={ `block-${ clientId }` }
								initialAngle={ rotationAngle }
								className="amp-story-editor__rotate-container"
								angle={ rotationAngle }
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
						</ResizableBox>
					) }
					{ needsTextSettings && (
						<InspectorControls>
							<PanelBody title={ __( 'Text Settings', 'amp' ) }>
								<FontFamilyPicker
									value={ ampFontFamily }
									onChange={ ( value ) => {
										maybeEnqueueFontStyle( value );
										setAttributes( { ampFontFamily: value } );
									} }
								/>
								{ ! ampFitText && (
									<FontSizePicker
										value={ fontSize.size }
										onChange={ setFontSize }
									/>
								) }
								{ isTextBlock && (
									<ToggleControl
										label={ __( 'Automatically fit text to container', 'amp' ) }
										checked={ ampFitText }
										onChange={ () => ( setAttributes( { ampFitText: ! ampFitText } ) ) }
									/>
								) }
								{ isTextBlock && (
									<SelectControl
										label={ __( 'Select text type', 'amp' ) }
										value={ textBlockTextType }
										onChange={ ( selected ) => setAttributes( { type: selected } ) }
										options={ [
											{ value: 'auto', label: __( 'Automatic', 'amp' ) },
											{ value: 'p', label: __( 'Paragraph', 'amp' ) },
											{ value: 'h1', label: __( 'Heading 1', 'amp' ) },
											{ value: 'h2', label: __( 'Heading 2', 'amp' ) },
										] }
									/>
								) }
							</PanelBody>
							<PanelColorSettings
								title={ __( 'Color Settings', 'amp' ) }
								initialOpen={ false }
								colorSettings={ [
									{
										value: backgroundColor.color,
										onChange: setBackgroundColor,
										label: __( 'Background Color', 'amp' ),
									},
									{
										value: textColor.color,
										onChange: setTextColor,
										label: __( 'Text Color', 'amp' ),
									},
								] }
							>
								<ContrastChecker
									{ ...{
										textColor: textColor.color,
										backgroundColor: backgroundColor.color,
										fallbackTextColor,
										fallbackBackgroundColor,
										fontSize: fontSize.size,
									} }
								/>
								<RangeControl
									label={ __( 'Background Opacity', 'amp' ) }
									value={ opacity }
									onChange={ ( value ) => setAttributes( { opacity: value } ) }
									min={ 5 }
									max={ 100 }
									step={ 5 }
								/>
							</PanelColorSettings>
						</InspectorControls>
					) }
					{ isMovableBlock && (
						<InspectorControls>
							<PanelBody
								title={ __( 'Animation', 'amp' ) }
							>
								<AnimationControls
									animatedBlocks={ getAnimatedBlocks }
									animationType={ ampAnimationType }
									animationDuration={ ampAnimationDuration ? parseInt( ampAnimationDuration ) : '' }
									animationDelay={ ampAnimationDelay ? parseInt( ampAnimationDelay ) : '' }
									animationAfter={ animationAfter }
									onAnimationTypeChange={ onAnimationTypeChange }
									onAnimationDurationChange={ onAnimationDurationChange }
									onAnimationDelayChange={ onAnimationDelayChange }
									onAnimationAfterChange={ onAnimationOrderChange }
								/>
							</PanelBody>
						</InspectorControls>
					) }
					{ isImageBlock && (
						<InspectorControls>
							<PanelBody
								title={ __( 'Story Settings', 'amp' ) }
							>
								<ToggleControl
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
							</PanelBody>
						</InspectorControls>
					) }
				</Fragment>
			);
		} );
	},
	'withAmpStorySettings'
);
