/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { withFallbackStyles, RangeControl } from '@wordpress/components';
import { ContrastChecker, PanelColorSettings } from '@wordpress/block-editor';
import { compose } from '@wordpress/compose';

const { getComputedStyle } = window;

const applyFallbackStyles = withFallbackStyles( ( node, ownProps ) => {
	const { textColor, backgroundColor } = ownProps;
	const editableNode = node.querySelector( '[contenteditable="true"]' );
	const computedStyles = editableNode ? getComputedStyle( editableNode ) : null;

	return {
		fallbackBackgroundColor: backgroundColor || ! computedStyles ? undefined : computedStyles.backgroundColor,
		fallbackTextColor: textColor || ! computedStyles ? undefined : computedStyles.color,
	};
} );

const ColorSettings = ( { backgroundColor, setBackgroundColor, textColor, setTextColor, fallbackTextColor, fallbackBackgroundColor, fontSize, opacity, setOpacity } ) => {
	return (
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
				onChange={ setOpacity }
				min={ 5 }
				max={ 100 }
				step={ 5 }
			/>
		</PanelColorSettings>
	);
};

export default compose(
	applyFallbackStyles
)( ColorSettings );
