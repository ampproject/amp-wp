/**
 * WordPress dependencies
 */
import { createHigherOrderComponent } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { Notice } from '@wordpress/components';

export default createHigherOrderComponent(
	( BlockEdit ) => {
		return ( props ) => (
			<>
				<Notice
					status="warning"
					isDismissible={ false }
				>
					{ __( 'This AMP-specific block has been deprecated and will removed in a future version of the AMP plugin.', 'amp' ) }
				</Notice>
				<BlockEdit { ...props } />
			</>
		);
	},
	'withAmpDeprecationNotice',
);
