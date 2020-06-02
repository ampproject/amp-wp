/**
 * WordPress dependencies
 */
import { useState, useMemo, useContext } from '@wordpress/element';
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

/**
 * State wrapper for the page component.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 * @param {string} props.exitLink Exit link.
 */
function Page( { children, exitLink } ) {
	const { fetchingOptions, fetchOptionsError } = useContext( Options );

	if ( fetchOptionsError ) {
		return (
			<p>
				{ fetchOptionsError.message || __( 'There was an error loading the setup wizard.', 'amp' ) }
				{ ' ' }
				<a href={ exitLink }>
					{ __( 'Return to AMP Settings.', 'amp' ) }
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
export function SetupWizard( { exitLink, pages } ) {
	const [ activePageIndex, setActivePageIndex ] = useState( 0 );
	const { fetchOptionsError } = useContext( Options );

	const { title, PageComponent } = useMemo( () => pages[ activePageIndex ], [ activePageIndex, pages ] );

	return (
		<div className="amp-setup-container">
			<div className="amp-setup">
				<div className="amp-stepper-container">
					<WordmarkLogo />
					<div className="amp-setup-plugin-name">
						{ __( 'Official AMP Plugin for WordPress', 'amp' ) }
					</div>
					<Stepper
						activePageIndex={ activePageIndex }
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
						activePageIndex={ activePageIndex }
						exitLink={ exitLink }
						pages={ pages }
						setActivePageIndex={ setActivePageIndex }
					/>
				</div>
			</div>
			<WizardUnsavedChangesWarning />
		</div>
	);
}

SetupWizard.propTypes = {
	exitLink: PropTypes.string.isRequired,
	pages: PropTypes.arrayOf(
		PropTypes.shape( {
			title: PropTypes.string.isRequired,
		} ),
	).isRequired,
};
