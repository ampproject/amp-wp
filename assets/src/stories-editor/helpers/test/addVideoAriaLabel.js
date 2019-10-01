/**
 * Internal dependencies
 */
import addVideoAriaLabel from '../addVideoAriaLabel';

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

	it( 'ignores video content if content is not a figure object', () => {
		const result = addVideoAriaLabel(
			<div>Ignore me 3</div>,
			{ name: 'core/video' },
			{ ampAriaLabel: 'Ignored label' },
		);

		expect( result ).toMatchSnapshot();
	} );

	it( 'ignores video content if content has incorrect node order', () => {
		const result = addVideoAriaLabel(
			<figure>
				<figcaption>Caption above content</figcaption>
				<video>
					Fallback content
				</video>
			</figure>,
			{ name: 'core/video' },
			{ ampAriaLabel: 'Ignored label' },
		);

		expect( result ).toMatchSnapshot();
	} );

	it( 'adds an aria label to video content', () => {
		const result = addVideoAriaLabel(
			<figure>
				<video>
					Fallback content
				</video>
			</figure>,
			{ name: 'core/video' },
			{ ampAriaLabel: 'Aria label 1' },
		);

		expect( result ).toMatchSnapshot();
	} );

	it( 'adds an aria label to amp-video content', () => {
		const result = addVideoAriaLabel(
			<figure>
				<amp-video>
					Fallback content
				</amp-video>
			</figure>,
			{ name: 'core/video' },
			{ ampAriaLabel: 'Aria label 1' },
		);

		expect( result ).toMatchSnapshot();
	} );

	it( 'adds an aria label to video content with a caption', () => {
		const result = addVideoAriaLabel(
			<figure>
				<video>
					Fallback content
				</video>
				<figcaption>Caption below content</figcaption>
			</figure>,
			{ name: 'core/video' },
			{ ampAriaLabel: 'Aria label 2' },
		);

		expect( result ).toMatchSnapshot();
	} );
} );
