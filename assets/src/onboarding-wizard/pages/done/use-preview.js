/**
 * WordPress dependencies
 */
import { useContext, useMemo, useState } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import { PREVIEW_URLS } from 'amp-settings'; // From WP inline script.

/**
 * Internal dependencies
 */
import { Options } from '../../../components/options-context-provider';
import { STANDARD } from '../../../common/constants';

/**
 * Gets the title for the preview page selector.
 *
 * @param {string} type The page type.
 */
function getTitle( type ) {
	switch ( type ) {
		case 'home':
			return __( 'Homepage', 'amp' );

		case 'author':
			return __( 'Author page', 'amp' );

		case 'date':
			return __( 'Archive page', 'amp' );

		case 'search':
			return __( 'Search results', 'amp' );

		default:
			return `${ type.charAt( 0 ).toUpperCase() }${ type.slice( 1 ) }`;
	}
}

const links = Object.keys( PREVIEW_URLS ).map( ( type ) => ( {
	type,
	text: getTitle( type ),
	url: PREVIEW_URLS[ type ].url,
	ampUrl: PREVIEW_URLS[ type ].amp_url,
} ) );

export function usePreview() {
	const { editedOptions: { theme_support: themeSupport } } = useContext( Options );
	const [ isPreviewingAMP, setIsPreviewingAMP ] = useState( themeSupport !== STANDARD );
	const [ previewedPageType, setPreviewedPageType ] = useState( links[ 0 ].type );

	const toggleIsPreviewingAMP = () => setIsPreviewingAMP( ( mode ) => ! mode );
	const setActivePreviewLink = ( link ) => setPreviewedPageType( link.type );

	const previewLinks = useMemo( () => links.map( ( { url, ampUrl, type, ...rest } ) => ( {
		...rest,
		type,
		url: isPreviewingAMP ? ampUrl : url,
		isActive: type === previewedPageType,
	} ) ), [ isPreviewingAMP, previewedPageType ] );

	return {
		previewLinks,
		setActivePreviewLink,
		previewUrl: PREVIEW_URLS[ previewedPageType ][ isPreviewingAMP ? 'amp_url' : 'url' ],
		isPreviewingAMP,
		toggleIsPreviewingAMP,
	};
}
