/**
 * External dependencies
 */
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { ListItems } from '..';

describe( 'ListItems', () => {
	it( 'matches snapshots', () => {
		// Normal listing.
		let wrapper = create(
			<ListItems
				items={
					[
						{ value: 'Item 1' },
						{ value: 'Item 2' },
					]
				}
			/>,
		);

		expect( wrapper.toJSON() ).toMatchSnapshot();

		// Listing with keys.
		wrapper = create(
			<ListItems
				items={
					[
						{ label: 'Label 1', value: 'Item 1' },
						{ label: 'Label 2', value: 'Item 2' },
					]
				}
			/>,
		);

		expect( wrapper.toJSON() ).toMatchSnapshot();

		// Listing with hading.
		wrapper = create(
			<ListItems
				heading="List heading"
				items={
					[
						{ label: 'Label 1', value: 'Item 1' },
						{ label: 'Label 2', value: 'Item 2' },
					]
				}
			/>,
		);

		expect( wrapper.toJSON() ).toMatchSnapshot();

		// Listing with url.
		wrapper = create(
			<ListItems
				heading="URL listing"
				items={
					[
						{ label: 'Label 1', url: 'https://example.com/' },
						{ label: 'Label 2', url: 'https://example.com/sample-page/' },
					]
				}
			/>,
		);

		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
