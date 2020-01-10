/**
 * Internal dependencies
 */
import { coreDeprecations } from '../deprecations/core-blocks';
import isMovableBlock from './isMovableBlock';

const deprecateCoreBlocks = ( settings, name ) => {
	if ( ! isMovableBlock( name ) ) {
		return settings;
	}

	let deprecated = settings.deprecated ? settings.deprecated : [];
	const blockDeprecation = coreDeprecations[ name ] || undefined;
	if ( blockDeprecation ) {
		deprecated = [ ...deprecated, ...blockDeprecation ];
		return {
			...settings,
			deprecated,
		};
	}

	return settings;
};

export default deprecateCoreBlocks;
