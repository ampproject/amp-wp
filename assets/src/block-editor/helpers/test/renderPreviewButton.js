/**
 * Internal dependencies
 */
import { renderPreviewButton } from '../';
import { POST_PREVIEW_CLASS } from '../../constants';

const ampPreviewButtonWrapperId = '#amp-wrapper-post-preview';
const mockPreviewButtonClass = 'mock-preview-class';
const MockPreviewButton = () => {
	return <button className={ mockPreviewButtonClass } />;
};

describe( 'renderPreviewButton', () => {
	it( 'should not render the preview component into the DOM if the preview button is not present', () => {
		renderPreviewButton( MockPreviewButton );
		expect( document.querySelectorAll( ampPreviewButtonWrapperId ) ).toHaveLength( 0 );
	} );

	it( 'should render the preview component into the DOM now that the preview button exists', () => {
		const previewButton = document.createElement( 'div' );
		previewButton.setAttribute( 'class', POST_PREVIEW_CLASS );
		const previewButtonSibling = document.createElement( 'div' );

		const wrapper = document.createElement( 'div' );
		document.body.appendChild( wrapper );
		wrapper.appendChild( previewButton );
		wrapper.appendChild( previewButtonSibling );

		renderPreviewButton( MockPreviewButton );
		expect( document.querySelectorAll( ampPreviewButtonWrapperId ) ).toHaveLength( 1 );
		expect( document.getElementsByClassName( mockPreviewButtonClass ) ).toHaveLength( 1 );
	} );
} );
