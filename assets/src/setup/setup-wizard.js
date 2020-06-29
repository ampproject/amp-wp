/**
 * WordPress dependencies
 */
import { useContext, useEffect, useMemo } from '@wordpress/element';
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
 */
function Page( { children } ) {
	const { fetchingOptions } = useContext( Options );

	if ( fetchingOptions ) {
		return <Loading />;
	}

	return children;
}

/**
 * Side effect wrapper for page component.
 *
 * @param {Object} props Component props.
 * @param {?any} props.children Component children.
 */
function PageComponentSideEffects( { children } ) {
	useEffect( () => {
		document.body.scrollTop = 0;
		document.documentElement.scrollTop = 0;
	}, [] );

	return children;
}

/**
 * Setup wizard root component.
 *
 * @param {Object} props Component props.
 * @param {Array} props.exitLink Exit link.
 */
export function SetupWizard( { exitLink } ) {
	const { activePageIndex, currentPage: { title, PageComponent, showTitle }, moveBack, moveForward, pages } = useContext( Navigation );

	const PageComponentWithSideEffects = useMemo( () => () => (
		<PageComponentSideEffects>
			<PageComponent />
		</PageComponentSideEffects>
	// eslint-disable-next-line react-hooks/exhaustive-deps
	), [ PageComponent ] );

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
						{ false !== showTitle && (
							<h1>
								{ title }
							</h1>

						) }
						<Page exitLink={ exitLink }>
							<PageComponentWithSideEffects />
						</Page>
					</Panel>
					<Nav
						activePageIndex={ activePageIndex }
						exitLink={ exitLink }
						moveBack={ moveBack }
						moveForward={ moveForward }
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
