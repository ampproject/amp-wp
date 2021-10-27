/**
 * External dependencies
 */
const { execSync } = require( 'child_process' );
const fs = require( 'fs' );
const { getPluginsList, getThemesList } = require( 'wporg-api-client' );
const axios = require( 'axios' );

const PLUGINS_FILE = 'includes/ecosystem-data/plugins.php';
const THEMES_FILE = 'includes/ecosystem-data/themes.php';

class UpdateExtensionFiles {
	/**
	 * Construct method.
	 */
	constructor() {
		( async () => {
			this.plugins = [];
			this.themes = [];

			await this.fetchData();
			this.storeData();
		} )();
	}

	/**
	 * Fetch AMP compatible plugins and themes.
	 *
	 * @return {Promise<void>}
	 */
	async fetchData() {
		let totalPage;
		const pluginTerm = 552;
		const themeTerm = 245;
		const url = 'https://amp-wp.org/wp-json/wp/v2/ecosystem?_embed';

		const queryParams = {
			ecosystem_types: [ themeTerm, pluginTerm ],
			per_page: 100,
			page: 1,
		};

		do {
			// eslint-disable-next-line no-await-in-loop
			const response = await axios.get( url, { params: queryParams } );
			const items = response.data;
			totalPage = parseInt( response.headers[ 'x-wp-totalpages' ] );

			if ( ! Array.isArray( items ) ) {
				break;
			}

			for ( const item of items ) {
				const ecosystemTerm = item.ecosystem_types.pop();

				if ( ecosystemTerm === pluginTerm ) {
					// eslint-disable-next-line no-await-in-loop
					const plugin = await this.preparePlugin( item );
					if ( plugin ) {
						this.plugins.push( plugin );
					}
				} else if ( ecosystemTerm === themeTerm ) {
					// eslint-disable-next-line no-await-in-loop
					const theme = await this.prepareTheme( item );
					if ( theme ) {
						this.themes.push( theme );
					}
				}
			}

			queryParams.page++;
		} while ( queryParams.page <= totalPage );
	}

	/**
	 * Store plugins and theme data in JSON respective file.
	 */
	storeData() {
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

		if ( this.plugins ) {
			let output = this.convertToPhpArray( this.plugins );
			output = `<?php ${ phpcsDisableComments }\n// NOTICE: This file was auto-generated with: npm run update-ecosystem-files.\nreturn ${ output };`;
			fs.writeFileSync( PLUGINS_FILE, output );
		}

		if ( this.themes ) {
			let output = this.convertToPhpArray( this.themes );
			output = `<?php ${ phpcsDisableComments }\n// NOTICE: This file was auto-generated with: npm run update-ecosystem-files.\nreturn ${ output };`;
			fs.writeFileSync( THEMES_FILE, output );
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

	/**
	 * Prepare a object for WordPress install page from the object of amp-wp.org rest object.
	 *
	 * @param {Object} item Single item from rest API.
	 * @return {Promise<object>} Object of plugin, Compatible for WordPress plugin install page.
	 */
	async preparePlugin( item ) {
		const regex = /wordpress\.org\/plugins\/(.[^\/]+)\/?/;
		const ecosystemUrl = item?.meta?.ampps_ecosystem_url;
		const matches = regex.exec( ecosystemUrl );
		let plugin;

		const slug = null !== matches ? matches[ 1 ] : item.slug;
		plugin = await this.fetchPluginFromWporg( slug );

		// Plugin data for amp-wp.org
		if ( null === matches || null === plugin ) {
			plugin = this.preparePluginData( item );
		}

		delete plugin.description;

		return plugin;
	}

	/**
	 * Prepare a object for WordPress install page from the object of amp-wp.org rest object.
	 *
	 * @param {Object} item Single item from rest API.
	 * @return {Promise<object>} Object of theme, Compatible for WordPress plugin install page.
	 */
	async prepareTheme( item ) {
		const regex = /wordpress\.org\/themes\/(.[^\/]+)\/?/;
		const ecosystemUrl = item?.meta?.ampps_ecosystem_url;
		const matches = regex.exec( ecosystemUrl );
		let theme;

		const slug = null !== matches ? matches[ 1 ] : item.slug;
		theme = await this.fetchThemeFromWporg( slug );

		// Theme data for amp-wp.org
		if ( null === matches || null === theme ) {
			theme = this.prepareThemeData( item );
		}

		return theme;
	}

	/**
	 * Fetch theme data from WordPress.org REST API for theme.
	 *
	 * @param {string} slug Theme slug.
	 * @return {Promise<null|*>} Theme object from WP org.
	 */
	async fetchThemeFromWporg( slug ) {
		// eslint-disable-next-line no-console
		console.log( `Fetching theme "${ slug }" from WordPress.org.` );
		const filters = {
			search: slug,
			page: 1,
			per_page: 100,
		};

		const response = await this.getThemesList( filters );
		let items = response?.data?.themes;
		items = Array.isArray( items ) ? items : Object.values( items );

		for ( const item of items ) {
			if ( slug === item.slug ) {
				return {
					name: item.name,
					slug: item.slug,
					preview_url: item.preview_url,
					screenshot_url: item.screenshot_url,
					homepage: item.homepage,
					description: item.description,
					wporg: true,
				};
			}
		}

		return null;
	}

	/**
	 * Wrapper function to get theme list.
	 * On fail it will try upto five time to get data.
	 *
	 * @param {Object} filter List of filters.
	 * @return {Promise<object>} Response from wp.org API.
	 */
	async getThemesList( filter ) {
		let error = false;

		for ( let attempts = 0; attempts < 5; attempts++ ) {
			try {
				// eslint-disable-next-line no-await-in-loop
				const responseData = await getThemesList( filter );
				return responseData;
			} catch ( exception ) {
				error = exception;
			}
		}

		throw error;
	}

	/**
	 * Transform theme data fetched from amp-wp.org to compatible with theme install screen.
	 *
	 * @param {Object} item Theme object.
	 * @return {Object} Theme object compatible for theme install screen.
	 */
	prepareThemeData( item ) {
		if ( ! item._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ] ) {
			throw new Error( `Missing featured image for theme '${ item.slug }'` );
		}

		const attachment = item._embedded[ 'wp:featuredmedia' ][ 0 ];
		return {
			name: item.title.rendered,
			slug: item.slug,
			preview_url: item?.meta?.ampps_ecosystem_url,
			screenshot_url: attachment.source_url,
			homepage: item?.meta?.ampps_ecosystem_url,
			description: item.content.rendered,
			wporg: false,
		};
	}

	/**
	 * Fetch plugin data from WordPress.org REST API for theme.
	 *
	 * @param {string} slug Plugin slug.
	 * @return {Promise<null|*>} Plugin object from WP org.
	 */
	async fetchPluginFromWporg( slug ) {
		// eslint-disable-next-line no-console
		console.log( `Fetching plugin "${ slug }" from WordPress.org.` );
		const filters = {
			search: slug,
			page: 1,
			per_page: 100,
		};

		const response = await this.getPluginsList( filters );
		let items = response?.data?.plugins;
		items = Array.isArray( items ) ? items : Object.values( items );

		for ( const item of items ) {
			if ( slug === item.slug ) {
				return {
					name: item.name,
					slug: item.slug,
					homepage: item.homepage,
					short_description: item.short_description,
					description: item.description,
					icons: item.icons,
					wporg: true,
				};
			}
		}

		return null;
	}

	/**
	 * Wrapper function to get plugin list.
	 * On fail it will try upto five time to get data.
	 *
	 * @param {Object} filter List of filters.
	 * @return {Promise<object|*>} Response from wp.org API.
	 */
	async getPluginsList( filter ) {
		let error = false;

		for ( let attempts = 0; attempts < 5; attempts++ ) {
			try {
				// eslint-disable-next-line no-await-in-loop
				const responseData = await getPluginsList( filter );
				return responseData;
			} catch ( exception ) {
				error = exception;
			}
		}

		throw error;
	}

	/**
	 * Transform plugin data fetched from amp-wp.org to compatible with theme install screen.
	 *
	 * @param {Object} item Plugin object.
	 * @return {Object} Plugin object compatible for plugin install screen.
	 */
	preparePluginData( item ) {
		if ( ! item._embedded?.[ 'wp:featuredmedia' ]?.[ 0 ] ) {
			throw new Error( `Missing featured image for ${ item.slug }` );
		}

		const attachment = item._embedded[ 'wp:featuredmedia' ][ 0 ];
		return {
			name: item.title.rendered,
			slug: item.slug,
			homepage: item?.meta?.ampps_ecosystem_url,
			short_description: item.excerpt.rendered,
			description: item.content.rendered,
			icons: {
				'1x': attachment.media_details.sizes[ 'amp-wp-org-thumbnail' ].source_url,
				'2x': attachment.media_details.sizes[ 'amp-wp-org-medium' ].source_url,
				svg: '',
			},
			wporg: false,
		};
	}
}

// eslint-disable-next-line no-new
new UpdateExtensionFiles();
