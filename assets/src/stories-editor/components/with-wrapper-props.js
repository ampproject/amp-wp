/**
 * WordPress dependencies
 */
import { compose } from '@wordpress/compose';

/**
 * Internal dependencies
 */
import { ALLOWED_BLOCKS, ALLOWED_CHILD_BLOCKS } from '../constants';
import { withAttributes, withBlockName, withHasSelectedInnerBlock } from './';

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
		const { clientId, blockName, hasSelectedInnerBlock, attributes } = props;

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

		const noCaption = ( 'core/image' === blockName && ! attributes.ampShowImageCaption ) ||
			( 'core/video' === blockName && ! attributes.ampShowCaption );

		// If we have image caption or font-family set, add these to wrapper properties.
		wrapperProps = {
			...props.wrapperProps,
			'data-amp-caption': noCaption ? 'noCaption' : undefined,
			'data-font-family': attributes.ampFontFamily || undefined,
		};

		if ( ALLOWED_CHILD_BLOCKS.includes( blockName ) ) {
			const innerStyle = {
				transform: `scale(var(--preview-scale)) translateX(var(--preview-translateX)) translateY(var(--preview-translateY)) rotate(${ attributes.rotationAngle || 0 }deg)`,
			};

			if ( 'amp/amp-story-cta' === blockName ) {
				innerStyle.transform = `scale(var(--preview-scale))`;
			}

			wrapperProps = {
				...wrapperProps,
				style: {
					...wrapperProps.style,
					...innerStyle,
				},
			};

			const outerStyle = {
				top: `${ attributes.positionTop }%`,
				left: `${ attributes.positionLeft }%`,
			};

			return (
				<div
					className="amp-page-child-block"
					data-block={ clientId }
					data-type={ blockName }
					style={ outerStyle }
				>
					<BlockListBlock { ...props } wrapperProps={ wrapperProps } enableAnimation={ false } />
				</div>
			);
		}

		return <BlockListBlock { ...props } wrapperProps={ wrapperProps } enableAnimation={ false } />;
	} );
};

export default withWrapperProps;
