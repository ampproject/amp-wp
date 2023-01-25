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

export const AMP_ADMIN_NOTICE_TYPE_INFO = 'info';
export const AMP_ADMIN_NOTICE_TYPE_SUCCESS = 'success';
export const AMP_ADMIN_NOTICE_TYPE_WARNING = 'warning';
export const AMP_ADMIN_NOTICE_TYPE_ERROR = 'error';

/**
 * WordPress admin notice.
 *
 * @param {Object}   props               Component props.
 * @param {Element}  props.children      Component children.
 * @param {string}   props.className     Additional class names.
 * @param {boolean}  props.isDismissible Indicates whether the notice should be dismissible.
 * @param {Function} props.onDismiss     Function to be called whenever the notice gets dismissed.
 * @param {string}   props.type          Specifies type of the notice.
 */
export function AmpAdminNotice({
	children,
	className,
	isDismissible = false,
	onDismiss,
	type = AMP_ADMIN_NOTICE_TYPE_INFO,
}) {
	const [dismissed, setDismissed] = useState(false);

	const dismissHandler = useCallback(() => {
		setDismissed(true);

		if (typeof onDismiss === 'function') {
			onDismiss();
		}
	}, [onDismiss]);

	if (isDismissible && dismissed) {
		return null;
	}

	return (
		<div
			className={classnames('amp-admin-notice', className, {
				'amp-admin-notice--dismissible': isDismissible,
				'amp-admin-notice--info': type === AMP_ADMIN_NOTICE_TYPE_INFO,
				'amp-admin-notice--success':
					type === AMP_ADMIN_NOTICE_TYPE_SUCCESS,
				'amp-admin-notice--warning':
					type === AMP_ADMIN_NOTICE_TYPE_WARNING,
				'amp-admin-notice--error': type === AMP_ADMIN_NOTICE_TYPE_ERROR,
			})}
		>
			{children}
			{isDismissible && (
				<button
					type="button"
					onClick={dismissHandler}
					className="amp-admin-notice__dismiss"
				>
					<VisuallyHidden as="span">
						{__('Dismiss', 'amp')}
					</VisuallyHidden>
				</button>
			)}
		</div>
	);
}

AmpAdminNotice.propTypes = {
	children: PropTypes.any,
	className: PropTypes.string,
	isDismissible: PropTypes.bool,
	onDismiss: PropTypes.func,
	type: PropTypes.oneOf([
		AMP_ADMIN_NOTICE_TYPE_INFO,
		AMP_ADMIN_NOTICE_TYPE_SUCCESS,
		AMP_ADMIN_NOTICE_TYPE_WARNING,
		AMP_ADMIN_NOTICE_TYPE_ERROR,
	]),
};
