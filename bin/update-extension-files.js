/**
 * External dependencies
 */
const { getPluginsList, getThemesList } = require( 'wporg-api-client' );
const axios = require( 'axios' );

/**
 * Internal dependencies
 */
const filesystem = require( './file-system' );

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
			let output = this.convertToPhpArray( this.plugins );
			output = `<?php\nreturn ${ output };`;
			await filesystem.writeFile( 'includes/amp-plugins.php', output );
		}

		if ( this.themes ) {
			let output = this.convertToPhpArray( this.themes );
			output = `<?php\nreturn ${ output };`;
			await filesystem.writeFile( 'includes/amp-themes.php', output );
		}
	}

	/**
	 * Convert JS object into PHP array variable.
	 *
	 * @param {Object} object An object that needs to convert into a PHP array.
	 * @param {number} depth  Depth of iteration.
	 * @return {string|null} PHP array in string.
	 */
	convertToPhpArray( object, depth = 1 ) {
		if ( 'object' !== typeof object ) {
			return null;
		}

		const tabs = '\t'.repeat( depth );
		let output = '[';

		// eslint-disable-next-line guard-for-in
		for ( const key in object ) {
			let value = object[ key ];

			switch ( typeof value ) {
				case 'object':
					let childObjectOutput = this.convertToPhpArray( value, ( depth + 1 ) );
					childObjectOutput = childObjectOutput ? childObjectOutput : '[]';
					output += `\n${ tabs }'${ key }' => ${ childObjectOutput },`;
					break;
				case 'boolean':
					output += `\n${ tabs }'${ key }' => ${ value ? 'true' : 'false' },`;
					break;
				case 'string':
					value = value.toString().replace( /'/gm, `\\'` );
					output += `\n${ tabs }'${ key }' => '${ value }',`;
					break;
				case 'bigint':
				case 'number':
					output += `\n${ tabs }'${ key }' => ${ value },`;
					break;
				default:
					output += `\n${ tabs }'${ key }' => '',`;
					break;
			}
		}
		output += '\n' + '\t'.repeat( depth - 1 ) + ']';
		return output;
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
		const filters = {
			search: slug,
			page: 1,
			per_page: 100,
		};

		const response = await getThemesList( filters );
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
	 * Transform theme data fetched from amp-wp.org to compatible with theme install screen.
	 *
	 * @param {Object} item Theme object.
	 * @return {Promise<Object>} Theme object compatible for theme install screen.
	 */
	async prepareThemeData( item ) {
		const imageRequestUrl = item._links[ 'wp:featuredmedia' ][ 0 ].href;
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
		const filters = {
			search: slug,
			page: 1,
			per_page: 100,
		};

		const response = await getPluginsList( filters );
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
	 * Transform plugin data fetched from amp-wp.org to compatible with theme install screen.
	 *
	 * @param {Object} item Plugin object.
	 * @return {Promise<Object>} Plugin object compatible for plugin install screen.
	 */
	async preparePluginData( item ) {
		const imageRequestUrl = item._links[ 'wp:featuredmedia' ][ 0 ].href;
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
