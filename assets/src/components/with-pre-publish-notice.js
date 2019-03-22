/**
 * WordPress dependencies
 */
import { Notice } from '@wordpress/components';
import { select } from '@wordpress/data';
import { PluginPrePublishPanel } from '@wordpress/edit-post';
import { Fragment } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

export default function() {
	const featuredMedia = select( 'core/editor' ).getEditedPostAttribute( 'featured_media' );
	if ( featuredMedia ) {
		return PluginPrePublishPanel;
	}

	return (
		<Fragment>
			<PluginPrePublishPanel
				title={ __( 'Featured Image', 'amp' ) }
				initialOpen="true"
			>
				<Notice status="warning">
					<span>
						{ __( 'There is no featured image, which is required for an AMP Story.', 'amp' ) }
					</span>
				</Notice>
			</PluginPrePublishPanel>
		</Fragment>
	);
}
