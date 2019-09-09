/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { IconButton } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import addTemplateIcon from '../../../../images/stories-editor/add-template.svg';
import './edit.css';

function PageInserter() {
	const { insertBlock } = useDispatch( 'core/block-editor' );
	const onClick = () => {
		insertBlock( createBlock( 'amp/amp-story-page' ) );
	};

	return (
		<IconButton
			icon={ addTemplateIcon( { width: 16, height: 16 } ) }
			label={ __( 'Insert Blank Page', 'amp' ) }
			onClick={ onClick }
			className="block-editor-inserter editor-inserter__amp-inserter"
		/>
	);
}

export default PageInserter;
