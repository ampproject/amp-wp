/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch';
import { useCallback, useEffect, useRef, useState } from '@wordpress/element';
import { Spinner } from '@wordpress/components';

const Image = styled.img`
	height: 150px;
	width: 150px;
	padding: 4px;
	margin: 4px;
	border: 1px solid white;
`;

function MediaLibrary( { onInsert } ) {

	const [ selectedMedia, setSelectedMedia ] = useState( null );
	const [ isFetching, setIsFetching ] = useState( false );
	const fetchRequest = useRef( null );
	const isStillMounted = useRef( true );

	const fetchSelectedPost = useCallback( () => {
		isStillMounted.current = true;
		setIsFetching( true );
		const currentFetchRequest = fetchRequest.current = apiFetch( {
			path: `/wp/v2/media/`,
		} ).then(
			( post ) => {
				if (isStillMounted.current && fetchRequest.current === currentFetchRequest) {
					setSelectedMedia( post );
					setIsFetching( false );
				}
			},
		).catch(
			() => {
				if (isStillMounted.current && fetchRequest.current === currentFetchRequest) {
					setSelectedMedia( null );
					setIsFetching( false );
				}
			},
		);

	}, [] );

	useEffect( () => {
		return () => {
			isStillMounted.current = false;
		};
	}, [] );

	useEffect( () => {
		fetchSelectedPost();
	}, [ fetchSelectedPost ] );

	return (
		<>
			{ isFetching && <Spinner /> }
			{ ! isFetching && selectedMedia && Boolean( selectedMedia.length ) ? (
				<div>
					{ selectedMedia.map( ( attachment ) => (
						<Image
							   src={ attachment.guid.rendered }
							   width={ 150 }
							   height={ 150 }
							   onClick={ () => onInsert( 'image', {
								   src: attachment.guid.rendered,
								   width: 20,
								   height: 10,
								   x: 5,
								   y: 5,
							   } ) }/>
					) )
					}
				</div>
			) : ( <div>No media found</div> )
			}

		</>
	);
}

MediaLibrary.propTypes = {
	onInsert: PropTypes.func.isRequired,
};

export default MediaLibrary;
