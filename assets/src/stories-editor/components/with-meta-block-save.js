/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import { getClassNameFromBlockAttributes, getStylesFromBlockAttributes } from '../helpers';

export default ( { tagName } ) => {
	const MetaBlockSave = ( { attributes } ) => {
		const { ampFitText } = attributes;

		const className = getClassNameFromBlockAttributes( attributes );
		const styles = getStylesFromBlockAttributes( attributes );

		if ( ! ampFitText ) {
			return (
				<RichText.Content
					tagName={ tagName }
					style={ styles }
					className={ className }
					value="{content}" // Placeholder to be replaced server-side.
				/>
			);
		}

		const ContentTag = tagName;

		return (
			<ContentTag
				style={ styles }
				className={ className }>
				<amp-fit-text layout="flex-item" className="amp-text-content">{ '{content}' }</amp-fit-text>
			</ContentTag>
		);
	};

	MetaBlockSave.propTypes = {
		attributes: PropTypes.shape( {
			ampFitText: PropTypes.bool,
		} ),
	};

	return MetaBlockSave;
};
