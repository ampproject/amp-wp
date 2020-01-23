/**
 * External dependencies
 */
import { css } from 'styled-components';

const PointerEventsCss = css`
  ${ ( { pointerEvents } ) => {
		if ( typeof pointerEvents === 'boolean' ) {
			return `pointer-events: ${ pointerEvents ? 'initial' : 'none' };`;
		}
		if ( typeof pointerEvents === 'string' && pointerEvents ) {
			return `pointer-events: ${ pointerEvents };`;
		}
		return '';
	} }
`;

export default PointerEventsCss;
