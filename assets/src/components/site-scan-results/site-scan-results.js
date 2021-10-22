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
import { Selectable } from '../selectable';

/**
 * Renders a panel with a site scan results.
 *
 * @param {Object}  props                   Component props.
 * @param {Object}  props.children          Component children.
 * @param {number}  props.count             Issues count.
 * @param {string}  props.className         Additional class names.
 * @param {Element} props.icon              Panel icon.
 * @param {string}  props.title             Panel title.
 * @param {string}  props.validatedUrlsLink URL to the Validated URLs page.
 */
export function SiteScanResults( {
	children,
	count,
	className,
	icon,
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
				{ children }
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
	children: PropTypes.any,
	count: PropTypes.number,
	className: PropTypes.string,
	icon: PropTypes.element,
	title: PropTypes.string,
	validatedUrlsLink: PropTypes.string,
};
