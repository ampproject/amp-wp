/**
 * A testing class that gets and sets values.
 *
 * @class
 */
export class Mock {
	/**
	 * Gets a given value.
	 *
	 * @param {string} key The key of the value to get.
	 * @return {mixed} value The value corresponding to the key.
	 */
	get( key ) {
		if ( this.hasOwnProperty( key ) ) {
			return this[ key ];
		}

		return undefined;
	}

	/**
	 * Sets the values.
	 *
	 * @param {Object} values The values to set.
	 */
	set( values ) {
		for ( const property in values ) {
			if ( values.hasOwnProperty( property ) ) {
				this[ property ] = values[ property ];
			}
		}
	}
}

/**
 * Mocks the secondary object that stores the file error.
 *
 * @class
 */
export class AlternateMock extends Mock {
	/**
	 * Sets the error value.
	 *
	 * Different from Mock.set, as it accepts 2 arguments.
	 *
	 * @param {string} key   The name of the error.
	 * @param {Object} value The file type error object.
	 */
	set( key, value ) {
		this[ key ] = value;
	}

	/**
	 * Unsets the key and value stored at a given key.
	 *
	 * @param {string} key The key of the value to unset.
	 */
	unset( key ) {
		delete this[ key ];
	}
}

/**
 * Mocks a selection error.
 *
 * @class
 */
export class MockSelectionError extends Mock {
	/**
	 * Class constructor.
	 *
	 * @param {Object} errorData Error data.
	 */
	constructor( errorData = {} ) {
		super( errorData );

		for ( const name in errorData ) {
			if ( errorData.hasOwnProperty( name ) ) {
				this[ name ] = errorData[ name ];
			}
		}
	}
}
