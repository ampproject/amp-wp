/**
 * WordPress dependencies
 */
import { useContext, useMemo, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Options } from '../../../components/options-context-provider';
import { SiteScan } from '../../../components/site-scan-context-provider';
import { STANDARD } from '../../../common/constants';

export function usePreview() {
	const { scannableUrls } = useContext( SiteScan );
	const { editedOptions: { theme_support: themeSupport } } = useContext( Options );

	const hasPreview = scannableUrls.length > 0;
	const [ isPreviewingAMP, setIsPreviewingAMP ] = useState( themeSupport !== STANDARD );
	const [ previewedPageType, setPreviewedPageType ] = useState( hasPreview ? scannableUrls[ 0 ].type : null );

	const toggleIsPreviewingAMP = () => setIsPreviewingAMP( ( mode ) => ! mode );
	const setActivePreviewLink = ( link ) => setPreviewedPageType( link.type );

	const previewLinks = useMemo( () => scannableUrls.map( ( { url, amp_url: ampUrl, type, label } ) => ( {
		type,
		label,
		url: isPreviewingAMP ? ampUrl : url,
		isActive: type === previewedPageType,
	} ) ), [ isPreviewingAMP, previewedPageType, scannableUrls ] );

	const previewUrl = useMemo( () => previewLinks.find( ( link ) => link.isActive )?.url, [ previewLinks ] );

	return {
		hasPreview,
		isPreviewingAMP,
		previewLinks,
		previewUrl,
		setActivePreviewLink,
		toggleIsPreviewingAMP,
	};
}
