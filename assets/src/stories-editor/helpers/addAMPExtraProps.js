/**
 * Internal dependencies
 */
import { ALLOWED_CHILD_BLOCKS } from '../constants';
import { addAMPExtraPropsDeprecations } from '../deprecations/filters';

/**
 * Add extra attributes to save to DB.
 *
 * @param {Object} props           Properties.
 * @param {Object} blockType       Block type object.
 * @param {string} blockType.name  Block type name.
 * @param {Object} attributes      Attributes.
 *
 * @return {Object} Props.
 */
const addAMPExtraProps = ( props, blockType, attributes ) => {
	const ampAttributes = {};

	if ( ! ALLOWED_CHILD_BLOCKS.includes( blockType.name ) ) {
		return props;
	}

	if ( attributes.deprecated && addAMPExtraPropsDeprecations[ attributes.deprecated ] ) {
		const deprecatedExtraProps = addAMPExtraPropsDeprecations[ attributes.deprecated ];
		if ( 'function' === typeof deprecatedExtraProps ) {
			return deprecatedExtraProps( props, blockType, attributes );
		}
	}

	if ( attributes.rotationAngle ) {
		let style = ! props.style ? {} : props.style;
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
		...props,
		...ampAttributes,
	};
};

export default addAMPExtraProps;
