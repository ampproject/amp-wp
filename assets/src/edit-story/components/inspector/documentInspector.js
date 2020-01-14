/**
 * WordPress dependencies
 */
import { useCallback, useEffect } from '@wordpress/element';

/**
 * External dependencies
 */
import styled, { css } from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../app/story';
import { useConfig } from '../../app/config';
import UploadButton from '../uploadButton';
import useInspector from './useInspector';
import { SelectMenu, InputGroup } from './shared';

const ButtonCSS = css`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	font-size: 14px;
	width: 100%;
	padding: 15px;
	background: none;
	margin: 0px 0px 5px;
`;
const Img = styled.img`
	width: 100%;
	max-height: 300px
`;

const Group = styled.div`
	border-color: ${ ( { theme } ) => theme.colors.mg.v1 };
	display: block;
	align-items: center;
	margin: 15px 0px;
`;

const RemoveButton = styled.button`
	${ ButtonCSS }
`;

function DocumentInspector() {
	const {
		actions: { loadStatuses, loadUsers, setDisabledStatuses },
		state: { users, statuses, disabledStatuses },
	} = useInspector();

	const {
		state: { meta: { isSaving }, story: { author, status, slug, date, excerpt, featuredMediaUrl }, capabilities },
		actions: { updateStory, deleteStory },
	} = useStory();

	const { postThumbnails } = useConfig();

	useEffect( () => {
		loadStatuses();
		loadUsers();
	} );

	useEffect( () => {
		const states = ( status === 'future' ) ? [ 'pending' ] : [ 'future', 'pending' ];
		setDisabledStatuses( states );
	}, [ status, setDisabledStatuses ] );

	const handleChangeStatus = useCallback(
		( evt ) => 	updateStory( { properties: { status: evt.target.value } } ),
		[ updateStory ],
	);

	const handleChangeAuthor = useCallback(
		( evt ) => updateStory( { properties: { author: evt.target.value } } ),
		[ updateStory ],
	);

	const handleChangeDate = useCallback(
		( value, evt ) => updateStory( { properties: { date: evt.target.value } } ),
		[ updateStory ],
	);

	const handleChangeExcerpt = useCallback(
		( value, evt ) => updateStory( { properties: { excerpt: evt.target.value } } ),
		[ updateStory ],
	);

	const handleChangeSlug = useCallback(
		( value, evt ) => updateStory( { properties: { slug: evt.target.value } } ),
		[ updateStory ],
	);

	const handleChangeImage = useCallback(
		( image ) => updateStory( { properties: { featuredMedia: image.id, featuredMediaUrl: ( image.sizes && image.sizes.medium ) ? image.sizes.medium.url : image.url } } ),
		[ updateStory ],
	);

	const handleRemoveImage = useCallback(
		( evt ) => {
			updateStory( { properties: { featuredMedia: 0, featuredMediaUrl: '' } } );
			evt.preventDefault();
		},	[ updateStory ],
	);

	const handleRemoveStory = useCallback(
		( evt ) => {
			deleteStory();
			evt.preventDefault();
		},	[ deleteStory ],
	);

	return (
		<>
			<h2>
				{ 'Document' }
			</h2>
			{ capabilities && capabilities.hasPublishAction && statuses && <SelectMenu
				label="Status"
				name="status"
				options={ statuses }
				disabled={ isSaving }
				disabledOptions={ disabledStatuses }
				value={ status }
				onChange={ handleChangeStatus }
			/> }
			<RemoveButton onClick={ handleRemoveStory } dangerouslySetInnerHTML={ { __html: 'Move to trash' } } />
			<InputGroup
				label={ 'Published date' }
				type={ 'datetime-local' }
				value={ date }
				disabled={ isSaving }
				onChange={ handleChangeDate }
			/>
			{ capabilities && capabilities.hasAssignAuthorAction && users && <SelectMenu
				label="Author"
				name="user"
				options={ users }
				value={ author }
				disabled={ isSaving }
				onChange={ handleChangeAuthor }
			/> }

			<InputGroup
				label={ 'Excerpt' }
				type={ 'text' }
				value={ excerpt }
				disabled={ isSaving }
				onChange={ handleChangeExcerpt }
			/>

			<InputGroup
				label={ 'Slug' }
				type={ 'text' }
				value={ slug }
				disabled={ isSaving }
				onChange={ handleChangeSlug }
			/>
			<Group>
				{ featuredMediaUrl && <Img src={ featuredMediaUrl } /> }
				{ featuredMediaUrl && <RemoveButton onClick={ handleRemoveImage } dangerouslySetInnerHTML={ { __html: 'Remove image' } } /> }

				{ postThumbnails && <UploadButton
					onSelect={ handleChangeImage }
					title={ 'Select as featured image' }
					type={ 'image' }
					buttonInsertText={ 'Set as featured image' }
					buttonText={ featuredMediaUrl ? 'Replace image' : 'Set featured image' }
					buttonCSS={ ButtonCSS }
				/> }
			</Group>

		</>
	);
}

export default DocumentInspector;
