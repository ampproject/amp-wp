/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';
import AMPValidationErrorsKeptIcon from '../../../../images/amp-validation-errors-kept.svg';
import { Loading } from '../../../components/loading';

/**
 * Notification component used in the block editor sidebar.
 *
 * @param {Object} props
 * @param {string|Object} props.action Call to action element.
 * @param {string|Object} props.icon Status icon element.
 * @param {boolean} props.isError Flag indicating if it's an error message.
 * @param {boolean} props.isLoading Flag indicating if it's a loading message.
 * @param {string} props.message Message text.
 */
export function SidebarNotification( {
	action,
	icon,
	isError = false,
	isLoading = false,
	message,
} ) {
	let iconElement;

	if ( isLoading ) {
		iconElement = <Loading />;
	} else if ( isError ) {
		iconElement = <AMPValidationErrorsKeptIcon />;
	} else {
		iconElement = icon;
	}

	return (
		<div className={
			classnames( 'sidebar-notification', {
				'is-error': isError,
				'is-loading': isLoading,
			} )
		}>
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
	action: PropTypes.oneOfType( [ PropTypes.element, PropTypes.node ] ),
	icon: PropTypes.oneOfType( [ PropTypes.element, PropTypes.node ] ),
	isError: PropTypes.bool,
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
