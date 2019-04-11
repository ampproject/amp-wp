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
} from '@wordpress/block-editor';
import { Fragment } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dateI18n, __experimentalGetSettings as getDateSettings } from '@wordpress/date';

/**
 * Internal dependencies
 */
import { getRgbaFromHex } from '../helpers';

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
		backgroundColor,
		textColor,
		tagName,
	} = props;

	const { align, opacity } = attributes;

	const userFontSize = fontSize.size && fontSize.size + 'px';

	const [ r, g, b, a ] = getRgbaFromHex( backgroundColor.color, opacity );

	const ContentTag = tagName;

	return (
		<Fragment>
			<BlockControls>
				<AlignmentToolbar
					value={ align }
					onChange={ ( value ) => setAttributes( { align: value } ) }
				/>
			</BlockControls>
			<ContentTag
				style={ {
					backgroundColor: ( backgroundColor.color && 100 !== opacity ) ? `rgba( ${ r }, ${ g }, ${ b }, ${ a })` : backgroundColor.color,
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
					const dateSettings = getDateSettings();
					const dateFormat = dateSettings.formats.date;
					const date = attributeValue || new Date();

					blockContent = dateI18n( dateFormat, date );

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
