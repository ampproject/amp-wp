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
import { Component, Fragment } from '@wordpress/element';
import { withSelect, withDispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dateI18n, __experimentalGetSettings as getDateSettings } from '@wordpress/date';

/**
 * Internal dependencies
 */
import { getBackgroundColorWithOpacity } from '../common/helpers';
import { calculateFontSize } from '../stories-editor/helpers';
import { MIN_FONT_SIZE, MAX_FONT_SIZE } from '../stories-editor/constants';

// @todo: Use minimal <RichText> when props.isEditable is true.
// @todo: Allow individual blocks to add custom controls.
class MetaBlockEdit extends Component {
	componentDidUpdate( prevProps ) {
		const { clientId, blockContent, attributes, isSelected, setAttributes } = this.props;
		const {
			height,
			width,
			autoFontSize,
			ampFitText,
		} = attributes;

		// If not selected, only proceed if height or width has changed.
		if (
			! isSelected &&
			prevProps.attributes.height === height &&
			prevProps.attributes.width === width
		) {
			return;
		}

		if ( ampFitText && blockContent.length ) {
			// Check if the font size is OK, if not, update the font size.
			const element = document.querySelector( `#block-${ clientId } .is-amp-fit-text` );

			if ( element ) {
				const fitFontSize = calculateFontSize( element, height, width, MAX_FONT_SIZE, MIN_FONT_SIZE );
				if ( autoFontSize !== fitFontSize ) {
					setAttributes( { autoFontSize: fitFontSize } );
				}
			}
		}
	}

	render() {
		const {
			blockContent,
			placeholder,
			attributes,
			setAttributes,
			className,
			fontSize,
			colors,
			backgroundColor,
			customBackgroundColor,
			textColor,
			tagName,
		} = this.props;

		const {
			align,
			opacity,
			ampFitText,
			autoFontSize,
		} = attributes;

		const userFontSize = fontSize && fontSize.size && fontSize.size + 'px';

		const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

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
						backgroundColor: appliedBackgroundColor,
						color: textColor.color,
						fontSize: ampFitText ? autoFontSize : userFontSize,
						textAlign: align,
					} }
					className={ classnames( className, {
						'has-text-color': textColor.color,
						'has-background': backgroundColor.color,
						[ backgroundColor.class ]: backgroundColor.class,
						[ textColor.class ]: textColor.class,
						[ fontSize.class ]: fontSize.class,
						'is-empty': ! blockContent,
						'is-amp-fit-text': ampFitText,
					} ) }
				>
					{ blockContent || placeholder }
				</ContentTag>
			</Fragment>
		);
	}
}

export default ( { attribute, placeholder, tagName, isEditable } ) => {
	return compose(
		withSelect( ( select ) => {
			const { getEditedPostAttribute } = select( 'core/editor' );
			const { getAuthors } = select( 'core' );
			const { getSettings } = select( 'core/block-editor' );

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

			const { colors } = getSettings();

			return {
				blockContent,
				placeholder,
				colors,
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
