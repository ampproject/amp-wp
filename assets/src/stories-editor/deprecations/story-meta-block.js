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
import { migrateV120 } from './shared';

export default ( { tagName } ) => {
	const blockAttributes = {
		align: {
			type: 'string',
		},
	};

	/**
	 * Meta blocks save logic for plugin version 1.2.0
	 *
	 * @param {Object} Attributes attributes
	 * @return {Object} Save element.
	 */
	const MetaBlockSaveV120 = ( { attributes } ) => {
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

	MetaBlockSaveV120.propTypes = {
		attributes: PropTypes.shape( {
			ampFitText: PropTypes.bool,
		} ),
	};
	return [
		{
			attributes: {
				...blockAttributes,
				deprecated: {
					default: '1.2.0',
				},
			},
			save: MetaBlockSaveV120,
			migrate: migrateV120,
		},
	];
};
