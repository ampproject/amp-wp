/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import AMPToolbarIcon from '../../images/amp-toolbar-icon.svg';
import AMPToolbarIconBroken from '../../images/amp-toolbar-icon-broken.svg';
import AMPNewTabIcon from '../../images/amp-new-tab-icon.svg';
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

export function MoreMenuIcon() {
	return <IconSVG hasBadge={ false } />;
}

export function NewTabIcon() {
	return <AMPNewTabIcon />;
}
