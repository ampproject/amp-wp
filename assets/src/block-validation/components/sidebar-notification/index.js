/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';
import { ReactNode } from 'react';

/**
 * Internal dependencies
 */
import './style.css';
import { Loading } from '../../../components/loading';

/**
 * Notification component used in the block editor sidebar.
 *
 * @param {Object} props
 * @param {ReactNode} props.action Call to action element.
 * @param {ReactNode} props.icon Status icon element.
 * @param {boolean} props.isLoading Flag indicating if it's a loading message.
 * @param {string} props.message Message text.
 */
export function SidebarNotification( {
	action,
	icon,
	isLoading = false,
	message,
} ) {
	const iconElement = isLoading ? <Loading /> : icon;

	return (
		<div className={ classnames( 'sidebar-notification', { 'is-loading': isLoading } ) }>
			{ iconElement && (
				<div className="sidebar-notification__icon">
					{ iconElement }
				</div>
			) }
			<div className="sidebar-notification__content">
				<p>
					{ message }
				</p>
				{ action && (
					<p>
						{ action }
					</p>
				) }
			</div>
		</div>
	);
}
SidebarNotification.propTypes = {
	action: PropTypes.node,
	icon: PropTypes.node,
	isLoading: PropTypes.bool,
	message: PropTypes.string.isRequired,
};

/**
 * Sidebar notifications container component.
 *
 * @param {Object} props
 * @param {Object} props.children Component children.
 * @param {boolean} props.isShady Flag indicating if the component should have a background.
 */
export function SidebarNotificationsContainer( { children, isShady } ) {
	return (
		<div className={ classnames( 'sidebar-notifications-container', { 'is-shady': isShady } ) }>
			{ children }
		</div>
	);
}
SidebarNotificationsContainer.propTypes = {
	children: PropTypes.any,
	isShady: PropTypes.bool,
};
