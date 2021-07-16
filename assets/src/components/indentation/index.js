/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const TAB = String.fromCharCode( 9 );
const NBSP = String.fromCharCode( 160 );

export default function Indentation( {
	size = 0,
	isTab = false,
} ) {
	return Array( size ).fill( isTab ? TAB : NBSP ).join( '' );
}
Indentation.propTypes = {
	size: PropTypes.number,
	isTab: PropTypes.bool,
};
