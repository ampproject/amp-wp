/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useDispatch, useSelect } from '@wordpress/data';
import { Button, PanelBody } from '@wordpress/components';
import { sprintf, __ } from '@wordpress/i18n';
import { useMemo } from '@wordpress/element';
import { addQueryArgs } from '@wordpress/url';
import { BlockIcon } from '@wordpress/block-editor';

/**
 * Internal dependencies
 */
import HTMLErrorIcon from '../../images/amp-html-error-icon.svg';
import JSErrorIcon from '../../images/amp-js-error-icon.svg';
import CSSErrorIcon from '../../images/amp-css-error-icon.svg';
import { useInlineData } from './use-inline-data';
import {
	AMP_VALIDITY_REST_FIELD_NAME,
	VALIDATION_ERROR_ACK_ACCEPTED_STATUS,
	VALIDATION_ERROR_ACK_REJECTED_STATUS,
	VALIDATION_ERROR_NEW_ACCEPTED_STATUS,
	VALIDATION_ERROR_NEW_REJECTED_STATUS,
} from './constants';
import { NewTabIcon } from './icon';

/**
 * Component rendering an icon representing JS, CSS, or HTML.
 *
 * @param {Object} props
 * @param {string} props.type The error type.
 */
function ErrorTypeIcon( { type } ) {
	const {
		CSS_ERROR_TYPE,
		HTML_ATTRIBUTE_ERROR_TYPE,
		HTML_ELEMENT_ERROR_TYPE,
		JS_ERROR_TYPE,
	} = useInlineData( 'ampBlockValidation', {} );

	switch ( type ) {
		case HTML_ATTRIBUTE_ERROR_TYPE:
		case HTML_ELEMENT_ERROR_TYPE:
			return <HTMLErrorIcon />;

		case JS_ERROR_TYPE:
			return <JSErrorIcon />;

		case CSS_ERROR_TYPE:
			return <CSSErrorIcon />;

		default:
			return null;
	}
}
ErrorTypeIcon.propTypes = {
	type: PropTypes.string.isRequired,
};

/**
 * Panel title component for an individual error.
 *
 * @param {Object} props Component props.
 * @param {Object} props.blockType
 * @param {string} props.title Title string from error data.
 * @param {Object} props.error Error details.
 * @param {string} props.error.type Error type.
 */
function ErrorPanelTitle( { blockType, title, error: { type } } ) {
	return (
		<>
			<div className="amp-error__icons">
				<div className={ `amp-error__error-type-icon amp-error__error-type-icon--${ type.replace( /_/g, '-' ) }` }>
					<ErrorTypeIcon type={ type } />
				</div>
				{ blockType?.icon && (
					<div className="amp-error__block-type-icon">
						<BlockIcon icon={ blockType.icon } />
					</div>
				) }
			</div>
			<span className="amp-error__title-text" dangerouslySetInnerHTML={ { __html: title } } />
		</>
	);
}
ErrorPanelTitle.propTypes = {
	blockType: PropTypes.object,
	title: PropTypes.string.isRequired,
	error: PropTypes.shape( {
		type: PropTypes.string,
	} ).isRequired,
};

/**
 * Content inside an error panel.
 *
 * @param {Object} props Component props.
 * @param {Object} props.blockType Block type details.
 * @param {string} props.clientId Block client ID
 * @param {number} props.status Number indicating the error status.
 */
function ErrorContent( { blockType, clientId, status } ) {
	const { blockSources } = useInlineData( 'ampBlockValidation' );

	const { selectBlock } = useDispatch( 'core/block-editor' );

	const blockSource = blockSources[ blockType?.name ];
	const title = blockType?.title;

	const errorContent = useMemo( () => {
		const content = [];
		const uniqueKey = ( id ) => `error-${ clientId }-${ id }`;

		if ( clientId && blockSource ) {
			content.push(
				<dt key={ uniqueKey( 'block-type-title' ) }>
					{ __( 'Block type', 'amp' ) }
				</dt>,
				<dd key={ uniqueKey( 'block-type-description' ) }>
					{ title }
					<Button
						className="amp-error__select-block"
						isLink
						onClick={ () => {
							selectBlock( clientId );
						} }
					>
						{ __( 'Select Block', 'amp' ) }
					</Button>
				</dd>,

			);

			let source;

			switch ( blockSource.source ) {
				case 'plugin':
					source = sprintf(
						// Translators: placeholder is the name of a plugin.
						__( `%s (plugin)`, 'amp' ),
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
					source = blockSource.title || __( 'unknown' );
					break;
			}

			content.push(
				<dt key={ uniqueKey( 'source-title' ) }>
					{ __( 'Source', 'amp' ) }
				</dt>,
				<dd key={ uniqueKey( 'source-description' ) }>
					{ source }
				</dd>,
			);
		}

		let keptRemoved;
		if ( [ VALIDATION_ERROR_NEW_ACCEPTED_STATUS, VALIDATION_ERROR_ACK_ACCEPTED_STATUS ].includes( status ) ) {
			keptRemoved = __( 'Removed', 'amp' );
		} else {
			keptRemoved = __( 'Kept', 'amp' );
		}

		content.push(
			<dt key={ uniqueKey( 'markup-status-title' ) }>
				{ __( 'Markup status', 'amp' ) }
			</dt>,
			<dd key={ uniqueKey( 'markup-status-description' ) }>
				{ keptRemoved }
			</dd>,
		);

		return content;
	}, [ clientId, status, blockSource, selectBlock, title ] );

	return (
		<>
			{ ! clientId && (
				<p>
					{ __( 'This error comes from outside the post content.', 'amp' ) }
				</p>
			) }
			{ 0 < errorContent.length && (
				<dl className="amp-error__details">
					{ errorContent }
				</dl>
			) }
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
};

/**
 * Component rendering an individual error. Parent component is a <ul>.
 *
 * @param {Object} props Component props.
 * @param {string} props.clientId
 * @param {number} props.status
 * @param {number} props.term_id
 */
export function Error( { clientId, status, term_id: termId, ...props } ) {
	const { review_link: reviewLink } = useSelect( ( select ) => select( 'core/editor' ).getEditedPostAttribute( AMP_VALIDITY_REST_FIELD_NAME ) ) || {};

	const reviewed = status === VALIDATION_ERROR_ACK_ACCEPTED_STATUS || status === VALIDATION_ERROR_ACK_REJECTED_STATUS;

	const { blockType } = useSelect( ( select ) => {
		const blockDetails = clientId ? select( 'core/block-editor' ).getBlock( clientId ) : null;
		const blockTypeDetails = blockDetails ? select( 'core/blocks' ).getBlockType( blockDetails.name ) : null;

		return {
			blockType: blockTypeDetails,
		};
	}, [ clientId ] );

	return (
		<li className="amp-error-container">
			<PanelBody
				className={ `amp-error amp-error--${ reviewed ? 'reviewed' : 'new' }` }
				title={
					<ErrorPanelTitle { ...props } blockType={ blockType } status={ status } />
				}
				initialOpen={ false }
			>
				<ErrorContent { ...props } clientId={ clientId } blockType={ blockType } status={ status } />

				<div className="amp-error__actions">
					<a
						href={ addQueryArgs( reviewLink, { term_id: termId } ) }
						target="_blank"
						rel="noreferrer"
						className="amp-error__details-link"
					>
						{ __( 'View details', 'amp' ) }
						<NewTabIcon />
					</a>
				</div>

			</PanelBody>
		</li>
	);
}
Error.propTypes = {
	clientId: PropTypes.string,
	status: PropTypes.number.isRequired,
	term_id: PropTypes.number.isRequired,
};
