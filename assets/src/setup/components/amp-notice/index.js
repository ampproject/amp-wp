/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import './style.css';

/**
 * WordPress dependencies
 */
import { useMemo } from '@wordpress/element';

export const NOTICE_TYPE_WARNING = 'warning';
export const NOTICE_TYPE_INFO = 'info';
export const NOTICE_TYPE_SUCCESS = 'success';

export const NOTICE_SIZE_SMALL = 'small';
export const NOTICE_SIZE_LARGE = 'large';

/**
 * Gets a default icon for a type of notice.
 *
 * @param {string} type Notice type.
 */
function getDefaultNoticeIcon( type ) {
	let Icon;

	switch ( type ) {
		case NOTICE_TYPE_SUCCESS:
			Icon = () => (
				<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24">
					<path fill="#00A02F" d="M12 2c5.514 0 10 4.486 10 10s-4.486 10-10 10-10-4.486-10-10 4.486-10 10-10zm0-2c-6.627 0-12 5.373-12 12s5.373 12 12 12 12-5.373 12-12-5.373-12-12-12zm4.393 7.5l-5.643 5.784-2.644-2.506-1.856 1.858 4.5 4.364 7.5-7.643-1.857-1.857z" />
				</svg>
			);
			break;

		case NOTICE_TYPE_WARNING:
			Icon = () => (
				<svg width="44" height="44" viewBox="0 0 44 44" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M8.18125 16.1001L16.4324 7.84902L28.1012 7.84902L36.3523 16.1001L36.3523 27.769L28.1012 36.0201L16.4324 36.0201L8.18125 27.769L8.18125 16.1001Z" stroke="#EF0000" strokeWidth="2" />
					<path d="M24.2671 27.4609C24.2671 28.5654 23.3716 29.4609 22.2671 29.4609C21.1626 29.4609 20.2671 28.5654 20.2671 27.4609C20.2671 26.3564 21.1626 25.4609 22.2671 25.4609C23.3716 25.4609 24.2671 26.3564 24.2671 27.4609Z" fill="#EF0000" />
					<line x1="22.2891" y1="14.0586" x2="22.2891" y2="23.5659" stroke="#EF0000" strokeWidth="2" />
				</svg>

			);
			break;

		default:
			Icon = () => (
				<svg width="31" height="33" viewBox="0 0 31 33" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="1.2666" y="1.28516" width="27.7439" height="30" rx="13.8719" stroke="#285BE7" strokeWidth="2" />
					<path d="M13.2796 10.4062C13.2796 9.30176 14.1119 8.40625 15.1386 8.40625C16.1652 8.40625 16.9976 9.30176 16.9976 10.4062C16.9976 11.5107 16.1652 12.4062 15.1386 12.4062C14.1119 12.4062 13.2796 11.5107 13.2796 10.4062Z" fill="#285BE7" />
					<line x1="15.0474" y1="23.8086" x2="15.0474" y2="14.3013" stroke="#285BE7" strokeWidth="2" />
				</svg>
			);
	}

	return <Icon />;
}

/**
 * A warning, info, or success notice similar to those used in WP core.
 *
 * @param {Object} props Component props.
 * @param {string} props.children Notice content, not including the icon.
 * @param {string} props.className Optional extra class names.
 * @param {?string|Object} props.icon An icon to render in the notice. If not supplied, a default will be used.
 * @param {string} props.size The notice size.
 * @param {string} props.type The notice type.
 */
export function AMPNotice( { children, className, icon, size, type } ) {
	const noticeIcon = useMemo( () => icon ? icon : getDefaultNoticeIcon( type ), [ icon, type ] );

	const classNames = [
		className ? className : '',
		'amp-notice',
		`amp-notice--${ type }`,
		`amp-notice--${ size }`,
	].filter( ( item ) => item );

	return (
		<div className={ classNames.join( ' ' ) }>
			<div className="amp-notice__icon">
				{ noticeIcon }
			</div>
			<div>
				{ children }
			</div>
		</div>
	);
}

AMPNotice.propTypes = {
	children: PropTypes.node,
	className: PropTypes.string,
	icon: PropTypes.node,
	size: PropTypes.oneOf( [ NOTICE_SIZE_LARGE, NOTICE_SIZE_SMALL ] ),
	type: PropTypes.oneOf( [ NOTICE_TYPE_INFO, NOTICE_TYPE_SUCCESS, NOTICE_TYPE_WARNING ] ).isRequired,
};
