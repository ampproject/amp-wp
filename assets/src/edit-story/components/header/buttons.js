/**
 * External dependencies
 */
import styled from 'styled-components';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { addQueryArgs } from '@wordpress/url';
import { Spinner } from '@wordpress/components';

/**
 * Internal dependencies
 */
import { useStory } from '../../app';
import { Outline, Primary } from '../button';

const ButtonList = styled.nav`
	display: flex;
<<<<<<< HEAD
	justify-content: space-between;
	padding-top: 20px;
	padding-right: 31px;
=======
	justify-content: flex-end;
	padding: 1em;
>>>>>>> 33d5bf55b0cec0d954337dd2874c5790d5ee1c3d
	height: 100%;
`;

const List = styled.div`
	display: flex;
`;

const Space = styled.div`
	width: 6px;
`;

function PreviewButton() {
	const {
		state: { meta: { isSaving }, story: { link } },
	} = useStory();

	/**
	 * Open a preview of the story in current window.
	 */
	const openPreviewLink = () => {
		const previewLink = addQueryArgs( link, { preview: 'true' } );
		window.open( previewLink, '_blank' );
	};
	return (
		<Outline onClick={ openPreviewLink } isDisabled={ isSaving }>
			{ __( 'Preview' ) }
		</Outline>
	);
}

function Publish() {
	const {
		state: { meta: { isSaving }, story: { status } },
		actions: { saveStory },
	} = useStory();

	const text = ( status !== 'publish' ) ? __( 'Publish' ) : __( 'Update' );

	return (
		<Primary onClick={ saveStory } isDisabled={ isSaving }>
			{ text }
		</Primary>
	);
}

function Loading() {
	const {
		state: { isSaving },
	} = useStory();

	return ( isSaving ) ? <Spinner /> : <Space />;
}

function Buttons() {
	return (
		<ButtonList>
			<List>
				<Loading />
				<PreviewButton />
				<Space />
				<Publish />
				<Space />
			</List>
		</ButtonList>
	);
}
export default Buttons;

