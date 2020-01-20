/**
 * External dependencies
 */
import PropTypes from 'prop-types';

function MediaLibrary( { onInsert } ) {
	return (
		<>
			<button
				onClick={ () => onInsert( 'square', { backgroundColor: '#ff0000', width: 10, height: 5, x: 5, y: 5, rotationAngle: 0 } ) }
			>
				{ 'Insert small red square' }
			</button>
			<br />
			<button
				onClick={ () => onInsert( 'square', { backgroundColor: '#0000ff', width: 30, height: 15, x: 5, y: 35, rotationAngle: 0 } ) }
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
