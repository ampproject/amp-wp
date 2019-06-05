/**
 * Internal dependencies
 */
import { replaceFullSizeImage } from '../../helpers';

/**
 * A component that extends block edit components, replacing the 'full' image size with a custom max size.
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
		}

		/**
		 * When the component updates, if it's for the Image block, replace the full size image.
		 */
		componentDidUpdate() {
			const { name } = this.props;
			if ( 'core/image' === name ) {
				replaceFullSizeImage();
			}

			if ( super.componentDidUpdate ) {
				super.componentDidUpdate();
			}
		}
	};
};
