/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { AMPNotice } from '../amp-notice';
import ClipboardButton from '../clipboard-button';

/**
 * To render uuid on site support page.
 *
 * @param {Object}   props          Component props.
 * @param {Object}   props.state    UUID
 * @param {Function} props.setState Function to change the state.
 * @return {JSX.Element|null} HTML markup to render UUID.
 */
export function UUID( { state, setState } ) {
	if ( state.uuid ) {
		return (
			<AMPNotice type="info" size="small">
				{ __( 'Support UUID: ', 'amp' ) }
				<code>
					{ state.uuid }
				</code>
				<ClipboardButton
					isSmall={ true }
					text={ state.uuid }
					onCopy={ () => setState( { hasCopied: true } ) }
					onFinishCopy={ () => setState( { hasCopied: false } ) }
				>
					{ state.hasCopied ? __( 'Copied!', 'amp' ) : __( 'Copy UUID', 'amp' ) }
				</ClipboardButton>
			</AMPNotice>
		);
	}

	return null;
}

UUID.propTypes = {
	state: PropTypes.shape( {
		uuid: PropTypes.string.isRequired,
		hasCopied: PropTypes.bool,
	} ),
	setState: PropTypes.func.isRequired,
};
