/**
 * WordPress dependencies
 */
import { useContext } from '@wordpress/element';
import { __ } from '@wordpress/i18n';
import { Panel } from '@wordpress/components';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { Stepper } from './components/stepper';
import { WordmarkLogo } from './components/svg/wordmark-logo';
import { Nav } from './components/nav';
import { Options } from './components/options-context-provider';
import { Loading } from './components/loading';
import { WizardUnsavedChangesWarning } from './components/unsaved-changes-warning';
import { Navigation } from './components/navigation-context-provider';

/**
 * State wrapper for the page component.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 * @param {string} props.string Exit link.
 */
function Page( { children, exitLink } ) {
	const { fetchingOptions, fetchOptionsError } = useContext( Options );

	if ( fetchOptionsError ) {
		return (
			<p>
				{ fetchOptionsError.message || __( 'There was an error loading the setup wizard.', 'amp' ) }
				{ ' ' }
				<a href={ exitLink }>
					{ __( 'Return to AMP for WordPress options.', 'amp' ) }
				</a>
			</p>
		);
	}

	if ( fetchingOptions ) {
		return <Loading />;
	}

	return children;
}

/**
 * Setup wizard root component.
 *
 * @param {Object} props Component props.
 * @param {Array} props.pages List of page configuration objects.
 */
export function SetupWizard( { exitLink } ) {
	const { currentPage: { title, PageComponent }, goBack, goForward, page, pages } = useContext( Navigation );
	const { fetchOptionsError } = useContext( Options );

	return (
		<div className="amp-setup-container">
			<div className="amp-setup">
				<div className="amp-stepper-container">
					<WordmarkLogo />
					<div className="amp-setup-plugin-name">
						{ 'Official AMP Plugin for WordPress' /* Untranslatable, as it's the plugin name. */ }
					</div>
					<Stepper
						activePageIndex={ page }
						pages={ pages }
					/>
				</div>
				<div className="amp-setup-panel-container">
					<Panel className="amp-setup-panel">
						<h1>
							{ ! fetchOptionsError ? title : __( 'Error', 'amp' ) }
						</h1>
						<Page exitLink={ exitLink }>
							<PageComponent />
						</Page>
					</Panel>
					<Nav
						activePageIndex={ page }
						exitLink={ exitLink }
						goBack={ goBack }
						goForward={ goForward }
						pages={ pages }
					/>
				</div>
			</div>
			<WizardUnsavedChangesWarning />
		</div>
	);
}

SetupWizard.propTypes = {
	exitLink: PropTypes.string.isRequired,

};
