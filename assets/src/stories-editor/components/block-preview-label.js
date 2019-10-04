/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { getBlockType } from '@wordpress/blocks';
import { BlockIcon } from '@wordpress/block-editor';
import { useSelect } from '@wordpress/data';
import { __experimentalGetSettings as getDateSettings, dateI18n } from '@wordpress/date';
import { __ } from '@wordpress/i18n';

const BlockPreviewLabel = ( { block, label, displayIcon = true, alignIcon = 'left', accessibilityText = false } ) => {
	const { attributes, name } = block;

	const {
		content,
		icon,
	} = useSelect( ( select ) => {
		const { getEditedPostAttribute } = select( 'core/editor' );
		const { getAuthors, getMedia } = select( 'core' );

		const blockType = getBlockType( name );

		label = blockType.title;
		let blockContent = '';

		switch ( name ) {
			case 'core/image':
				if ( attributes.url ) {
					blockContent = attributes.url.slice( attributes.url.lastIndexOf( '/' ) ).slice( 1, 30 );

					if ( blockContent.length > 0 ) {
						label = blockContent;
					}
				}

				if ( attributes.id ) {
					const media = getMedia( attributes.id );

					if ( media ) {
						label = media.caption.raw || media.title.raw || label;
					}
				}

				break;
			case 'amp/amp-story-text':
				if ( attributes.content.length > 0 ) {
					blockContent = attributes.content
						.replace( /<br>/g, ' ' )
						.replace( /<[^<>]+>/g, '' )
						.slice( 0, 30 );
				}

				label = blockContent.length > 0 ? blockContent : blockType.title;
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

			default:
				break;
		}

		return {
			content: label,
			icon: blockType.icon,
		};
	}, [ name, attributes ] );

	return (
		<>
			{ displayIcon && 'left' === alignIcon && <BlockIcon icon={ icon } /> }
			{ content.length > 20 ? `${ content.substr( 0, 20 ) }…` : content }
			{ accessibilityText && (
				<span className="screen-reader-text">
					{ accessibilityText }
				</span>
			) }
			{ displayIcon && 'right' === alignIcon && <BlockIcon icon={ icon } /> }
		</>
	);
};

BlockPreviewLabel.propTypes = {
	content: PropTypes.string.isRequired,
	block: PropTypes.object,
	label: PropTypes.string,
	displayIcon: PropTypes.bool,
	alignIcon: PropTypes.oneOf( [ 'left', 'right' ] ),
	accessibilityText: PropTypes.oneOfType( [
		PropTypes.bool,
		PropTypes.string,
	] ),
};

export default BlockPreviewLabel;
