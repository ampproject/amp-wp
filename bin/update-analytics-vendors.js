/**
 * External dependencies
 */
const { once } = require( 'events' );
const fs = require( 'fs' );
const readline = require( 'readline' );
const stream = require( 'stream' );
const axios = require( 'axios' );

/**
 * File path of the analytics vendors list.
 */
const ANALYTICS_VENDORS_FILE = 'data/analytics-vendors-list.json';

class UpdateAnalyticsVendors {
	/**
	 * Construct method.
	 */
	constructor() {
		this.vendors = {};
		this.content = '';
		this.init();
	}

	/**
	 * Initialize the script.
	 */
	async init() {
		await this.fetchData();
		await this.extractVendors();
		this.saveData();
	}

	/**
	 * Fetch analytics vendors.
	 *
	 * @return {Promise<void>}
	 */
	async fetchData() {
		// Remote URL for analytics vendors.
		const url =
			'https://raw.githubusercontent.com/ampproject/amphtml/main/extensions/amp-analytics/analytics-vendors-list.md';
		const response = await axios.get( url );

		// A new readable stream for response data.
		const bufferStream = new stream.PassThrough();
		bufferStream.end( response.data );

		// Create a readline interface to read the file line by line.
		const fileContent = readline.createInterface( {
			input: bufferStream,
			crlfDelay: Infinity,
		} );

		this.content = fileContent;
	}

	/**
	 * Extract vendors from recieved data.
	 *
	 * @return {Promise<void>}
	 */
	async extractVendors() {
		let commentFlag = false;
		let vendorSlug = '';
		let vendorTitle = '';

		try {
			this.content.on( 'line', ( line ) => {
				// Check if the line is in a comment.
				if ( line.trim() === '<!--' ) {
					commentFlag = true;
				}

				if ( line.trim() === '-->' ) {
					commentFlag = false;
				}

				// Check if line contains vendor title.
				if ( line.indexOf( '###' ) === 0 && ! commentFlag ) {
					vendorTitle = line.replace( '###', '' ).trim();
				}

				// Check if line contains vendor slug.
				if ( line.indexOf( 'Type attribute value:' ) === 0 && ! commentFlag ) {
					vendorSlug = line.replace( 'Type attribute value:', '' ).trim();
				}

				// Populate vendors object.
				if ( vendorSlug && vendorTitle ) {
					const vendorSlugs = vendorSlug.replace( /[^\w,\/-]/g, '' ).trim().split( ',' );

					// Loop through multiple vendor slugs with same titles and append extra information to title.
					vendorSlugs.forEach( ( slug ) => {
						if ( vendorSlugs.indexOf( slug ) === 0 ) {
							this.vendors[ 'N/A' !== slug ? slug : '' ] = vendorTitle.replace( /(<([^>]+)>)/gi, '' ).trim();
							return;
						}

						slug = slug.trim();
						/**
						 * Get extra information from vendor slug if the title is same.
						 * remove common prefixes from the vendor slug and convert to sentence case.
						 */
						const vendorInfo = slug.replace( /.*_/g, '' ) // Remove common prefixes.
							.replace( /([A-Z]+)/g, ' $1' ) // Add space before each capital letter.
							.toLowerCase();	// Convert to lowercase.

						// Strip HTML tags from title if any and append extra information.
						this.vendors[ slug ] = vendorTitle.replace( /(<([^>]+)>)/gi, '' ).concat( ' (', vendorInfo, ')' );
					} );

					vendorSlug = '';
					vendorTitle = '';
				}
			} );

			await once( this.content, 'close' );
		} catch ( error ) {
			throw error;
		}
	}

	/**
	 * Save data to JSON file.
	 */
	saveData() {
		if ( this.vendors ) {
			// Save vendors to JSON file.
			fs.writeFileSync(
				ANALYTICS_VENDORS_FILE,
				JSON.stringify( this.vendors, null, 4 ),
			);
		}
	}
}

// eslint-disable-next-line no-new
new UpdateAnalyticsVendors();
