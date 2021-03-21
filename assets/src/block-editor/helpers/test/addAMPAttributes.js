/**
 * Internal dependencies
 */
import { addAMPAttributes } from '..';

describe( 'addAMPAttributes', () => {
	it( 'adds attributes to core/gallery block', () => {
		expect(
			addAMPAttributes( {}, 'core/gallery' ),
		).toMatchObject( {
			attributes: {
				ampCarousel: {
					type: 'boolean',
					default: true,
				},
				ampLightbox: {
					type: 'boolean',
					default: false,
				},
			},
		} );
	} );

	it( 'adds attributes to core/image block', () => {
		expect(
			addAMPAttributes( {}, 'core/image' ),
		).toMatchObject( {
			attributes: {
				ampLightbox: {
					type: 'boolean',
					default: false,
				},
			},
		} );
	} );

	it( 'adds attributes to core embed block', () => {
		expect(
			addAMPAttributes( {}, 'core-embed/facebook' ),
		).toMatchObject( {
			attributes: {
				ampLayout: {
					type: 'string',
				},
				ampNoLoading: {
					type: 'boolean',
				},
			},
		} );
	} );
} );
