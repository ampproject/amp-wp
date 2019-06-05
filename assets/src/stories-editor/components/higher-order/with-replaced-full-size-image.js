/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { MAX_IMAGE_SIZE_SLUG } from '../../constants';

/**
 * A component that extends the BlockEdit components, replacing the 'full' image size with a custom max size.
 *
 * @param {Function} BlockEdit A block's edit component to be extended.
 * @return {Function} A function returning an extended BlockEdit.
 */
export default ( BlockEdit ) => {
	return class ReplacedFullSizeImage extends BlockEdit {
		/**
		 * The class constructor.
		 */
		constructor() {
			super( ...arguments );
			this.componentDidUpdate = this.componentDidUpdate.bind( this );
			this.ampReplaceFullSizeImage = this.ampReplaceFullSizeImage.bind( this );
		}

		/**
		 * When the component updates, if it's for the Image block, replace the full size image.
		 */
		componentDidUpdate() {
			const { name } = this.props;
			if ( 'core/image' === name ) {
				this.ampReplaceFullSizeImage();
			}

			if ( super.componentDidUpdate ) {
				super.componentDidUpdate();
			}
		}

		/**
		 * Replaces the 'full' image size with a custom size, which has a limited height.
		 *
		 * This prevents the user from selecting an image in the Image block that's too big.
		 * For example, 3200 x 4000.
		 */
		ampReplaceFullSizeImage() {
			const blockEditorStore = 'core/block-editor';
			const initialSizes = select( blockEditorStore ).getSettings( 'imageSizes' ).imageSizes;
			const sizesWithoutFullSize = initialSizes.filter( ( size ) => 'full' !== size.slug );

			// If the AMP Story slug isn't present, add it.
			if ( ! sizesWithoutFullSize.filter( ( size ) => MAX_IMAGE_SIZE_SLUG === size.slug ).length ) {
				sizesWithoutFullSize.push(
					{
						slug: MAX_IMAGE_SIZE_SLUG,
						name: __( 'AMP Story Max Size', 'amp' ),
					}
				);
				dispatch( blockEditorStore ).updateSettings( { imageSizes: sizesWithoutFullSize } );
			}
		}
	};
};
