/**
 * Internal dependencies
 */
import { ALLOWED_MOVABLE_BLOCKS } from '../constants';

const isMovableBlock = ( name ) => ALLOWED_MOVABLE_BLOCKS.includes( name );

export default isMovableBlock;
