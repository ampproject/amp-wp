/**
 * Helper class for file management.
 */

/**
 * External dependencies
 */
const fs = require( 'fs' );
const _ = require( 'underscore' );

class FileSystem {
	/**
	 * Directory separator.
	 *
	 * @return {string} Directory separator.
	 */
	static get DS() {
		return '/';
	}

	/**
	 * Assure that given path is directory path.
	 * If file path is provided then it will return parent directory of given path.
	 *
	 * @param {string} path Path to check.
	 * @return {string} Directory path.
	 */
	static assureDirectoryPath( path ) {
		if ( _.isEmpty( path ) || ! _.isString( path ) ) {
			return '';
		}

		const lastSegment = path.toString().split( this.DS ).pop();

		if ( -1 !== lastSegment.indexOf( '.' ) ) {
			path = path.replace( `${ this.DS }${ lastSegment }`, '' );
		}

		return path;
	}

	/**
	 * Assure that directory is physically exists.
	 *
	 * @param {string} path Path for that need to make sure physical directory exists.
	 * @return {Promise<boolean>} True on success otherwise false.
	 */
	static assureDirectoryExists( path ) {
		path = this.assureDirectoryPath( path );

		return new Promise( ( done ) => {
			fs.mkdir( path, { recursive: true }, ( error ) => {
				if ( error ) {
					done( false );
				} else {
					done( true );
				}
			} );
		} );
	}

	/**
	 * Write content to file.
	 *
	 * @param {string} filePath File path.
	 * @param {string} content  Content of file.
	 * @return {Promise<boolean>} True on success otherwise false.
	 */
	static async writeFile( filePath, content ) {
		if ( _.isEmpty( filePath ) || ! _.isString( filePath ) ||
			_.isEmpty( content ) || ! _.isString( content ) ) {
			return false;
		}

		await this.assureDirectoryExists( filePath );

		return new Promise( ( done ) => {
			fs.writeFile( filePath, content, ( error ) => {
				if ( error ) {
					done( false );
				} else {
					done( true );
				}
			} );
		} );
	}
}

module.exports = FileSystem;
