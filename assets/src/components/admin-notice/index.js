/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { VisuallyHidden } from '@wordpress/components';
import { useCallback, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import './style.scss';

export const ADMIN_NOTICE_TYPE_INFO = 'info';
export const ADMIN_NOTICE_TYPE_SUCCESS = 'success';
export const ADMIN_NOTICE_TYPE_WARNING = 'warning';
export const ADMIN_NOTICE_TYPE_ERROR = 'error';

/**
 * WordPress admin notice.
 *
 * @param {Object}   props               Component props.
 * @param {Element}  props.children      Component children.
 * @param {boolean}  props.isDismissible Indicates whether the notice should be dismissible.
 * @param {Function} props.onDismiss     Function to be called whenever the notice gets dismissed.
 * @param {string}   props.type          Specifies type of the notice.
 */
export function AdminNotice( {
	children,
	isDismissible = false,
	onDismiss,
	type = ADMIN_NOTICE_TYPE_INFO,
} ) {
	const [ dismissed, setDismissed ] = useState( false );

	const dismissHandler = useCallback( () => {
		setDismissed( true );

		if ( typeof onDismiss === 'function' ) {
			onDismiss();
		}
	}, [ onDismiss ] );

	if ( isDismissible && dismissed ) {
		return null;
	}

	return (
		<div
			className={ classnames( 'admin-notice', {
				'admin-notice--dismissible': isDismissible,
				'admin-notice--info': type === ADMIN_NOTICE_TYPE_INFO,
				'admin-notice--success': type === ADMIN_NOTICE_TYPE_SUCCESS,
				'admin-notice--warning': type === ADMIN_NOTICE_TYPE_WARNING,
				'admin-notice--error': type === ADMIN_NOTICE_TYPE_ERROR,
			} ) }
		>
			{ children }
			{ isDismissible && (
				<button
					type="button"
					onClick={ dismissHandler }
					className="admin-notice__dismiss"
				>
					<VisuallyHidden>
						{ __( 'Dismiss', 'amp' ) }
					</VisuallyHidden>
				</button>
			) }
		</div>
	);
}

AdminNotice.propTypes = {
	children: PropTypes.any,
	isDismissible: PropTypes.bool,
	onDismiss: PropTypes.func,
	type: PropTypes.oneOf( [
		ADMIN_NOTICE_TYPE_INFO,
		ADMIN_NOTICE_TYPE_SUCCESS,
		ADMIN_NOTICE_TYPE_WARNING,
		ADMIN_NOTICE_TYPE_ERROR,
	] ),
};
