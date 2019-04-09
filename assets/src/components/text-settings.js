/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { PanelBody, ToggleControl, withFallbackStyles } from '@wordpress/components';
import { FontSizePicker } from '@wordpress/block-editor';
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { FontFamilyPicker } from './';

const { getComputedStyle } = window;

const applyFallbackStyles = withFallbackStyles( ( node, ownProps ) => {
	const { fontSize, customFontSize } = ownProps;
	const editableNode = node.querySelector( '[contenteditable="true"]' );
	const computedStyles = editableNode ? getComputedStyle( editableNode ) : null;

	return {
		fallbackFontSize: fontSize || customFontSize || ! computedStyles ? undefined : parseInt( computedStyles.fontSize ) || undefined,
	};
} );

const TextSettings = ( { children, fontSize, setFontSize, fontFamily, setFontFamily, fitText, setFitText } ) => {
	return (
		<PanelBody title={ __( 'Text Settings', 'amp' ) }>
			<FontFamilyPicker
				value={ fontFamily }
				onChange={ setFontFamily }
			/>
			{ fitText && (
				<ToggleControl
					label={ __( 'Automatically fit text to container', 'amp' ) }
					checked={ fitText }
					onChange={ () => setFitText( ! fitText ) }
				/>
			) }
			{ ! fitText && (
				<FontSizePicker
					value={ fontSize.size }
					onChange={ setFontSize }
				/>
			) }
			{ children }
		</PanelBody>
	);
};

export default compose(
	applyFallbackStyles
)( TextSettings );
