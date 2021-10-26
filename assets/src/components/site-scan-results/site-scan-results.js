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
 * @param {Object}  props              Component props.
 * @param {Object}  props.callToAction A call to action element.
 * @param {Object}  props.children     Component children.
 * @param {number}  props.count        Incompatibilities count.
 * @param {string}  props.className    Additional class names.
 * @param {Element} props.icon         Panel icon.
 * @param {string}  props.title        Panel title.
 */
export function SiteScanResults( {
	callToAction,
	children,
	className,
	count,
	icon,
	title,
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
				{ callToAction && (
					<p className="site-scan-results__cta">
						{ callToAction }
					</p>
				) }
			</div>
		</Selectable>
	);
}

SiteScanResults.propTypes = {
	callToAction: PropTypes.element,
	children: PropTypes.any,
	className: PropTypes.string,
	count: PropTypes.number,
	icon: PropTypes.element,
	title: PropTypes.string,
};
