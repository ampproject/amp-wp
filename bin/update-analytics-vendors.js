
/**
 * External dependencies
 */
const { once } = require( 'events' );
const fs = require( 'fs' );
const readline = require( 'readline' );
const stream = require( 'stream' );
const { execSync } = require( 'child_process' );
const axios = require( 'axios' );

/**
 * File path of the analytics vendors list.
 */
const ANALYTICS_VENDORS_FILE = 'includes/ecosystem-data/analytics-vendors.php';

class UpdateAnalyticsVendors {
	/**
	 * Construct method.
	 */
	constructor() {
		this.vendors = [];
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
							/**
							 * Google Tag Manager will not be supported directly as it requires extra attributes.
							 * Also, A notice will be thrown if user enters `googletagmanager` as vendor slug.
							 */
							if ( slug === 'N/A' && vendorTitle === 'Google Tag Manager' ) {
								return;
							}

							this.vendors[ slug ] = vendorTitle.replace( /(<([^>]+)>)/gi, '' ).trim();
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
		const phpcsDisables = [
			'Squiz.Commenting.FileComment.Missing',
			'WordPress.Arrays.ArrayIndentation',
			'WordPress.WhiteSpace.PrecisionAlignment',
			'WordPress.Arrays.ArrayDeclarationSpacing',
			'Generic.WhiteSpace.DisallowSpaceIndent',
			'Generic.Arrays.DisallowLongArraySyntax',
			'Squiz.Commenting.FileComment.Missing',
			'Generic.Files.EndFileNewline',
			'WordPress.Arrays.MultipleStatementAlignment',
		];

		const phpcsDisableComments = phpcsDisables.map( ( rule ) => `// phpcs:disable ${ rule }\n` ).join( '' );

		if ( this.vendors ) {
			this.vendors = Object.entries( this.vendors ).map( ( [ value, label ] ) => ( { value, label } ) );

			// Sort vendors by label.
			this.vendors = this.vendors.sort( ( a, b ) => {
				return a.label.localeCompare( b.label );
			} );

			let output = this.convertToPhpArray( this.vendors );
			// Save vendors to JSON file.
			output = `<?php ${ phpcsDisableComments }\n// NOTICE: This file was auto-generated with: npm run update-analytics-vendors.\nreturn ${ output };`;
			fs.writeFileSync( ANALYTICS_VENDORS_FILE, output );
		}
	}

	/**
	 * Convert JS object into PHP array variable.
	 *
	 * @param {Object} object An object that needs to convert into a PHP array.
	 * @return {string|null} PHP array in string.
	 */
	convertToPhpArray( object ) {
		if ( 'object' !== typeof object ) {
			return null;
		}

		const json = JSON.stringify( object );
		const command = `php -r 'var_export( json_decode( file_get_contents( "php://stdin" ), true ) );'`;
		let output = execSync( command, { input: json } );
		output = output.toString();

		return ( output && 'NULL' !== output ) ? output : 'array()';
	}
}

// eslint-disable-next-line no-new
new UpdateAnalyticsVendors();
