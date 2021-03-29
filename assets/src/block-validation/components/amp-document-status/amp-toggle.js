/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { FormToggle, PanelRow } from '@wordpress/components';
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { useAMPDocumentToggle } from '../../hooks/use-amp-document-toggle';

/**
 * AMP toggle component.
 */
export default function AMPToggle() {
	const { isAMPEnabled, toggleAMP } = useAMPDocumentToggle();

	/**
	 * Use a random ID for the HTML input since the AMP toggle may be used
	 * more than once on the same page.
	 */
	const htmlId = useRef( `amp-toggle-${ Math.random().toString( 32 ).substr( -4 ) }` );

	return (
		<PanelRow>
			<label htmlFor={ htmlId.current }>
				{ __( 'Enable AMP', 'amp' ) }
			</label>
			<FormToggle
				checked={ isAMPEnabled }
				onChange={ toggleAMP }
				id={ htmlId.current }
			/>
		</PanelRow>
	);
}
