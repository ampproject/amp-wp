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

const Input = styled.input`
	color: ${ ( { theme } ) => `${ theme.colors.fg.v1 } !important` };
	margin: 0;
	font-size: 19px;
	line-height: 20px;
	background: none !important;
	border: 0px none !important;
	text-align: center;
`;

function Title() {
	const {
		state: { story: { title, status, slug } },
		actions: { updateStory },
	} = useStory();

	const handleChange = useCallback(
		( evt ) => updateStory( { properties: { title: evt.target.value } } ),
		[ updateStory ],
	);

	const handleBlur = useCallback(
		( evt ) => {
			if ( ! slug ) {
				updateStory( { properties: { slug: cleanForSlug( evt.target.value ) } } );
			}
		}, [ slug, updateStory ],

	);

	if ( typeof title !== 'string' ) {
		return null;
	}

	// TODO Make sure that Auto Draft checks translations.
	const titleFormatted = ( [ 'auto-draft', 'draft', 'pending' ].includes( status ) && title === 'Auto Draft' ) ? '' : title;

	return (
		<Input
			value={ titleFormatted }
			type={ 'text' }
			onBlur={ handleBlur }
			onChange={ handleChange }
			placeholder={ 'Add title' }
		/>
	);
}

export default Title;

