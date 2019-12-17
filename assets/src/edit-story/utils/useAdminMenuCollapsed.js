/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

const useAdminMenuCollapsed = () => {
	const [ isCollapsed, setIsCollapsed ] = useState( false );

	useEffect( () => {
		if ( document.body.classList.contains( 'folded' ) ) {
			setIsCollapsed( true );
		}

		const { MutationObserver } = window;
		const observer = new MutationObserver( ( mutations ) => {
			mutations.forEach( ( mutation ) => {
				if (
					( mutation.oldValue.match( /folded/ ) && ! mutation.target.classList.contains( 'folded' ) ) ||
					( mutation.oldValue.match( /folded/ ) && mutation.target.classList.contains( 'folded' ) )
				) {
					setIsCollapsed( ( _isCollapsed ) => ! _isCollapsed );
				}
			} );
		} );

		if ( document.body ) {
			observer.observe( document.body, {
				attributes: true,
				attributeOldValue: true,
				attributeFilter: [ 'class' ],
			} );
		}

		return () => {
			observer.disconnect();
		};
	}, [] );

	return isCollapsed;
};

export default useAdminMenuCollapsed;
