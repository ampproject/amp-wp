/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';
import { includes, get } from 'lodash';

/**
 * Internal dependencies
 */
import { migrateV120 } from '../shared';

/**
 * WordPress dependencies
 */
import {
	getColorClassName,
	RichText,
	getColorObjectByAttributeValues,
} from '@wordpress/block-editor';
import {
	select,
} from '@wordpress/data';

const blockAttributes = {
	value: {
		type: 'string',
		source: 'html',
		selector: 'blockquote',
		multiline: 'p',
	},
	citation: {
		type: 'string',
		source: 'html',
		selector: 'cite',
		default: '',
	},
	mainColor: {
		type: 'string',
	},
	customMainColor: {
		type: 'string',
	},
	textColor: {
		type: 'string',
	},
	customTextColor: {
		type: 'string',
	},
};

const saveV120 = ( { attributes } ) => {
	const { mainColor, customMainColor, textColor, customTextColor, value, citation, className } = attributes;
	const isSolidColorStyle = includes( className, 'is-style-solid-color' );

	let figureClass, figureStyles;
	// Is solid color style
	if ( isSolidColorStyle ) {
		figureClass = getColorClassName( 'background-color', mainColor );
		if ( ! figureClass ) {
			figureStyles = {
				backgroundColor: customMainColor,
			};
		}
		// Is normal style and a custom color is being used ( we can set a style directly with its value)
	} else if ( customMainColor ) {
		figureStyles = {
			borderColor: customMainColor,
		};
		// Is normal style and a named color is being used, we need to retrieve the color value to set the style,
		// as there is no expectation that themes create classes that set border colors.
	} else if ( mainColor ) {
		const colors = get( select( 'core/block-editor' ).getSettings(), [ 'colors' ], [] );
		const colorObject = getColorObjectByAttributeValues( colors, mainColor );
		figureStyles = {
			borderColor: colorObject.color,
		};
	}

	const blockquoteTextColorClass = getColorClassName( 'color', textColor );
	const blockquoteClasses = textColor || customTextColor ? classnames( 'has-text-color', {
		[ blockquoteTextColorClass ]: blockquoteTextColorClass,
	} ) : undefined;
	const blockquoteStyle = blockquoteTextColorClass ? undefined : { color: customTextColor };
	return (
		<figure className={ figureClass } style={ figureStyles }>
			<blockquote className={ blockquoteClasses } style={ blockquoteStyle } >
				<RichText.Content value={ value } multiline />
				{ ! RichText.isEmpty( citation ) && <RichText.Content tagName="cite" value={ citation } /> }
			</blockquote>
		</figure>
	);
};

saveV120.propTypes = {
	attributes: PropTypes.shape( {
		mainColor: PropTypes.string,
		customMainColor: PropTypes.string,
		textColor: PropTypes.string,
		customTextColor: PropTypes.string,
		value: PropTypes.string,
		citation: PropTypes.string,
		className: PropTypes.string,
	} ).isRequired,
};

const deprecated = [
	{
		attributes: {
			...blockAttributes,
			deprecated: {
				default: '1.2.0',
			},
		},
		save: saveV120,
		migrate: migrateV120,
	},
];

export default deprecated;
