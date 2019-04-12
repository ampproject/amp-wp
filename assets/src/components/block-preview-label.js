/**
 * WordPress dependencies
 */
import { Fragment } from '@wordpress/element';
import { getBlockType } from '@wordpress/blocks';
import { BlockIcon } from '@wordpress/block-editor';
import { withSelect } from '@wordpress/data';
import { __experimentalGetSettings as getDateSettings, dateI18n } from '@wordpress/date';
import { __ } from '@wordpress/i18n';

const BlockPreviewLabel = ( { content, icon, displayIcon = true, alignIcon = 'left', accessibilityText } ) => {
	return (
		<Fragment>
			{ displayIcon && 'left' === alignIcon && <BlockIcon icon={ icon } /> }
			{ content.length > 20 ? `${ content.substr( 0, 20 ) }…` : content }
			{ accessibilityText && <span className="screen-reader-text">{ accessibilityText }</span> }
			{ displayIcon && 'right' === alignIcon && <BlockIcon icon={ icon } /> }
		</Fragment>
	);
};

export default withSelect( ( select, { block } ) => {
	const { getEditedPostAttribute } = select( 'core/editor' );
	const { getAuthors, getMedia } = select( 'core' );

	const blockType = getBlockType( block.name );

	let label = blockType.title;
	let content;

	switch ( block.name ) {
		case 'core/image':
			if ( block.attributes.url ) {
				content = block.attributes.url.slice( block.attributes.url.lastIndexOf( '/' ) ).slice( 1, 30 );

				if ( content.length > 0 ) {
					label = content;
				}
			}

			if ( block.attributes.id ) {
				const media = getMedia( block.attributes.id );

				if ( media ) {
					label = media.caption.raw || media.title.raw || label;
				}
			}

			break;
		case 'amp/amp-story-text':
			content = block.attributes.content.length > 0 ? block.attributes.content.replace( /<[^<>]+>/g, ' ' ).slice( 0, 30 ) : '';

			label = content.length > 0 ? content : blockType.title;
			break;

		case 'amp/amp-story-post-author':
			const author = getAuthors().find( ( { id } ) => id === getEditedPostAttribute( 'author' ) );

			label = author ? author.name : __( 'Post Author', 'amp' );
			break;

		case 'amp/amp-story-post-date':
			const postDate = getEditedPostAttribute( 'date' );
			const dateSettings = getDateSettings();
			const dateFormat = dateSettings.formats.date;
			const date = postDate || new Date();

			label = dateI18n( dateFormat, date );
			break;

		case 'amp/amp-story-post-title':
			label = getEditedPostAttribute( 'title' ) || blockType.title;
			break;
	}

	return {
		content: label,
		icon: blockType.icon,
	};
} )( BlockPreviewLabel );
