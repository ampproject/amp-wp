/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';
import { cleanForSlug } from '@wordpress/editor';

/**
 * Internal dependencies
 */
import { useStory } from '../../app/story';
import { useConfig } from '../../app/config';

const Input = styled.input`
	color: ${ ( { theme } ) => `${ theme.colors.fg.v1 } !important` };
	margin: 0;
	font-family: ${ ( { theme } ) => theme.fonts.body1.family };
	font-size: ${ ( { theme } ) => theme.fonts.body1.size };
	line-height: ${ ( { theme } ) => theme.fonts.body1.lineHeight };
	letter-spacing: ${ ( { theme } ) => theme.fonts.body1.letterSpacing };
	background: none !important;
	border: 0px none !important;
	text-align: center;
`;

function Title() {
	const {
		state: { story: { title, slug } },
		actions: { updateStory },
	} = useStory();

	const { storyId } = useConfig();

	const handleChange = useCallback(
		( evt ) => updateStory( { properties: { title: evt.target.value } } ),
		[ updateStory ],
	);

	const handleBlur = useCallback(
		() => {
			if ( ! slug || slug === storyId ) {
				const cleanSlug = encodeURIComponent( cleanForSlug( titleFormatted( title ) ) ) || storyId;
				updateStory( { properties: { slug: cleanSlug } } );
			}
		}, [ slug, storyId, title, titleFormatted, updateStory ],
	);

	// TODO Make sure that Auto Draft checks translations.
	const titleFormatted = useCallback(
		( rawTitle ) => {
			return ( rawTitle === 'Auto Draft' ) ? '' : rawTitle;
		}, [],
	);

	if ( typeof title !== 'string' ) {
		return null;
	}

	return (
		<Input
			value={ titleFormatted( title ) }
			type={ 'text' }
			onBlur={ handleBlur }
			onChange={ handleChange }
			placeholder={ 'Add title' }
		/>
	);
}

export default Title;

