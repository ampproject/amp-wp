/**
 * External dependencies
 */
import PropTypes from 'prop-types';

function MediaLibrary( { onInsert } ) {
	return (
		<>
			<button
				onClick={ () => onInsert( 'image', { src: 'https://vignette.wikia.nocookie.net/spongebob/images/d/d7/SpongeBob_stock_art.png/revision/latest?cb=20190921125147', width: 20, height: 10, x: 5, y: 5 } ) }
			>
				{ 'Insert spongebob' }
			</button>
			<br />
			<button
				onClick={ () => onInsert( 'image', { src: 'https://www.character-online.com/content/images/thumbs/0006703_teletubbies-twist-chime-figure-dipsy.jpeg', width: 20, height: 10, x: 5, y: 5 } ) }
			>
				{ 'Insert teletubbie' }
			</button>
		</>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;
