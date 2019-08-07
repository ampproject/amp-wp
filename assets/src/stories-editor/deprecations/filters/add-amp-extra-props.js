/**
 * Internal dependencies
 */
import { getUniqueId } from '../../helpers';

/**
 * Export previous filter versions based on the Plugin version number.
 */
export default {
	'1.2.0': ( props, blockType, attributes ) => {
		const ampAttributes = {};

		const newProps = { ...props };

		// Always add anchor ID regardless of block support. Needed for animations.
		newProps.id = attributes.anchor || getUniqueId();

		if ( attributes.rotationAngle ) {
			let style = ! newProps.style ? {} : newProps.style;
			style = {
				...style,
				transform: `rotate(${ parseInt( attributes.rotationAngle ) }deg)`,
			};
			ampAttributes.style = style;
		}

		if ( attributes.ampFontFamily ) {
			ampAttributes[ 'data-font-family' ] = attributes.ampFontFamily;
		}

		return {
			...newProps,
			...ampAttributes,
		};
	},
};
