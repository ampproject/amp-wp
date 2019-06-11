/**
 * External dependencies
 */
import { get } from 'lodash';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { createHigherOrderComponent } from '@wordpress/compose';
import { dispatch, select } from '@wordpress/data';

/**
 * Internal dependencies
 */
import { MAX_IMAGE_SIZE_SLUG } from '../../constants';

const BLOCK_EDITOR_STORE = 'core/block-editor';

export default createHigherOrderComponent( ( BlockEdit ) => {
	return class extends BlockEdit {
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
			const initialImageSizes = get( select( BLOCK_EDITOR_STORE ).getSettings( 'imageSizes' ), [ 'imageSizes' ] );
			if ( ! initialImageSizes ) {
				return;
			}

			// If the AMP Story slug isn't present, add it.
			const sizesWithoutFullSize = initialImageSizes.filter( ( size ) => 'full' !== size.slug );
			if ( ! sizesWithoutFullSize.filter( ( size ) => MAX_IMAGE_SIZE_SLUG === size.slug ).length ) {
				sizesWithoutFullSize.push(
					{
						slug: MAX_IMAGE_SIZE_SLUG,
						name: __( 'AMP Story Max Size', 'amp' ),
					}
				);
				dispatch( BLOCK_EDITOR_STORE ).updateSettings( { imageSizes: sizesWithoutFullSize } );
			}
		}
	};
} );
