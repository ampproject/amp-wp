/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { Spinner } from '@wordpress/components';
import { useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import useLibrary from './useLibrary';

const Image = styled.img`
	height: 150px;
	width: 150px;
	padding: 4px;
	margin: 4px;
	border: 1px solid white;
`;

function MediaLibrary( { onInsert } ) {
	const {
		state: { media, isMediaLoading, isMediaLoaded },
		actions: { loadMedia },
	} = useLibrary();

	useEffect( loadMedia );

	if ( isMediaLoading || ! isMediaLoaded ) {
		return <Spinner />;
	}

	if ( ! media.length ) {
		return (
			<div>
				{ 'No media found' }
			</div>
		);
	}

	return (
		<div>
			{ media.map( ( { src } ) => (
				<Image
					key={ src }
					src={ src }
					width={ 150 }
					height={ 150 }
					onClick={ () => onInsert( 'image', {
						src,
						width: 20,
						height: 10,
						x: 5,
						y: 5,
					} ) }
				/>
			) )
			}
		</div>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;
