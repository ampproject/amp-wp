/**
 * WordPress dependencies
 */
import { useSelect } from '@wordpress/data';
import { useMemo } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { BLOCK_VALIDATION_STORE_KEY } from '../../store';

/**
 * Adds a style rule for all blocks with validation errors.
 */
export function InvalidBlockOutline() {
	const validationErrors = useSelect( ( select ) => select( BLOCK_VALIDATION_STORE_KEY ).getUnreviewedValidationErrors(), [] );

	const selectors = useMemo( () => {
		const clientIds = validationErrors.map( ( { clientId } ) => clientId )
			.filter( ( clientId ) => clientId );

		return clientIds.map( ( clientId ) => `#block-${ clientId }::before` );
	}, [ validationErrors ] );

	return (
		<style>
			{
				`${ selectors.join( ',' ) } {
					border-radius: 9px;
					bottom: -3px;
					box-shadow: 0 0 0 2px #bb522e;
					content: '';
					left: -3px;
					pointer-events: none;
					position: absolute;
					right: -3px;
					top: -3px;
				}`
			}
		</style>
	);
}
