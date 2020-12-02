/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Button } from '@wordpress/components';
import { useDispatch } from '@wordpress/data';
import { sprintf, __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { VALIDATION_ERROR_ACK_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_REJECTED_STATUS, VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_NEW_REJECTED_STATUS } from '../constants';
import { getErrorSourceTitle } from './get-error-source-title';

/**
 * @param {Object} props
 * @param {string} props.clientId Error client ID.
 * @param {string} props.blockTypeName Block type name.
 * @param {Object[]} props.sources List of source objects from the PHP backtrace.
 */
function ErrorSource( { clientId, blockTypeName, sources } ) {
	let source;

	const blockSource = global.ampBlockValidation.blockSources[ blockTypeName ];

	if ( clientId ) {
		switch ( blockSource?.source ) {
			case 'plugin':
				source = sprintf(
					// Translators: placeholder is the name of a plugin.
					__( `%s (plugin)`, 'amp' ),
					blockSource.title,
				);
				break;

			case 'mu-plugin':
				source = sprintf(
					// Translators: placeholder is the name of a plugin.
					__( `%s (must-use plugin)`, 'amp' ),
					blockSource.title,
				);
				break;

			case 'theme':
				source = sprintf(
					// Translators:placeholder is the name of a theme.
					__( `%s (theme)`, 'amp' ),
					blockSource.title,
				);
				break;

			default:
				source = blockSource?.title || getErrorSourceTitle( sources );
				break;
		}
	} else {
		source = getErrorSourceTitle( sources );
	}

	if ( source ) {
		return (
			<>
				<dt>
					{ __( 'Source', 'amp' ) }
				</dt>
				<dd>
					{ source }
				</dd>
			</>
		);
	}

	return null;
}
ErrorSource.propTypes = {
	blockTypeName: PropTypes.string,
	clientId: PropTypes.string,
	sources: PropTypes.arrayOf( PropTypes.object ),
};

/**
 * @param {Object} props
 * @param {number} props.status Error status.
 */
function MarkupStatus( { status } ) {
	let keptRemoved;
	if ( [ VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_ACCEPTED_STATUS ].includes( status ) ) {
		keptRemoved = __( 'Removed', 'amp' );
	} else {
		keptRemoved = __( 'Kept', 'amp' );
	}

	return (
		<>
			<dt>
				{ __( 'Markup status', 'amp' ) }
			</dt>
			<dd>
				{ keptRemoved }
			</dd>
		</>
	);
}
MarkupStatus.propTypes = {
	status: PropTypes.number,
};

/**
 * @param {Object} props
 * @param {string} props.blockTypeTitle Title of the block type.
 * @param {string} props.clientId Block ID.
 */
function BlockType( { blockTypeTitle, clientId } ) {
	const { selectBlock } = useDispatch( 'core/block-editor' );

	if ( clientId ) {
		return (
			<>
				<dt>
					{ __( 'Block type', 'amp' ) }
				</dt>
				<dd>
					<span className="amp-error__block-type-description">
						{ blockTypeTitle || __( 'unknown', 'amp' ) }
					</span>
					<Button
						className="amp-error__select-block"
						isLink
						onClick={ () => {
							selectBlock( clientId );
						} }
					>
						{ __( 'Select block', 'amp' ) }
					</Button>
				</dd>
			</>
		);
	}

	return null;
}
BlockType.propTypes = {
	blockTypeTitle: PropTypes.string,
	clientId: PropTypes.string,
};

/**
 * Content inside an error panel.
 *
 * @param {Object} props Component props.
 * @param {Object} props.blockType Block type details.
 * @param {string} props.clientId Block client ID
 * @param {number} props.status Number indicating the error status.
 * @param {Object} props.error Error details.
 * @param {Object[]} props.error.sources Sources from the PHP backtrace for the error.
 */
export function ErrorContent( { blockType, clientId, status, error: { sources } } ) {
	const blockTypeTitle = blockType?.title;
	const blockTypeName = blockType?.name;

	return (
		<>
			{ ! clientId && (
				<p>
					{ __( 'This error comes from outside the post content.', 'amp' ) }
				</p>
			) }
			<dl className="amp-error__details">
				<BlockType blockTypeName={ blockTypeName } clientId={ clientId } />
				<ErrorSource blockTypeTitle={ blockTypeTitle } clientId={ clientId } sources={ sources } />
				<MarkupStatus status={ status } />
			</dl>
		</>
	);
}
ErrorContent.propTypes = {
	blockType: PropTypes.shape( {
		name: PropTypes.string,
		title: PropTypes.string,
	} ),
	clientId: PropTypes.string,
	status: PropTypes.oneOf( [
		VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
		VALIDATION_ERROR_ACK_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	] ),
	error: PropTypes.shape( {
		sources: PropTypes.arrayOf( PropTypes.object ),
	} ),
};

