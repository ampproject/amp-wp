/**
 * External dependencies
 */
import PropTypes from 'prop-types';

function MediaLibrary( { onInsert } ) {
	return (
		<>
			<button
				onClick={ () => onInsert( 'square', { backgroundColor: 'red', width: 10, height: 5, x: 5, y: 5 } ) }
			>
				{ 'Insert small red square' }
			</button>
			<br />
			<button
				onClick={ () => onInsert( 'square', { backgroundColor: 'blue', width: 30, height: 15, x: 5, y: 35 } ) }
			>
				{ 'Insert big blue square' }
			</button>
		</>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;
