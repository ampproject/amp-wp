/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useMemo } from '@wordpress/element';

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
	margin: 5px 0px;
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
		actions: { loadStatuses, loadUsers },
		state: { users, statuses },
	} = useInspector();

	const {
		state: { meta: { isSaving }, story: { author, status, slug, date, excerpt, featuredMediaUrl, password }, capabilities },
		actions: { updateStory, deleteStory },
	} = useStory();

	const { postThumbnails } = useConfig();

	useEffect( () => {
		loadStatuses();
		loadUsers();
	} );

	const disabledStatuses = useMemo( () => ( status === 'future' ) ? [ 'pending' ] : [ 'future', 'pending' ], [ status ] );

	const handleChangeValue = useCallback(
		( prop ) => ( evt ) => updateStory( { properties: { [ prop ]: evt.target.value } } ),
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
				onChange={ handleChangeValue( 'status' ) }
			/> }
			{ capabilities && capabilities.hasPublishAction && status !== 'private' && <InputGroup
				label={ 'Password' }
				type={ 'password' }
				value={ password }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'password' ) }
			/> }

			<RemoveButton onClick={ handleRemoveStory } dangerouslySetInnerHTML={ { __html: 'Move to trash' } } />
			<InputGroup
				label={ 'Published date' }
				type={ 'datetime-local' }
				value={ date }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'date' ) }
			/>
			{ capabilities && capabilities.hasAssignAuthorAction && users && <SelectMenu
				label="Author"
				name="user"
				options={ users }
				value={ author }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'author' ) }
			/> }

			<InputGroup
				label={ 'Excerpt' }
				type={ 'text' }
				value={ excerpt }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'excerpt' ) }
			/>

			<InputGroup
				label={ 'Slug' }
				type={ 'text' }
				value={ slug }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'slug' ) }
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
