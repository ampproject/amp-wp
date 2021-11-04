/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const INTRINSIC_ICON_WIDTH = 58;
const INTRINSIC_ICON_HEIGHT = 40;
const INTRINSIC_STROKE_WIDTH = 2;

export function IconLaptopPlug( { width = INTRINSIC_ICON_WIDTH, ...props } ) {
	const strokeWidth = INTRINSIC_STROKE_WIDTH * ( INTRINSIC_ICON_WIDTH / width );

	return (
		<svg viewBox={ `0 0 ${ INTRINSIC_ICON_WIDTH } ${ INTRINSIC_ICON_HEIGHT }` } width={ width } fill="none" xmlns="http://www.w3.org/2000/svg" { ...props }>
			<g stroke="#005AF0" strokeWidth={ strokeWidth } strokeLinecap="round" strokeLinejoin="round">
				<path d="M6.2 31.05V5.37A4.37 4.37 0 0 1 10.57 1H47.2a4.37 4.37 0 0 1 4.37 4.37v26.47M37.25 32.14v1.32H20.76v-1.32H1v3.14a3.37 3.37 0 0 0 3.36 3.37h49.28A3.36 3.36 0 0 0 57 35.28v-3.14H37.25Z" />
				<path d="M21.2 11.53h2.15v12.35H21.2a6.178 6.178 0 0 1-5.728-8.54 6.183 6.183 0 0 1 5.728-3.81v0ZM36.99 23.89h-2.15V11.54h2.15a6.181 6.181 0 0 1 6.18 6.18 6.18 6.18 0 0 1-6.18 6.17v0ZM29.15 13.4h-5.79v2.83h5.79V13.4ZM29.15 19.19h-5.79v2.83h5.79v-2.83Z" />
				<path d="M42.76 15.62h5.39v4.18h-5.39M15 19.8H9.61v-4.18H15" />
			</g>
		</svg>
	);
}
IconLaptopPlug.propTypes = {
	width: PropTypes.number,
};
