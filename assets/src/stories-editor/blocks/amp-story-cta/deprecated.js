/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { omit } from 'lodash';

/**
 * WordPress dependencies
 */
import { RichText } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import {
	getClassNameFromBlockAttributes,
	getStylesFromBlockAttributes,
	getUniqueId,
} from '../../helpers';

const blockAttributes = {
	url: {
		type: 'string',
		source: 'attribute',
		selector: 'a',
		attribute: 'href',
	},
	text: {
		type: 'string',
		source: 'html',
		selector: 'a',
	},
	customTextColor: {
		type: 'string',
		default: '#ffffff',
	},
	customBackgroundColor: {
		type: 'string',
		default: '#32373c',
	},
	btnPositionTop: {
		type: 'number',
		default: 0,
	},
	btnPositionLeft: {
		type: 'number',
		default: 30,
	},
	btnWidth: {
		type: 'number',
	},
	btnHeight: {
		type: 'number',
	},
};

/**
 * Deprecated save function for plugin version 1.2.1
 *
 * @param {Object} attributes Attributes.
 * @return {*} CTA save.
 */
const CallToActionSaveV121 = ( { attributes } ) => {
	const {
		anchor,
		url,
		text,
	} = attributes;

	const className = getClassNameFromBlockAttributes( { ...attributes, className: 'amp-block-story-cta__link' } );
	const styles = getStylesFromBlockAttributes( attributes );

	return (
		<amp-story-cta-layer id={ anchor }>
			<RichText.Content
				tagName="a"
				className={ className }
				href={ url }
				style={ styles }
				value={ text }
			/>
		</amp-story-cta-layer>
	);
};

CallToActionSaveV121.propTypes = {
	attributes: PropTypes.shape( {
		anchor: PropTypes.string,
		url: PropTypes.string,
		text: PropTypes.string,
	} ).isRequired,
};

/**
 * Deprecated save function which was used last for plugin version 1.3.0
 *
 * @param {Object} attributes Attributes.
 * @return {*} CTA save.
 */
const CallToActionSaveV130 = ( { attributes } ) => {
	const {
		anchor,
		btnPositionLeft,
		btnPositionTop,
		text,
		url,
	} = attributes;

	const className = getClassNameFromBlockAttributes( { ...attributes, className: 'amp-block-story-cta__link' } );
	const styles = getStylesFromBlockAttributes( attributes );

	styles.top = btnPositionTop ? `${ btnPositionTop }%` : undefined;
	styles.left = btnPositionLeft ? `${ btnPositionLeft }%` : undefined;

	return (
		<amp-story-cta-layer id={ anchor ? anchor : getUniqueId() }>
			<RichText.Content
				tagName="a"
				className={ className }
				href={ url }
				style={ styles }
				value={ text }
			/>
		</amp-story-cta-layer>
	);
};

CallToActionSaveV130.propTypes = {
	attributes: PropTypes.shape( {
		anchor: PropTypes.string,
		url: PropTypes.string,
		text: PropTypes.string,
		btnPositionLeft: PropTypes.number,
		btnPositionTop: PropTypes.number,
	} ).isRequired,
};

const deprecated = [
	{
		attributes: {
			align: {
				type: 'string',
				default: 'center',
			},
			...blockAttributes,
		},
		supports: {
			align: true,
			alignWide: false,
		},

		save: CallToActionSaveV121,

		migrate( attributes ) {
			return {
				...omit( attributes, 'align' ),
				btnPositionTop: 0,
				btnPositionLeft: 30,
			};
		},
	},
	{
		attributes: {
			...blockAttributes,
		},

		save: CallToActionSaveV130,
	},
];

export default deprecated;
