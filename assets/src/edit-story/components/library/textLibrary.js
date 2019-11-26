/**
 * External dependencies
 */
import PropTypes from 'prop-types';

function MediaLibrary( { onInsert } ) {
	return (
		<>
			<button
				onClick={ () => onInsert( 'text', { content: 'Hello', color: 'black', width: 20, height: 10, x: 5, y: 5, rotationAngle: 0 } ) }
			>
				{ 'Insert default' }
			</button>
			<br />
			<button
				onClick={ () => onInsert( 'text', { content: 'World', color: 'purple', fontSize: '40px', fontFamily: 'Comic Sans MS', fontWeight: 'bold', width: 20, height: 10, x: 5, y: 5, rotationAngle: 0 } ) }
			>
				{ 'Insert big purple comic sans bold' }
			</button>
		</>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;
