/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';

const useDoubleClick = ( onClick, onDoubleClick ) => {
	const [ target, setTarget ] = useState( null );
	const [ lastEvent, setLastEvent ] = useState( null );
	const getHandler = useCallback( ( newTarget ) => ( evt ) => {
		evt.stopPropagation();

		if ( target !== newTarget ) {
			if ( target ) {
				onClick( target, evt );
			}
			setTarget( newTarget );
			evt.persist();
			setLastEvent( evt );
			return;
		}

		onDoubleClick( target, evt );
		setTarget( null );
	}, [ onClick, onDoubleClick, target ] );
	useEffect( () => {
		if ( ! target ) {
			return undefined;
		}
		const int = setTimeout( () => {
			setTarget( null );
			onClick( target, lastEvent );
		}, 200 );
		return () => {
			clearTimeout( int );
		};
	}, [ target, lastEvent, onClick ] );

	return getHandler;
};

export default useDoubleClick;
