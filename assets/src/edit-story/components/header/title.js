/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { useCallback } from '@wordpress/element';

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
		state: { story: { title, status } },
		actions: { updateStory },
	} = useStory();

	const handleChange = useCallback(
		( evt ) => updateStory( { properties: { title: evt.target.value } } ),
		[ updateStory ],
	);

	if ( typeof title !== 'string' ) {
		return null;
	}

	// TODO Make sure that Auto Draft checks translations.
	const titleFormatted = ( status === 'auto-draft' && title === 'Auto Draft' ) ? '' : title;

	return (
		<Input
			value={ titleFormatted }
			type={ 'text' }
			onChange={ handleChange }
			placeholder={ 'Add title' }
		/>
	);
}

export default Title;

