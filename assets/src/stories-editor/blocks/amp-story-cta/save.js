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
import { getClassNameFromBlockAttributes, getStylesFromBlockAttributes } from '../../helpers';

const CallToActionEdit = ( { attributes } ) => {
	const {
		url,
		text,
	} = attributes;

	const className = getClassNameFromBlockAttributes( { ...attributes, className: 'amp-block-story-cta__link' } );
	const styles = getStylesFromBlockAttributes( attributes );

	return (
		<amp-story-cta-layer>
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

CallToActionEdit.propTypes = {
	attributes: PropTypes.shape( {
		url: PropTypes.string,
		text: PropTypes.string,
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
		customTextColor: PropTypes.string,
		fontSize: PropTypes.shape( {
			name: PropTypes.string,
			shortName: PropTypes.string,
			size: PropTypes.number,
			slug: PropTypes.string,
		} ).isRequired,
		customFontSize: PropTypes.number,
	} ).isRequired,
};

export default CallToActionEdit;
