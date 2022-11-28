/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import { AMPSettingToggle } from '../amp-setting-toggle';
import { User } from '../user-context-provider';
import { Loading } from '../loading';

export function DevToolsToggle() {
	const {
		developerToolsOption,
		fetchingUser,
		setDeveloperToolsOption,
	} = useContext( User );

	if ( fetchingUser ) {
		return <Loading />;
	}

	return (
		<AMPSettingToggle
			checked={ true === developerToolsOption }
			title={ __( 'Enable Developer Tools', 'amp' ) }
			onChange={ () => {
				setDeveloperToolsOption( ! developerToolsOption );
			} }
		/>
	);
}
