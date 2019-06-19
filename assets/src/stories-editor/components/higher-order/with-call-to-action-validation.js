/**
 * WordPress dependencies
 */
import { getBlockType } from '@wordpress/blocks';
import { Button } from '@wordpress/components';
import { withSelect, withDispatch } from '@wordpress/data';
import { Warning } from '@wordpress/block-editor';
import { createHigherOrderComponent, compose } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';

const enhance = compose(
	/**
	 * For blocks which are only allowed once per page,  provides the
	 * wrapped component with `originalBlockClientId` -- a reference to the
	 * first block of the same type on the page -- if and only if that
	 * "original" block is not the current one. Thus, an non-existent
	 * `originalBlockClientId` prop signals that the block is valid.
	 *
	 * @param {Component} WrappedBlockEdit A filtered BlockEdit instance.
	 *
	 * @return {Component} Enhanced component with merged state data props.
	 */
	withSelect( ( select, props ) => {
		const { getBlockRootClientId, getBlock, getBlockOrder, getBlocksByClientId } = select( 'core/block-editor' );

		if ( 'amp/amp-story-cta' !== props.name ) {
			return {};
		}

		const parentBlock = getBlock( getBlockRootClientId( props.clientId ) );

		if ( ! parentBlock ) {
			return {};
		}

		const isFirstPage = getBlockOrder().indexOf( parentBlock.clientId ) === 0;
		const blocksOnPage = getBlocksByClientId( getBlockOrder( parentBlock.clientId ) );
		const firstOfSameType = blocksOnPage.find( ( { name } ) => name === props.name );
		const isInvalid = firstOfSameType && firstOfSameType.clientId !== props.clientId;

		return {
			isInvalid: isFirstPage || isInvalid,
			originalBlockClientId: isInvalid && firstOfSameType.clientId,
		};
	} ),
	withDispatch( ( dispatch, { originalBlockClientId } ) => ( {
		selectFirst: () => dispatch( 'core/block-editor' ).selectBlock( originalBlockClientId ),
	} ) ),
);

export default createHigherOrderComponent( ( BlockEdit ) => {
	return enhance( ( {
		isInvalid,
		originalBlockClientId,
		selectFirst,
		...props
	} ) => {
		if ( ! isInvalid || 'amp/amp-story-cta' !== props.name ) {
			return <BlockEdit { ...props } />;
		}

		const blockType = getBlockType( props.name );

		const actions = [
			<Button key="remove" isLarge onClick={ () => props.onReplace( [] ) }>
				{ __( 'Remove', 'amp' ) }
			</Button>,
		];

		if ( originalBlockClientId ) {
			actions.unshift(
				<Button key="find-original" isLarge onClick={ selectFirst }>
					{ __( 'Find original', 'amp' ) }
				</Button>
			);
		}

		return (
			<Warning actions={ actions }>
				<strong>{ blockType.title }: </strong>
				{ originalBlockClientId ?
					__( 'This block can only be used once per page.', 'amp' ) :
					__( 'This block can not be used on the first page.', 'amp' )
				}
			</Warning>
		);
	} );
}, 'withCallToActionValidation' );
