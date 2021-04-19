/**
 * Internal dependencies
 */
import { SidebarNotificationsContainer } from '../sidebar-notification';
import AMPRevalidateNotification from './revalidate-notification';
import AMPValidationStatusNotification from './status-notification';

export default function AMPValidationStatus() {
	return (
		<SidebarNotificationsContainer isShady={ true }>
			<AMPRevalidateNotification />
			<AMPValidationStatusNotification />
		</SidebarNotificationsContainer>
	);
}
