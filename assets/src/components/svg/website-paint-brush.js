/**
 * External dependencies
 */
import PropTypes from 'prop-types';

const INTRINSIC_ICON_WIDTH = 58;
const INTRINSIC_ICON_HEIGHT = 38;
const INTRINSIC_STROKE_WIDTH = 2;

export function IconWebsitePaintBrush( { width = INTRINSIC_ICON_WIDTH, ...props } ) {
	const strokeWidth = INTRINSIC_STROKE_WIDTH * ( INTRINSIC_ICON_WIDTH / width );

	return (
		<svg viewBox={ `0 0 ${ INTRINSIC_ICON_WIDTH } ${ INTRINSIC_ICON_HEIGHT }` } width={ width } fill="none" xmlns="http://www.w3.org/2000/svg" { ...props }>
			<path d="m5.764 8.012-.031 7.59A1.89 1.89 0 0 0 7.615 17.5l24.96.105a1.89 1.89 0 0 0 1.897-1.882l.032-7.59a1.89 1.89 0 0 0-1.882-1.898L7.662 6.13a1.89 1.89 0 0 0-1.898 1.882ZM20.22 29.17a1.9 1.9 0 0 1-1.88-1.9v-3.13a1.9 1.9 0 0 1 1.9-1.88l12.38.07a1.91 1.91 0 0 1 1.88 1.9v3.13a1.869 1.869 0 0 1-1.89 1.87l-12.39-.06ZM7.61 29.18a1.92 1.92 0 0 1-1.88-1.91v-3.13a1.87 1.87 0 0 1 1.89-1.86h3.57a1.92 1.92 0 0 1 1.89 1.91v3.13a1.862 1.862 0 0 1-1.89 1.86H7.61Z" stroke="#005AF0" strokeWidth="2.08" strokeLinecap="round" strokeLinejoin="round" />
			<path d="M11.71 12.77a1.69 1.69 0 1 0 0-3.38 1.69 1.69 0 0 0 0 3.38Z" fill="#005AF0" />
			<path d="M13.65 17.21 25.39 9.9l6.41 7.69" stroke="#005AF0" strokeWidth={ strokeWidth } strokeLinecap="round" strokeLinejoin="round" />
			<path d="M2.4 1h35.43a1.4 1.4 0 0 1 1.4 1.4v32.07H1V2.4A1.4 1.4 0 0 1 2.4 1v0Z" stroke="#005AF0" strokeWidth={ strokeWidth } strokeLinecap="round" strokeLinejoin="round" />
			<path d="m48.87 29.137-14-14-7.283 7.283 14 14 7.283-7.283Z" fill="#fff" stroke="#005AF0" strokeWidth={ strokeWidth } strokeLinecap="round" strokeLinejoin="round" />
			<path d="m41.797 12.362 9.836 9.836a2.94 2.94 0 0 1 0 4.158l-2.78 2.779L34.86 15.14l2.779-2.779a2.94 2.94 0 0 1 4.158 0ZM55.774 8.23a4.17 4.17 0 0 1 0 5.897l-6.102 6.103-5.897-5.898 6.102-6.102a4.17 4.17 0 0 1 5.897 0Z" fill="#fff" stroke="#005AF0" strokeWidth={ strokeWidth } strokeLinecap="round" strokeLinejoin="round" />
			<path d="m32.55 26.42 6.55-6.56M37.59 31.46l6.55-6.56" stroke="#005AF0" strokeWidth={ strokeWidth } strokeLinecap="round" strokeLinejoin="round" />
		</svg>
	);
}
IconWebsitePaintBrush.propTypes = {
	width: PropTypes.number,
};
