/**
 * WordPress dependencies
 */
import { useCallback, useEffect } from '@wordpress/element';
/**
 * Internal dependencies
 */
/**
 * External dependencies
 */
import styled, { css } from 'styled-components';
import { useStory } from '../../app/story';
import { useConfig } from '../../app/config';
import UploadButton from '../uploadButton';
import useInspector from './useInspector';
import { SelectMenu, InputGroup } from './shared';
/**
 * External dependencies
 */

const ButtonCSS = css`
	color: ${ ( { theme } ) => theme.colors.mg.v1 };
	font-size: 14px;
	width: 100%;
	padding: 15px;
	background: none;
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

function DocumentInspector() {
	const {
		actions: { loadStatuses, loadUsers, setDisabledStatuses },
		state: { users, statuses, disabledStatuses },
	} = useInspector();

	const {
		state: { meta: { isSaving }, story: { author, status, date, excerpt, featuredMediaUrl }, capabilities },
		actions: { updateStory },
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
		( evt ) => {
			updateStory( { properties: { status: evt.target.value } } );
		}, [ updateStory ],
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

	const handleChangeImage = useCallback(
		( image ) => updateStory( { properties: { featuredMedia: image.id, featuredMediaUrl: image.url } } ),
		[ updateStory ],
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
			<Group>
				{ featuredMediaUrl && <Img src={ featuredMediaUrl } /> }

				{ postThumbnails && <UploadButton
					onSelect={ handleChangeImage }
					title={ 'Select as featured image' }
					type={ 'image' }
					buttonInsertText={ 'Set as featured image' }
					buttonText={ featuredMediaUrl ? 'Update a featured image' : 'Upload a featured image' }
					buttonCSS={ ButtonCSS }
				/> }
			</Group>
		</>
	);
}

export default DocumentInspector;
