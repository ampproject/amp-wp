/**
 * Internal dependencies
 */

/**
 * Internal dependencies
 */
import { addVideoAriaLabel } from '../';

const getFigure = ( caption ) => (
	<figure>
		<amp-video>
			Fallback content
		</amp-video>
		{ caption && <figcaption>{ caption }</figcaption> }
	</figure>
);

describe( 'addVideoAriaLabel', () => {
	it( 'ignores arbitrary content', () => {
		const result = addVideoAriaLabel(
			<div>Ignore me</div>,
			{ name: 'anythin' },
			{},
		);

		expect( result ).toMatchSnapshot();
	} );

	it( 'ignores video content without an aria label attribute', () => {
		const result = addVideoAriaLabel(
			<div>Ignore me 2</div>,
			{ name: 'core/video' },
			{ ampAriaLabel: '' },
		);

		expect( result ).toMatchSnapshot();
	} );

	it( 'ignores video content even with an aria label attribute if content does not have the right format', () => {
		const result = addVideoAriaLabel(
			<div>Ignore me 3</div>,
			{ name: 'core/video' },
			{ ampAriaLabel: 'Ignored label' },
		);

		expect( result ).toMatchSnapshot();
	} );

	it( 'adds an aria label to video content without a caption', () => {
		const result = addVideoAriaLabel(
			getFigure(),
			{ name: 'core/video' },
			{ ampAriaLabel: 'Aria label 1' },
		);

		expect( result ).toMatchSnapshot();
	} );

	it( 'adds an aria label to video content with a caption', () => {
		const result = addVideoAriaLabel(
			getFigure( 'My video caption' ),
			{ name: 'core/video' },
			{ ampAriaLabel: 'Aria label 2' },
		);

		expect( result ).toMatchSnapshot();
	} );
} );
