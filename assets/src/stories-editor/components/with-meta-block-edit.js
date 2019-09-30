/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { AlignmentToolbar, BlockControls } from '@wordpress/block-editor';
import { useEffect } from '@wordpress/element';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dateI18n, __experimentalGetSettings as getDateSettings } from '@wordpress/date';

/**
 * Internal dependencies
 */
import { getBackgroundColorWithOpacity } from '../../common/helpers';
import { maybeUpdateFontSize, maybeUpdateBlockDimensions } from '../helpers';

// @todo: Use minimal <RichText> when props.isEditable is true.
// @todo: Allow individual blocks to add custom controls.
const MetaBlockEdit = ( props ) => {
	const {
		attribute,
		attributes,
		setAttributes,
		className,
		fontSize,
		backgroundColor,
		customBackgroundColor,
		textColor,
		tagName,
	} = props;

	const {
		height,
		width,
		ampFitText,
		ampFontFamily,
		align,
		opacity,
		autoFontSize,
	} = attributes;

	const {
		content,
		placeholder,
		colors,
		isLoading,
	} = useSelect( ( select ) => {
		const { getEditedPostAttribute } = select( 'core/editor' );
		const { getAuthors } = select( 'core' );
		const { getSettings } = select( 'core/block-editor' );

		const attributeValue = getEditedPostAttribute( attribute );

		let blockContent;
		let loading = false;

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
				loading = ! author;

				break;
			default:
				blockContent = attributeValue;
		}

		return {
			content: blockContent,
			placeholder,
			colors: getSettings().colors,
			isLoading: loading,
		};
	} );

	useEffect( () => maybeUpdateFontSize( props ), [ isLoading ] );

	useEffect( () => {
		if ( ampFitText && ! isLoading ) {
			maybeUpdateFontSize( props );
		}
	}, [ ampFitText, ampFontFamily, width, height, content, isLoading ] );

	useEffect( () => {
		if ( ! ampFitText ) {
			maybeUpdateBlockDimensions( props );
		}
	}, [ ampFitText, fontSize, ampFontFamily, content ] );

	const userFontSize = fontSize && fontSize.size && fontSize.size + 'px';

	const appliedBackgroundColor = getBackgroundColorWithOpacity( colors, backgroundColor, customBackgroundColor, opacity );

	const ContentTag = tagName;

	return (
		<>
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
					fontSize: ampFitText ? autoFontSize + 'px' : userFontSize,
					textAlign: align,
				} }
				className={ classnames( className, {
					'has-text-color': textColor.color,
					'has-background': backgroundColor.color,
					[ backgroundColor.class ]: backgroundColor.class,
					[ textColor.class ]: textColor.class,
					[ fontSize.class ]: fontSize.class,
					'is-empty': ! content,
					'is-amp-fit-text': ampFitText,
				} ) }
			>
				{ content || placeholder }
			</ContentTag>
		</>
	);
};

MetaBlockEdit.propTypes = {
	attributes: PropTypes.shape( {
		ampFitText: PropTypes.bool,
		width: PropTypes.number,
		height: PropTypes.number,
		align: PropTypes.string,
		opacity: PropTypes.number,
		autoFontSize: PropTypes.number,
		ampFontFamily: PropTypes.string,
	} ).isRequired,
	setAttributes: PropTypes.func.isRequired,
	className: PropTypes.string,
	tagName: PropTypes.string,
	attribute: PropTypes.string,
	isSelected: PropTypes.bool,
	isEditable: PropTypes.bool,
	fontSize: PropTypes.shape( {
		name: PropTypes.string,
		shortName: PropTypes.string,
		size: PropTypes.number,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
	backgroundColor: PropTypes.shape( {
		color: PropTypes.string,
		name: PropTypes.string,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
	customBackgroundColor: PropTypes.string,
	textColor: PropTypes.shape( {
		color: PropTypes.string,
		name: PropTypes.string,
		slug: PropTypes.string,
		class: PropTypes.string,
	} ).isRequired,
};

export default ( { attribute, tagName, isEditable } ) => {
	return ( props ) => {
		return (
			<MetaBlockEdit
				attribute={ attribute }
				tagName={ tagName }
				isEditable={ isEditable }
				{ ...props }
			/>
		);
	};
};
