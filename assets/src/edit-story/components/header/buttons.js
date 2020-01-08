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
import { useHistory, useStory } from '../../app';
import { Outline, Primary, Undo, Redo } from '../button';

const ButtonList = styled.nav`
	background-color: ${ ( { theme } ) => theme.colors.bg.v3 };
	display: flex;
	justify-content: space-between;
	padding: 1em;
	height: 100%;
`;

const List = styled.div`
	display: flex;
`;

const Space = styled.div`
	width: 1em;
`;

function Undoer() {
	const { state: { canUndo }, actions: { undo } } = useHistory();
	return (
		<Undo onClick={ undo } isDisabled={ ! canUndo } />
	);
}

function Redoer() {
	const { state: { canRedo }, actions: { redo } } = useHistory();
	return (
		<Redo onClick={ redo } isDisabled={ ! canRedo } />
	);
}

function PreviewButton() {
	const {
		state: { isSaving, link },
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
		state: { isSaving, story: { status } },
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
				<Undoer />
				<Space />
				<Redoer />
			</List>
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

