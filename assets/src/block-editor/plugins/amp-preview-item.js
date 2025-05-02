/**
 * Internal dependencies
 */
import { AMPFilledIcon } from '../../icons';
import AmpPreviewMenuItem from '../components/preview-menu-item';

export const name = 'amp-preview-menu-item';

export const icon = (
	<AMPFilledIcon viewBox="0 0 62 62" height={18} width={18} />
);

export const onlyPaired = true;

export const render = AmpPreviewMenuItem;
