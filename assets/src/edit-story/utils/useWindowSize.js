/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

const useWindowSize = () => {
	const [ width, setWidth ] = useState( 0 );
	const [ height, setHeight ] = useState( 0 );

	useEffect( () => {
		setWidth( window.innerWidth );
		setHeight( window.innerHeight );

		const onResize = () => {
			setWidth( window.innerWidth );
			setHeight( window.innerHeight );
		};

		window.addEventListener( 'resize', onResize );

		return () => {
			window.removeEventListener( 'resize', onResize );
		};
	}, [] );

	return {
		windowWidth: width,
		windowHeight: height,
	};
};

export default useWindowSize;
