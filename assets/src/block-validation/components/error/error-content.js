/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import {
	blockSources,
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from 'amp-block-validation';

/**
 * WordPress dependencies
 */
import { sprintf, __ } from '@wordpress/i18n';
import { BlockIcon } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import AMPAlert from '../../../../images/amp-alert.svg';
import AMPDelete from '../../../../images/amp-delete.svg';
import { getErrorSourceTitle } from './get-error-source-title';

/**
 * @param {Object}   props
 * @param {string}   props.clientId      Error client ID.
 * @param {string}   props.blockTypeName Block type name.
 * @param {Object[]} props.sources       List of source objects from the PHP backtrace.
 */
function ErrorSource( { clientId, blockTypeName, sources } ) {
	let source;

	const blockSource = blockSources?.[ blockTypeName ];

	if ( clientId ) {
		switch ( blockSource?.source ) {
			case 'plugin':
				source = sprintf(
					/* translators: %s: plugin name. */
					__( `%s (plugin)`, 'amp' ),
					blockSource.title,
				);
				break;

			case 'mu-plugin':
				source = sprintf(
					/* translators: %s: plugin name. */
					__( `%s (must-use plugin)`, 'amp' ),
					blockSource.title,
				);
				break;

			case 'theme':
				source = sprintf(
					/* translators: %s: theme name. */
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

	if ( ! source ) {
		source = __( 'Unknown', 'amp' );
	}

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
ErrorSource.propTypes = {
	blockTypeName: PropTypes.string,
	clientId: PropTypes.string,
	sources: PropTypes.arrayOf( PropTypes.object ).isRequired,
};

/**
 * @param {Object} props
 * @param {number} props.status Error status.
 */
function MarkupStatus( { status } ) {
	let keptRemoved;
	if ( [ VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_ACCEPTED_STATUS ].includes( status ) ) {
		keptRemoved = (
			<span className="amp-error__kept-removed amp-error__kept-removed--removed">
				{ __( 'Removed', 'amp' ) }
				<span>
					<AMPDelete />
				</span>
			</span>
		);
	} else {
		keptRemoved = (
			<span className="amp-error__kept-removed amp-error__kept-removed--kept">
				{ __( 'Kept', 'amp' ) }
				<span>
					<AMPAlert />
				</span>
			</span>
		);
	}

	let reviewed;
	if ( [ VALIDATION_ERROR_ACK_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_REJECTED_STATUS ].includes( status ) ) {
		reviewed = __( 'Yes', 'amp' );
	} else {
		reviewed = __( 'No', 'amp' );
	}

	return (
		<>
			<dt>
				{ __( 'Markup status', 'amp' ) }
			</dt>
			<dd>
				{ keptRemoved }
			</dd>
			<dt>
				{ __( 'Reviewed', 'amp' ) }
			</dt>
			<dd>
				{ reviewed }
			</dd>
		</>
	);
}
MarkupStatus.propTypes = {
	status: PropTypes.number.isRequired,
};

/**
 * @param {Object} props
 * @param {Object} props.blockTypeIcon  Block type icon.
 * @param {string} props.blockTypeTitle Title of the block type.
 */
function BlockType( { blockTypeIcon, blockTypeTitle } ) {
	return (
		<>
			<dt>
				{ __( 'Block type', 'amp' ) }
			</dt>
			<dd>
				<span className="amp-error__block-type-description">
					{ blockTypeTitle || __( 'unknown', 'amp' ) }
					{ blockTypeIcon && (
						<span className="amp-error__block-type-icon">
							<BlockIcon icon={ blockTypeIcon } />
						</span>
					) }
				</span>
			</dd>
		</>
	);
}
BlockType.propTypes = {
	blockTypeIcon: PropTypes.object,
	blockTypeTitle: PropTypes.string,
};

/**
 * Content inside an error panel.
 *
 * @param {Object}   props               Component props.
 * @param {Object}   props.blockType     Block type details.
 * @param {boolean}  props.external      Flag indicating if the error comes from outside of post content.
 * @param {boolean}  props.removed       Flag indicating if the block has been removed.
 * @param {string}   props.clientId      Block client ID
 * @param {number}   props.status        Number indicating the error status.
 * @param {Object}   props.error         Error details.
 * @param {Object[]} props.error.sources Sources from the PHP backtrace for the error.
 */
export function ErrorContent( {
	blockType,
	clientId,
	error: { sources },
	external,
	removed,
	status,
} ) {
	const blockTypeTitle = blockType?.title;
	const blockTypeName = blockType?.name;
	const blockTypeIcon = blockType?.icon;

	return (
		<>
			{ removed && (
				<p>
					{ __( 'This error is no longer detected, either because the block was removed or the editor mode was switched.', 'amp' ) }
				</p>
			) }
			{ external && (
				<p>
					{ __( 'This error comes from outside the content (e.g. header or footer).', 'amp' ) }
				</p>
			) }
			<dl className="amp-error__details">
				{ ! ( removed || external ) && (
					<BlockType
						blockTypeIcon={ blockTypeIcon }
						blockTypeTitle={ blockTypeTitle }
					/>
				) }
				<ErrorSource blockTypeName={ blockTypeName } clientId={ clientId } sources={ sources } />
				<MarkupStatus status={ status } />
			</dl>
		</>
	);
}
ErrorContent.propTypes = {
	blockType: PropTypes.shape( {
		icon: PropTypes.object,
		name: PropTypes.string,
		title: PropTypes.string,
	} ),
	clientId: PropTypes.string,
	error: PropTypes.shape( {
		sources: PropTypes.arrayOf( PropTypes.object ),
	} ).isRequired,
	external: PropTypes.bool,
	removed: PropTypes.bool,
	status: PropTypes.oneOf( [
		VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
		VALIDATION_ERROR_ACK_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_REJECTED_STATUS,
		VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	] ).isRequired,
};
