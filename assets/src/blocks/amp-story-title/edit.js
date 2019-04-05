/**
 * Displays the title of the current post.
 * Mainly forked from the Core Heading block.
 */

/**
 * Internal dependencies
 */
import HeadingToolbar from './heading-toolbar';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Fragment } from '@wordpress/element';
import { select } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import {
	RichText,
	BlockControls,
	AlignmentToolbar,
} from '@wordpress/block-editor';

export default function StoryTitleEdit( {
	attributes,
	setAttributes,
	mergeBlocks,
	insertBlocksAfter,
	onReplace,
	className,
} ) {
	const { align, level } = attributes;
	const tagName = 'h' + level;
	const post = select( 'core/editor' ).getCurrentPost();
	let displayTitle;
	if ( post && post.title && 'auto-draft' !== post.status ) {
		displayTitle = post.title;
	} else {
		displayTitle = __( '(Please set a title)', 'amp' );
	}

	return (
		<Fragment>
			<BlockControls>
				<HeadingToolbar minLevel={ 2 } maxLevel={ 5 } selectedLevel={ level } onChange={ ( newLevel ) => setAttributes( { level: newLevel } ) } />
				<AlignmentToolbar
					value={ align }
					onChange={ ( nextAlign ) => {
						setAttributes( { align: nextAlign } );
					} }
				/>
			</BlockControls>
			<RichText
				identifier="content"
				wrapperClassName="wp-block-heading"
				tagName={ tagName }
				value={ displayTitle }
				onChange={ ( value ) => setAttributes( { content: value } ) }
				onMerge={ mergeBlocks }
				unstableOnSplit={
					insertBlocksAfter ?
						( before, after, ...blocks ) => {
							setAttributes( { content: before } );
							insertBlocksAfter( [
								...blocks,
								createBlock( 'core/paragraph', { content: after } ),
							] );
						} :
						undefined
				}
				onRemove={ () => onReplace( [] ) }
				style={ { textAlign: align } }
				formattingControls={ [] }
				className={ className }
			/>
		</Fragment>
	);
}
