/**
 * WordPress dependencies
 */
import { useInstanceId } from '@wordpress/compose';

/**
 * Check mark SVG.
 */
export function Check() {
	const id = useInstanceId( Check );

	return (
		<svg width="20" height="21" viewBox="0 0 20 21" fill="none" xmlns="http://www.w3.org/2000/svg">
			<mask id={ `mask-${ id }` } mask-type="alpha" maskUnits="userSpaceOnUse" x="2" y="5" width="16" height="12">
				<path d="M7.32923 14.137L3.85423 10.662L2.6709 11.837L7.32923 16.4953L17.3292 6.49531L16.1542 5.32031L7.32923 14.137Z" fill="white" />
			</mask>
			<g mask={ `url(#mask-${ id })` }>
				<rect y="0.90625" width="20" height="20" fill="white" />
			</g>
		</svg>
	);
}
