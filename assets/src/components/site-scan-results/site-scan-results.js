/**
 * WordPress dependencies
 */
import { ExternalLink, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { Loading } from '../loading';
import { Selectable } from '../selectable';
import { SourcesList } from '../../onboarding-wizard/pages/site-scan/sources-list';

/**
 * Renders a panel with a site scan results.
 *
 * @param {Object}  props                   Component props.
 * @param {Element} props.count             Issues count.
 * @param {Element} props.icon              Panel icon.
 * @param {Array}   props.sources           Array of issues sources data.
 * @param {string}  props.title             Panel title.
 * @param {string}  props.validatedUrlsLink URL to the Validated URLs page.
 */
export function SiteScanResults( {
	count,
	icon,
	sources,
	title,
	validatedUrlsLink,
} ) {
	return (
		<Selectable className="site-scan__section site-scan__section--compact">
			<div className="site-scan__header">
				{ icon }
				<p
					className="site-scan__heading"
					data-badge-content={ count }
				>
					{ title }
					<VisuallyHidden as="span">
						{ `(${ count })` }
					</VisuallyHidden>
				</p>
			</div>
			<div className="site-scan__content">
				{ sources.length === 0
					? <Loading />
					: <SourcesList sources={ sources } /> }
				<p className="site-scan__cta">
					<ExternalLink href={ validatedUrlsLink }>
						{ __( 'AMP Validated URLs page', 'amp' ) }
					</ExternalLink>
				</p>
			</div>
		</Selectable>
	);
}

SiteScanResults.propTypes = {
	count: PropTypes.number,
	icon: PropTypes.element,
	sources: PropTypes.array,
	title: PropTypes.string,
	validatedUrlsLink: PropTypes.string,
};
