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
import { Logo } from '../components/svg/logo';
import { UnsavedChangesWarning } from '../components/unsaved-changes-warning';
import { useWindowWidth } from '../utils/use-window-width';
import { Stepper } from './components/stepper';
import { Nav, CloseLink } from './components/nav';
import { Navigation } from './components/navigation-context-provider';

/**
 * Side effect wrapper for page component.
 *
 * @param {Object} props          Component props.
 * @param {?any}   props.children Component children.
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
 * @param {Object}  props            Component props.
 * @param {string}  props.closeLink  Link to return to previous user location.
 * @param {string}  props.finishLink Exit link.
 * @param {Element} props.appRoot    App root element.
 */
export function SetupWizard( { closeLink, finishLink, appRoot } ) {
	const { isMobile } = useWindowWidth();
	const { activePageIndex, currentPage: { title, PageComponent, showTitle }, moveBack, moveForward, pages } = useContext( Navigation );

	const PageComponentWithSideEffects = useMemo( () => () => (
		<PageComponentSideEffects>
			<PageComponent />
		</PageComponentSideEffects>
	// eslint-disable-next-line react-hooks/exhaustive-deps
	), [ PageComponent ] );

	return (
		<div className="amp-onboarding-wizard-container">
			<div className="amp-onboarding-wizard">
				<div className="amp-stepper-container">
					<div className="amp-stepper-container__header">
						{
							isMobile && (
								<CloseLink closeLink={ closeLink } />
							)
						}
						<div className="amp-onboarding-wizard__logo-container">
							<Logo />
							<h1>
								{ __( 'AMP', 'amp' ) }
							</h1>
						</div>
						<div className="amp-onboarding-wizard-plugin-name">
							{ __( 'Official AMP Plugin for WordPress', 'amp' ) }
						</div>
					</div>
					<Stepper
						activePageIndex={ activePageIndex }
						pages={ pages }
					/>
				</div>
				<div className="amp-onboarding-wizard-panel-container">
					<Panel className="amp-onboarding-wizard-panel">
						{ false !== showTitle && (
							<h1>
								{ title }
							</h1>

						) }
						<PageComponentWithSideEffects />
					</Panel>
					<Nav
						activePageIndex={ activePageIndex }
						closeLink={ closeLink }
						finishLink={ finishLink }
						moveBack={ moveBack }
						moveForward={ moveForward }
						pages={ pages }
					/>
				</div>
			</div>
			<UnsavedChangesWarning appRoot={ appRoot } />
		</div>
	);
}

SetupWizard.propTypes = {
	closeLink: PropTypes.string.isRequired,
	finishLink: PropTypes.string.isRequired,
	appRoot: PropTypes.instanceOf( global.Element ),
};
