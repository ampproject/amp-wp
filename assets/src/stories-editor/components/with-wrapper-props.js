/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { ALLOWED_BLOCKS, ALLOWED_CHILD_BLOCKS, TEXT_BLOCK_BORDER } from '../constants';
import { withAttributes, withBlockName, withHasSelectedInnerBlock } from './';
import { getPercentageFromPixels } from '../helpers';

const wrapperWithSelect = compose(
	withAttributes,
	withBlockName,
	withHasSelectedInnerBlock,
);

/**
 * Adds wrapper props to the blocks.
 *
 * @param {Object} BlockListBlock BlockListBlock element.
 * @return {Function} Enhanced component.
 */
const withWrapperProps = ( BlockListBlock ) => {
	return wrapperWithSelect( ( props ) => {
		const { blockName, hasSelectedInnerBlock, attributes } = props;

		// If it's not an allowed block then lets return original;
		if ( -1 === ALLOWED_BLOCKS.indexOf( blockName ) ) {
			return <BlockListBlock { ...props } />;
		}

		let wrapperProps;

		// If we have an inner block selected let's add 'data-amp-selected=parent' to the wrapper.
		if (
			hasSelectedInnerBlock &&
			(
				'amp/amp-story-page' === blockName
			)
		) {
			wrapperProps = {
				...props.wrapperProps,
				'data-amp-selected': 'parent',
			};

			return <BlockListBlock { ...props } wrapperProps={ wrapperProps } />;
		}

		// If we have image caption or font-family set, add these to wrapper properties.
		wrapperProps = {
			...props.wrapperProps,
			'data-amp-image-caption': ( 'core/image' === blockName && ! attributes.ampShowImageCaption ) ? 'noCaption' : undefined,
			'data-font-family': attributes.ampFontFamily || undefined,
		};

		if ( ALLOWED_CHILD_BLOCKS.includes( blockName ) ) {
			let style = {};
			if ( 'amp/amp-story-text' === blockName ) {
				const textBlockBorderInPercentageTop = getPercentageFromPixels( 'y', TEXT_BLOCK_BORDER );
				const textBlockBorderInPercentageLeft = getPercentageFromPixels( 'x', TEXT_BLOCK_BORDER );
				style = {
					top: `${ parseFloat( attributes.positionTop ) - textBlockBorderInPercentageTop }%`,
					left: `${ parseFloat( attributes.positionLeft ) - textBlockBorderInPercentageLeft }%`,
				};
			} else {
				style = {
					top: `${ attributes.positionTop }%`,
					left: `${ attributes.positionLeft }%`,
				};
			}
			style.transform = `scale(var(--preview-scale)) translateX(var(--preview-translateX)) translateY(var(--preview-translateY)) rotate(${ attributes.rotationAngle || 0 }deg)`;

			if ( 'amp/amp-story-cta' === blockName ) {
				style.transform = `scale(var(--preview-scale))`;
			}

			if ( props.wrapperProps && props.wrapperProps.style ) {
				style = {
					...style,
					...props.wrapperProps.style,
				};
			}
			wrapperProps = {
				...wrapperProps,
				style,
			};
		}

		return <BlockListBlock { ...props } wrapperProps={ wrapperProps } />;
	} );
};

export default withWrapperProps;
