/**
 * This is an almost 1:1 1:1 copy of the BlockTypesList component in @wordpress/block-editor.
 *
 * It is included here because the component is not exported to the public by that package.
 * The only modification compared to the original one is the addition of PropTypes.
 */

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { getBlockMenuDefaultClassName } from '@wordpress/blocks';

/**
 * Internal dependencies
 */
import InserterListItem from '../inserter-list-item';

function BlockTypesList( { items, onSelect, onHover = () => {}, children } ) {
	return (
		/*
		 * Disable reason: The `list` ARIA role is redundant but
		 * Safari+VoiceOver won't announce the list otherwise.
		 */
		/* eslint-disable jsx-a11y/no-redundant-roles */
		<ul role="list" className="editor-block-types-list block-editor-block-types-list">
			{ items && items.map( ( item ) =>
				<InserterListItem
					key={ item.id }
					className={ getBlockMenuDefaultClassName( item.id ) }
					icon={ item.icon }
					hasChildBlocksWithInserterSupport={
						item.hasChildBlocksWithInserterSupport
					}
					onClick={ () => {
						onSelect( item );
						onHover( null );
					} }
					onFocus={ () => onHover( item ) }
					onMouseEnter={ () => onHover( item ) }
					onMouseLeave={ () => onHover( null ) }
					onBlur={ () => onHover( null ) }
					isDisabled={ item.isDisabled }
					title={ item.title }
				/>
			) }
			{ children }
		</ul>
		/* eslint-enable jsx-a11y/no-redundant-roles */
	);
}

BlockTypesList.propTypes = {
	items: PropTypes.arrayOf( PropTypes.shape( {
		id: PropTypes.string.isRequired,
		icon: PropTypes.shape( {
			background: PropTypes.string,
			foreground: PropTypes.string,
			shadowColor: PropTypes.string,
		} ),
		hasChildBlocksWithInserterSupport: PropTypes.bool.isRequired,
		title: PropTypes.string.isRequired,
		isDisabled: PropTypes.bool.isRequired,
	} ) ),
	onSelect: PropTypes.func.isRequired,
	onHover: PropTypes.func,
	children: PropTypes.any,
};

export default BlockTypesList;
