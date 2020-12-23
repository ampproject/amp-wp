/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import { blockSources } from 'amp-block-validation';

/**
 * WordPress dependencies
 */
import { sprintf, __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from '../constants';
import AMPAlert from '../../../images/amp-alert.svg';
import AMPDelete from '../../../images/amp-delete.svg';
import { getErrorSourceTitle } from './get-error-source-title';

/**
 * @param {Object} props
 * @param {string} props.clientId Error client ID.
 * @param {string} props.blockTypeName Block type name.
 * @param {Object[]} props.sources List of source objects from the PHP backtrace.
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
	sources: PropTypes.arrayOf( PropTypes.object ),
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
 * @param {string} props.blockTypeTitle Title of the block type.
 * @param {string} props.clientId Block ID.
 */
function BlockType( { blockTypeTitle, clientId } ) {
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
 * @param {string} props.title Error title.
 * @param {Object} props.error Error details.
 * @param {Object[]} props.error.sources Sources from the PHP backtrace for the error.
 */
export function ErrorContent( { blockType, clientId, status, title, error: { sources } } ) {
	const blockTypeTitle = blockType?.title;
	const blockTypeName = blockType?.name;

	// @todo Refactor AMP_Validation_Error_Taxonomy::get_error_title_from_code() to return structured data.
	const [ titleText, nodeName ] = title.split( ':' ).map( ( item ) => item.trim() );

	return (
		<>
			{ ! clientId && (
				<p>
					{ __( 'This error comes from outside the post content.', 'amp' ) }
				</p>
			) }
			<dl className="amp-error__details">
				{
					// If node name is empty, the title text displayed in the panel header is enough.
					nodeName && (
						<>
							<dt>
								{ titleText }
							</dt>
							<dd dangerouslySetInnerHTML={ { __html: nodeName } } />
						</>
					)
				}
				<BlockType blockTypeTitle={ blockTypeTitle } clientId={ clientId } />
				<ErrorSource blockTypeName={ blockTypeName } clientId={ clientId } sources={ sources } />
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
	] ).isRequired,
	title: PropTypes.string.isRequired,
	error: PropTypes.shape( {
		sources: PropTypes.arrayOf( PropTypes.object ),
	} ).isRequired,
};
