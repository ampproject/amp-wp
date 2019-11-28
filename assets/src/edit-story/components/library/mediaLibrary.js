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
	padding: 3px;
	margin: 3px;
	border: 1px solid white;
`;

const Title = styled.h3`
	color: ${ ( { theme } ) => theme.colors.fg.v1 };
	margin: 0px;
	font-size: 19px;
	line-height: 20px;
	line-height: 1.4em;
	flex: 2 0 0;
`;

const Button = styled.button`
	 background: #242A3B;
	 color: #ffffff;
	 padding: 5px;
	 font-weight: bold;
	 flex: 1 0 0;
	 text-align: center;
	 border: 0px none;
`

const Header = styled.div`
	display: flex;
	margin: 0px 4px 10px;
`

function MediaLibrary( { onInsert } ) {
	const {
		state: { media, isMediaLoading, isMediaLoaded },
		actions: { loadMedia, setIsMediaLoading, setIsMediaLoaded },
	} = useLibrary();

	useEffect( loadMedia );

	if ( ! isMediaLoaded || isMediaLoading) {
		return <Spinner />;
	}

	if ( ! media.length ) {
		return (
			<div>
				{ 'No media found' }
			</div>
		);
	}

	const mediaPicker = () => {
		// Create the media frame.
		const fileFrame = wp.media( {
			multiple: false,
			library: {
				type: 'image',
			},
		} );
		let attachment;

		// When an image is selected, run a callback.
		fileFrame.on( 'select', () => {
			attachment = fileFrame.state().get( 'selection' ).first().toJSON();
			const { url } = attachment;
			onInsert( 'image', {
				src: url,
				width: 20,
				height: 10,
				x: 5,
				y: 5,
			} );
		} );

		fileFrame.on( 'close', () => {
			setIsMediaLoading( false );
			setIsMediaLoaded( false );
		} );
		// Finally, open the modal
		fileFrame.open();
	};


	const uploadMedia = () => {
		mediaPicker();
	}

	return (
		<div>
			<Header>
				<Title>{ 'Media' }</Title>
				<Button onClick={ uploadMedia }>{ 'Upload Media' }</Button>
			</Header>
			{ media.map( ( { src } ) => (
				<Image
					key={ src }
					src={ src }
					width={ 150 }
					height={ 150 }
					onClick={ () => onInsert( 'image', {
						src,
						width: 200,
						height: 100,
						x: 5,
						y: 5,
						rotationAngle: 0,
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
