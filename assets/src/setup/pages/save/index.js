/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { useContext, useEffect } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Options } from '../../components/options-context-provider';
import { Loading } from '../../components/loading';

/**
 * Final screen, where data is saved.
 */
export function Save() {
	const { hasSaved, saveOptions, saveOptionsError, savingOptions } = useContext( Options );

	useEffect( () => {
		if ( ! hasSaved && ! savingOptions ) {
			saveOptions();
		}
	}, [ hasSaved, saveOptions, savingOptions ] );

	if ( saveOptionsError ) {
		return (
			<p>
				{ __( 'There was an error saving options.', 'amp' ) }
			</p>
		);
	}

	if ( savingOptions ) {
		return <Loading />;
	}

	if ( ! hasSaved ) {
		return null;
	}

	return (
		<div>
			{ __( 'Options saved', 'amp' ) }
		</div>
	);
}
