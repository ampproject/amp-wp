/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useState } from '@wordpress/element';

// Disable reason: Should not check indentation in code samples inside
// markdown, but it does.

/* eslint-disable jsdoc/check-indentation */

/**
 * This hook creates a handler to use for double click listening
 * on a node, where single clicks are also relevant. Default timeout
 * is 200 ms. If no subsequent click has been recorded in the given
 * time, single click will be assumed.
 *
 * This hook returns a handler retrieval function, not the handler
 * itself. This allows the component to use the same hook for multiple
 * children listening for events.
 *
 * For example:
 * ```
 * function SomeComponent() {
 *   const handleSingle = useCallback( ( evt, item ) => {
 *     // Handle single click on `item`
 *   }, [] );
 *   const handleDouble = useCallback( ( evt, item ) => {
 *     // Handle double click on `item`
 *   }, [] );
 *   const getHandler = useDoubleClick( handleSingle, handleDouble );
 *   const items = [
 *     // Some objects with `id` and `name` attributes.
 *   ];
 *   return items.map( ( item} ) => (
 *     <button key={ item.id } onClick={ getHandler( item ) }>
 *       { item.name }
 *     </button>
 *   ) );
 * }
 * ```
 *
 * @param {Function} onSingleClick  Handler to activate on single click.
 * @param {Function} onDoubleClick  Handler to activate on double click.
 * @param {number}   ms             Timeout in ms to wait - defaults to 200.
 * @return {Function} Handler retrieval function to get an onClick listener (invoke with unique value).
 */
const useDoubleClick = ( onSingleClick, onDoubleClick, ms = null ) => {
	const [ target, setTarget ] = useState( null );
	const [ lastEvent, setLastEvent ] = useState( null );
	const getHandler = useCallback( ( newTarget ) => ( evt ) => {
		evt.stopPropagation();

		if ( target !== newTarget ) {
			if ( target ) {
				onSingleClick( evt, target );
			}
			setTarget( newTarget );
			evt.persist();
			setLastEvent( evt );
			return;
		}

		onDoubleClick( evt, target );
		setTarget( null );
	}, [ onSingleClick, onDoubleClick, target ] );
	useEffect( () => {
		if ( ! target ) {
			return undefined;
		}
		const int = setTimeout( () => {
			setTarget( null );
			onSingleClick( lastEvent, target );
		}, ms || DEFAULT_MS );
		return () => {
			clearTimeout( int );
		};
	}, [ target, lastEvent, onSingleClick, ms ] );

	return getHandler;
};

/* eslint-enable jsdoc/check-indentation */

export default useDoubleClick;

const DEFAULT_MS = 200;
