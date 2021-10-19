/**
 * External dependencies
 */
const { exec } = require( 'child_process' );
const fs = require( 'fs' );
const { getPluginsList, getThemesList } = require( 'wporg-api-client' );
const axios = require( 'axios' );

class UpdateExtensionFiles {
	/**
	 * Construct method.
	 */
	constructor() {
		( async () => {
			this.plugins = [];
			this.themes = [];

			await this.fetchData();
			await this.storeData();
		} )();
	}

	/**
	 * Fetch AMP compatible plugins and themes.
	 *
	 * @return {Promise<void>}
	 */
	async fetchData() {
		let totalPage = 1;
		const pluginTerm = 552;
		const themeTerm = 245;
		const url = 'https://amp-wp.org/wp-json/wp/v2/ecosystem';

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

			// eslint-disable-next-line guard-for-in
			for ( const index in items ) {
				const item = items[ index ];
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
	 *
	 * @return {Promise<void>}
	 */
	async storeData() {
		if ( this.plugins ) {
			let output = await this.convertToPhpArray( this.plugins );
			output = `<?php\nreturn ${ output };`;
			fs.writeFileSync( 'includes/amp-plugins.php', output );
		}

		if ( this.themes ) {
			let output = await this.convertToPhpArray( this.themes );
			output = `<?php\nreturn ${ output };`;
			fs.writeFileSync( 'includes/amp-themes.php', output );
		}
	}

	/**
	 * Execute given command in shell and return the output.
	 *
	 * @param {string} command Shell command.
	 * @return {Promise<object>} Output or error from shell command.
	 */
	executeCommand( command ) {
		return new Promise( ( done, failed ) => {
			exec( command, ( error, stdout, stderr ) => {
				if ( error ) {
					error.stdout = stdout;
					error.stderr = stderr;
					failed( error );
					return;
				}
				done( { stdout, stderr } );
			} );
		} );
	}

	/**
	 * Convert JS object into PHP array variable.
	 *
	 * @param {Object} object An object that needs to convert into a PHP array.
	 * @return {string|null} PHP array in string.
	 */
	async convertToPhpArray( object ) {
		if ( 'object' !== typeof object ) {
			return null;
		}

		const tempFilePath = '/tmp/amp.json';
		const json = JSON.stringify( object );
		const command = `php -r 'var_export( json_decode( file_get_contents( "${ tempFilePath }" ), true ) );'`;

		fs.writeFileSync( tempFilePath, json );
		const output = await this.executeCommand( command );

		fs.unlinkSync( tempFilePath );

		return ( output.stdout ) ? output.stdout : 'array()';
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
		let plugin = null;

		// WordPress org plugin.
		if ( null !== matches ) {
			plugin = await this.fetchPluginFromWporg( matches[ 1 ] );
		} else {
			plugin = await this.fetchPluginFromWporg( item.slug );
		}

		// Plugin data for amp-wp.org
		if ( null === matches || null === plugin ) {
			plugin = await this.preparePluginData( item );
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
		let theme = null;

		// WordPress org plugin.
		if ( null !== matches ) {
			theme = await this.fetchThemeFromWporg( matches[ 1 ] );
		} else {
			theme = await this.fetchThemeFromWporg( item.slug );
		}

		// Theme data for amp-wp.org
		if ( null === matches || null === theme ) {
			theme = await this.prepareThemeData( item );
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
		console.log( `Fetching theme ${ slug } from WordPress.org.` );
		const filters = {
			search: slug,
			page: 1,
			per_page: 100,
		};

		const response = await this.getThemesList( filters );
		const items = response?.data?.themes;

		for ( const index in items ) {
			if ( slug === items[ index ].slug ) {
				items[ index ].wporg = true;
				return items[ index ];
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
				return responseData.data;
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
	 * @return {Promise<Object>} Theme object compatible for theme install screen.
	 */
	async prepareThemeData( item ) {
		const imageRequestUrl = item._links[ 'wp:featuredmedia' ][ 0 ].href;
		// eslint-disable-next-line no-console
		console.log( `Fetching theme data: ${ imageRequestUrl }` );
		let attachment = await axios.get( imageRequestUrl );
		attachment = attachment.data;

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
		console.log( `Fetching plugin ${ slug } from WordPress.org.` );
		const filters = {
			search: slug,
			page: 1,
			per_page: 100,
		};

		const response = await this.getPluginsList( filters );
		const items = response?.data?.plugins;

		for ( const index in items ) {
			if ( slug === items[ index ].slug ) {
				items[ index ].wporg = true;
				return items[ index ];
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
	 * @return {Promise<Object>} Plugin object compatible for plugin install screen.
	 */
	async preparePluginData( item ) {
		const imageRequestUrl = item._links[ 'wp:featuredmedia' ][ 0 ].href;
		// eslint-disable-next-line no-console
		console.log( `Fetching theme data: ${ imageRequestUrl }` );
		let attachment = await axios.get( imageRequestUrl );
		attachment = attachment.data;

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
