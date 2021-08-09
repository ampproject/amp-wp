/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import classnames from 'classnames';

/**
 * Internal dependencies
 */
import './style.css';

export const NOTICE_TYPE_ERROR = 'error';
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
function getNoticeIcon( type ) {
	let Icon;

	switch ( type ) {
		case NOTICE_TYPE_SUCCESS:
			Icon = () => (
				<svg width="35" height="36" viewBox="0 0 35 36" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="1.90112" y="1.98828" width="32.0691" height="32.0691" rx="16.0345" stroke="#00A02F" strokeWidth="2" />
					<mask id="mask-notice-success" mask-type="alpha" maskUnits="userSpaceOnUse" x="10" y="12" width="16" height="12">
						<path d="M15.0921 21.461L11.3924 17.7613L10.1326 19.0122L15.0921 23.9718L25.7387 13.3252L24.4877 12.0742L15.0921 21.461Z" fill="white" />
					</mask>
					<g mask="url(#mask-notice-success)">
						<rect x="7.28906" y="7.375" width="21.2932" height="21.2932" fill="#00A02F" />
					</g>
				</svg>

			);
			break;

		case NOTICE_TYPE_ERROR:
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
				<svg width="35" height="35" viewBox="0 0 35 35" fill="none" xmlns="http://www.w3.org/2000/svg">
					<rect x="1.66626" y="1.76172" width="31.4597" height="31.4597" rx="15.7299" stroke="currentColor" strokeWidth="2" />
					<path d="M15.3048 11.3424C15.3048 10.1875 16.2412 9.25113 17.3961 9.25113C18.5509 9.25113 19.4873 10.1875 19.4873 11.3424C19.4873 12.4972 18.5509 13.4336 17.3961 13.4336C16.2412 13.4336 15.3048 12.4972 15.3048 11.3424Z" fill="currentColor" />
					<line x1="17.4184" y1="25.3594" x2="17.4184" y2="15.4184" stroke="currentColor" strokeWidth="2" />
				</svg>

			);
	}

	return <Icon />;
}

/**
 * A warning, info, or success notice similar to those used in WP core.
 *
 * @param {Object} props           Component props.
 * @param {string} props.children  Notice content, not including the icon.
 * @param {string} props.className Optional extra class names.
 * @param {string} props.size      The notice size.
 * @param {string} props.type      The notice type.
 */
export function AMPNotice( { children, className, size = NOTICE_SIZE_LARGE, type = NOTICE_TYPE_INFO, ...props } ) {
	const noticeIcon = getNoticeIcon( type );

	return (
		<div
			className={
				classnames(
					className,
					'amp-notice',
					`amp-notice--${ type }`,
					`amp-notice--${ size }`,
				) }
			{ ...props }
		>
			<div className="amp-notice__icon">
				{ noticeIcon }
			</div>
			<div className="amp-notice__body">
				{ children }
			</div>
		</div>
	);
}

AMPNotice.propTypes = {
	children: PropTypes.node,
	className: PropTypes.string,
	size: PropTypes.oneOf( [ NOTICE_SIZE_LARGE, NOTICE_SIZE_SMALL ] ),
	type: PropTypes.oneOf( [ NOTICE_TYPE_INFO, NOTICE_TYPE_SUCCESS, NOTICE_TYPE_ERROR, NOTICE_TYPE_WARNING ] ),
};
