/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { getBlockType } from '@wordpress/blocks';
import { Button } from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { Warning } from '@wordpress/block-editor';
import { createHigherOrderComponent } from '@wordpress/compose';
import { __ } from '@wordpress/i18n';
import { useCallback } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { isCTABlock } from '../../helpers';

export default createHigherOrderComponent( ( BlockEdit ) => {
	const CallToActionValidation = ( props ) => {
		const {
			isInvalid,
			originalBlockClientId,
		} = useSelect( ( select ) => {
			const {
				getBlockRootClientId,
				getBlock,
				getBlockOrder,
				getBlocksByClientId,
			} = select( 'core/block-editor' );

			if ( ! isCTABlock( props.name ) ) {
				return {};
			}

			const parentBlock = getBlock( getBlockRootClientId( props.clientId ) );

			if ( ! parentBlock ) {
				return {};
			}

			const isFirstPage = getBlockOrder().indexOf( parentBlock.clientId ) === 0;
			const blocksOnPage = getBlocksByClientId( getBlockOrder( parentBlock.clientId ) );
			const firstOfSameType = blocksOnPage.find( ( { name } ) => name === props.name );
			const _isInvalid = firstOfSameType && firstOfSameType.clientId !== props.clientId;

			return {
				isInvalid: isFirstPage || _isInvalid,
				/**
				 * This is a reference to the first block of the same type on the page -- if and only if that
				 * "original" block is not the current one. Thus, an non-existent
				 * `originalBlockClientId` prop signals that the block is valid.
				 */
				originalBlockClientId: _isInvalid && firstOfSameType.clientId,
			};
		}, [ props.name, props.clientId ] );

		const { selectBlock } = useDispatch( 'core/block-editor' );
		const selectFirst = useCallback(
			() => selectBlock( originalBlockClientId ),
			[ originalBlockClientId, selectBlock ]
		);

		if ( ! isInvalid || ! isCTABlock( props.name ) ) {
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
				<strong>
					{ `${ blockType.title }: ` }
				</strong>
				{ originalBlockClientId ?
					__( 'This block can only be used once per page.', 'amp' ) :
					__( 'This block can not be used on the first page.', 'amp' )
				}
			</Warning>
		);
	};

	CallToActionValidation.propTypes = {
		name: PropTypes.string.isRequired,
		clientId: PropTypes.string.isRequired,
		onReplace: PropTypes.func.isRequired,
	};

	return CallToActionValidation;
}, 'withCallToActionValidation' );
