/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import AMPToolbarIcon from '../../images/amp-icon-toolbar.svg';
import AMPToolbarIconBroken from '../../images/amp-toolbar-icon-broken.svg';
import AMPNewTabIcon from '../../images/amp-new-tab-icon.svg';

/**
 * Plugin icon.
 *
 * @param {Object} props
 * @param {boolean} props.hasBadge Whether the icon is showing a number.
 */
function IconSVG( { hasBadge } ) {
	return (
		<span className={ `amp-toolbar-icon${ hasBadge ? ' amp-toolbar-icon--has-badge' : '' }` }>
			<AMPToolbarIcon />
		</span>
	);
}
IconSVG.propTypes = {
	hasBadge: PropTypes.bool.isRequired,
};

/**
 * Plugin icon when AMP is broken at the URL.
 *
 * @param {Object} props
 * @param {boolean} props.hasBadge Whether the icon is showing a number.
 */
export function BrokenIconSVG( { hasBadge } ) {
	return (
		<span className={ `amp-toolbar-broken-icon${ hasBadge ? ' amp-toolbar-broken-icon--has-badge' : '' }` } >
			<AMPToolbarIconBroken />
		</span>
	);
}
BrokenIconSVG.propTypes = {
	hasBadge: PropTypes.bool.isRequired,
};

/**
 * The icon to display in the editor toolbar to toggle the editor sidebar.
 *
 * @param {Object} props
 * @param {boolean} props.broken Whether AMP is broken at the URL.
 * @param {number} props.count The number of new errors at the URL.
 */
export function ToolbarIcon( { broken = false, count } ) {
	return (
		<div className={ `amp-plugin-icon ${ broken ? 'amp-plugin-icon--broken' : '' }` }>
			{
				broken ? <BrokenIconSVG hasBadge={ Boolean( count ) } /> : <IconSVG hasBadge={ Boolean( count ) } />
			}
			{ 0 < count && (
				<div className="amp-error-count-badge">
					{ count }
				</div>
			) }
		</div>
	);
}
ToolbarIcon.propTypes = {
	broken: PropTypes.bool,
	count: PropTypes.number.isRequired,
};

/**
 * The icon to display in the editor more menu.
 */
export function MoreMenuIcon() {
	return <IconSVG hasBadge={ false } />;
}

/**
 * An icon indicating that a link opens in a new tab.
 */
export function NewTabIcon() {
	return <AMPNewTabIcon />;
}
