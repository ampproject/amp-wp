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
import { useRef } from '@wordpress/element';
import { __experimentalGetSettings as getDateSettings, dateI18n } from '@wordpress/date';
import { __ } from '@wordpress/i18n';

const BlockPreviewLabel = ( { block, label, displayIcon = true, alignIcon = 'left', accessibilityText = false } ) => {
	const blockPreviewLabel = useRef( label );

	const {
		content,
		icon,
	} = useSelect( ( select ) => {
		if ( ! block ) {
			return {
				content: blockPreviewLabel.current,
				icon: null,
			};
		}

		const { attributes, name } = block;

		const { getEditedPostAttribute } = select( 'core/editor' );
		const { getAuthors, getMedia } = select( 'core' );

		const blockType = getBlockType( name );

		blockPreviewLabel.current = blockType.title;
		let blockContent = '';

		switch ( name ) {
			case 'core/image':
				if ( attributes.url ) {
					blockContent = attributes.url.slice( attributes.url.lastIndexOf( '/' ) ).slice( 1, 30 );

					if ( blockContent.length > 0 ) {
						blockPreviewLabel.current = blockContent;
					}
				}

				if ( attributes.id ) {
					const media = getMedia( attributes.id );

					if ( media ) {
						blockPreviewLabel.current = media.caption.raw || media.title.raw || blockPreviewLabel.current;
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

				blockPreviewLabel.current = blockContent.length > 0 ? blockContent : blockType.title;
				break;

			case 'amp/amp-story-post-author':
				const author = getAuthors().find( ( { id } ) => id === getEditedPostAttribute( 'author' ) );

				blockPreviewLabel.current = author ? author.name : __( 'Post Author', 'amp' );
				break;

			case 'amp/amp-story-post-date':
				const postDate = getEditedPostAttribute( 'date' );
				const dateSettings = getDateSettings();
				const dateFormat = dateSettings.formats.date;
				const date = postDate || new Date();

				blockPreviewLabel.current = dateI18n( dateFormat, date );
				break;

			case 'amp/amp-story-post-title':
				blockPreviewLabel.current = getEditedPostAttribute( 'title' ) || blockType.title;
				break;

			default:
				break;
		}

		return {
			content: blockPreviewLabel.current,
			icon: blockType.icon,
		};
	}, [ block ] );

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
