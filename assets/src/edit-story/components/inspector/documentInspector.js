/**
 * WordPress dependencies
 */
import { useCallback, useEffect, useMemo } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * External dependencies
 */
import styled, { css } from 'styled-components';

/**
 * Internal dependencies
 */
import { useStory } from '../../app/story';
import { useConfig } from '../../app/config';
import { SimplePanel } from '../../panels/panel';
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

	const allStatuses = useMemo( () => {
		const disabledStatuses = ( status === 'future' ) ? [ 'pending' ] : [ 'future', 'pending' ];
		return statuses.filter( ( { value } ) => ! disabledStatuses.includes( value ) );
	}, [ status, statuses ] );

	const handleChangeValue = useCallback(
		( prop ) => ( value ) => updateStory( { properties: { [ prop ]: value } } ),
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
		<SimplePanel title={ __( 'Document', 'amp' ) }>
			{ capabilities && capabilities.hasPublishAction && statuses && <SelectMenu
				label={ __( 'Status', 'amp' ) }
				name="status"
				options={ allStatuses }
				disabled={ isSaving }
				value={ status }
				onChange={ handleChangeValue( 'status' ) }
			/> }
			{ capabilities && capabilities.hasPublishAction && status !== 'private' && <InputGroup
				label={ __( 'Password', 'amp' ) }
				type={ 'password' }
				value={ password }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'password' ) }
			/> }

			<RemoveButton onClick={ handleRemoveStory } dangerouslySetInnerHTML={ { __html: 'Move to trash' } } />
			<InputGroup
				label={ __( 'Published date', 'amp' ) }
				type={ 'datetime-local' }
				value={ date }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'date' ) }
			/>
			{ capabilities && capabilities.hasAssignAuthorAction && users && <SelectMenu
				label={ __( 'Author', 'amp' ) }
				name="user"
				options={ users }
				value={ author }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'author' ) }
			/> }

			<InputGroup
				label={ __( 'Excerpt', 'amp' ) }
				type={ 'text' }
				value={ excerpt }
				disabled={ isSaving }
				onChange={ handleChangeValue( 'excerpt' ) }
			/>

			<InputGroup
				label={ __( 'Slug', 'amp' ) }
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
					title={ __( 'Select as featured image', 'amp' ) }
					type={ 'image' }
					buttonInsertText={ __( 'Set as featured image', 'amp' ) }
					buttonText={ featuredMediaUrl ? __( 'Replace image', 'amp' ) : __( 'Set featured image', 'amp' ) }
					buttonCSS={ ButtonCSS }
				/> }
			</Group>
		</SimplePanel>
	);
}

export default DocumentInspector;
