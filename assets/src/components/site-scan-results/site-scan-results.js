/**
 * WordPress dependencies
 */
import { ExternalLink, VisuallyHidden } from '@wordpress/components';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import classnames from 'classnames';
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.scss';
import { Loading } from '../loading';
import { Selectable } from '../selectable';
import { SourcesList } from './sources-list';

/**
 * Renders a panel with a site scan results.
 *
 * @param {Object}  props                   Component props.
 * @param {number}  props.count             Issues count.
 * @param {string}  props.className         Additional class names.
 * @param {Element} props.icon              Panel icon.
 * @param {Array}   props.sources           Array of issues sources data.
 * @param {string}  props.title             Panel title.
 * @param {string}  props.validatedUrlsLink URL to the Validated URLs page.
 */
export function SiteScanResults( {
	count,
	className,
	icon,
	sources,
	title,
	validatedUrlsLink,
} ) {
	return (
		<Selectable className={ classnames( 'site-scan-results', className ) }>
			<div className="site-scan-results__header">
				{ icon }
				<p
					className="site-scan-results__heading"
					data-badge-content={ count }
				>
					{ title }
					<VisuallyHidden as="span">
						{ `(${ count })` }
					</VisuallyHidden>
				</p>
			</div>
			<div className="site-scan-results__content">
				{ sources.length === 0
					? <Loading />
					: <SourcesList sources={ sources } /> }
				{ validatedUrlsLink && (
					<p className="site-scan-results__cta">
						<ExternalLink href={ validatedUrlsLink }>
							{ __( 'AMP Validated URLs page', 'amp' ) }
						</ExternalLink>
					</p>
				) }
			</div>
		</Selectable>
	);
}

SiteScanResults.propTypes = {
	count: PropTypes.number,
	className: PropTypes.string,
	icon: PropTypes.element,
	sources: PropTypes.array,
	title: PropTypes.string,
	validatedUrlsLink: PropTypes.string,
};
