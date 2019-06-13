/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';
import { RawHTML } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { getClassNameFromBlockAttributes, getStylesFromBlockAttributes } from '../../helpers';

const TextBlockSave = ( { attributes } ) => {
	const {
		content,
		ampFitText,
		tagName,
	} = attributes;

	const className = getClassNameFromBlockAttributes( attributes );
	const styles = getStylesFromBlockAttributes( attributes );

	if ( ! ampFitText ) {
		return (
			<RichText.Content
				tagName={ tagName }
				style={ styles }
				className={ className }
				value={ content }
				format="string"
			/>
		);
	}

	const ContentTag = tagName;

	styles.display = 'flex';

	// Uses RawHTML to mimic RichText.Content behavior.
	return (
		<ContentTag
			style={ styles }
			className={ className }>
			<amp-fit-text layout="flex-item" className="amp-text-content"><RawHTML>{ content }</RawHTML></amp-fit-text>
		</ContentTag>
	);
};

TextBlockSave.propTypes = {
	attributes: PropTypes.shape( {
		content: PropTypes.string,
		ampFitText: PropTypes.bool,
		tagName: PropTypes.string,
	} ).isRequired,
};

export default TextBlockSave;
