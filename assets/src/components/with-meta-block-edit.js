/**
 * External dependencies
 */
import classnames from 'classnames';

/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';
import {
	AlignmentToolbar,
	BlockControls,
	InspectorControls,
	withColors,
	withFontSizes,
} from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dateI18n, __experimentalGetSettings } from '@wordpress/date';

/**
 * Internal dependencies
 */
import { ResizableBox, ColorSettings, TextSettings } from './';
import { maybeEnqueueFontStyle } from '../helpers';

// @todo: Use minimal <RichText> when props.isEditable is true.
// @todo: Allow individual blocks to add custom controls.
const MetaBlockEdit = ( props ) => {
	const {
		blockContent,
		placeholder,
		attributes,
		setAttributes,
		className,
		fontSize,
		isSelected,
		setFontSize,
		backgroundColor,
		textColor,
		setBackgroundColor,
		setTextColor,
		toggleSelection,
		tagName,
	} = props;

	const {
		align,
		ampFontFamily,
		customFontSize,
		height,
		width,
		opacity,
	} = attributes;

	const minTextHeight = 20;
	const minTextWidth = 30;
	const userFontSize = fontSize.size ? fontSize.size + 'px' : '2rem';

	const ContentTag = tagName;

	return (
		<Fragment>
			<BlockControls>
				<AlignmentToolbar
					value={ align }
					onChange={ ( value ) => setAttributes( { align: value } ) }
				/>
			</BlockControls>
			<InspectorControls>
				<TextSettings
					fontFamily={ ampFontFamily }
					setFontFamily={ ( value ) => {
						maybeEnqueueFontStyle( value );
						setAttributes( { ampFontFamily: value } );
					} }
					fontSize={ fontSize }
					setFontSize={ setFontSize }
					customFontSize={ customFontSize }
				/>
				<ColorSettings
					backgroundColor={ backgroundColor }
					setBackgroundColor={ setBackgroundColor }
					textColor={ textColor }
					setTextColor={ setTextColor }
					fontSize={ fontSize }
					opacity={ opacity }
					setOpacity={ ( value ) => setAttributes( { opacity: value } ) }
				/>
			</InspectorControls>
			<ResizableBox
				isSelected={ isSelected }
				width={ width }
				height={ height }
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
				<ContentTag
					style={ {
						backgroundColor: backgroundColor.color,
						color: textColor.color,
						fontSize: userFontSize,
						textAlign: align,
					} }
					className={ classnames( className, {
						'has-text-color': textColor.color,
						'has-background': backgroundColor.color,
						[ backgroundColor.class ]: backgroundColor.class,
						[ textColor.class ]: textColor.class,
						[ fontSize.class ]: fontSize.class,
						'is-empty': ! blockContent,
					} ) }
				>
					{ blockContent || placeholder }
				</ContentTag>
			</ResizableBox>
		</Fragment>
	);
};

export default ( { attribute, placeholder, tagName, isEditable } ) => {
	return compose(
		withSelect( ( select ) => {
			const { getEditedPostAttribute } = select( 'core/editor' );
			const { getAuthors } = select( 'core' );

			const attributeValue = getEditedPostAttribute( attribute );

			let blockContent;

			// Todo: Maybe pass callbacks as props instead.
			switch ( attribute ) {
				case 'date':
					// Disable reason: false positive because of the two leading underscores.
					const dateFormat = __experimentalGetSettings().formats.date; // eslint-disable-line no-restricted-syntax
					blockContent = dateI18n( dateFormat, attributeValue );

					break;
				case 'author':
					const author = getAuthors().find( ( { id } ) => id === attributeValue );

					blockContent = author ? author.name : __( 'Anonymous', 'amp' );

					break;
				default:
					blockContent = attributeValue;
			}

			return {
				blockContent,
				placeholder,
			};
		} ),
		// @todo: Implement isEditable handling to make this usable.
		withDispatch( ( dispatch ) => {
			const { editPost } = dispatch( 'core/editor' );

			return {
				onChange: ( value ) => editPost( { [ attribute ]: value } ),
			};
		} ),
		withColors( 'backgroundColor', { textColor: 'color' } ),
		withFontSizes( 'fontSize' ),
	)( ( props ) => {
		return (
			<MetaBlockEdit
				tagName={ tagName }
				isEditable={ isEditable }
				{ ...props }
			/>
		);
	} );
};
