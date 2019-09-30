/**
 * External dependencies
 */
import { includes } from 'lodash';
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { Dropdown, IconButton } from '@wordpress/components';
import { useState, useEffect } from '@wordpress/element';
import { useSelect, useDispatch } from '@wordpress/data';
import { createBlock } from '@wordpress/blocks';
import { ENTER, SPACE } from '@wordpress/keycodes';

/**
 * Internal dependencies
 */
import TemplatePreview from './template-preview';
import pageIcon from '../../../../images/stories-editor/add-page-inserter.svg';
import addTemplateIcon from '../../../../images/stories-editor/add-template.svg';
import './edit.css';
import { createSkeletonTemplate, maybeEnqueueFontStyle, isPageBlock } from '../../helpers';

const TemplateInserter = ( props ) => {
	const { storyTemplates, allBlocks, getBlock } = props;

	const [ cachedStoryTemplates, cacheStoryTemplates ] = useState( [] );

	const { __experimentalFetchReusableBlocks: fetchStoryTemplates } = useDispatch( 'core/editor' );
	const { insertBlock } = useDispatch( 'core/block-editor' );

	const {} = useSelect( ( select ) => {
		const {	__experimentalGetReusableBlocks: getReusableBlocks } = select( 'core/editor' );

		return {
			storyTemplates: getReusableBlocks().filter( ( { clientId } ) => isPageBlock( clientId ) ),
			getBlock: select( 'core/block-editor' ).getBlock,
			allBlocks: select( 'core/block-editor' ).getBlocks(),
		};
	}, [] );

	useEffect( () => {
		fetchStoryTemplates();
	}, [ fetchStoryTemplates ] );

	useEffect( () => {
		for ( const template of storyTemplates ) {
			const templateBlock = getBlock( template.clientId );

			if ( ! templateBlock ) {
				continue;
			}

			for ( const innerBlock of templateBlock.innerBlocks ) {
				if ( innerBlock.attributes.ampFontFamily ) {
					maybeEnqueueFontStyle( innerBlock.attributes.ampFontFamily );
				}
			}
		}

		cacheStoryTemplates( storyTemplates );
	}, [ storyTemplates, allBlocks, getBlock ] );

	const onToggle = ( isOpen ) => {
		// Surface toggle callback to parent component
		if ( props.onToggle ) {
			props.onToggle( isOpen );
		}
	};

	return (
		<Dropdown
			className="editor-inserter block-editor-inserter"
			contentClassName="amp-stories__template-inserter__popover is-from-top is-bottom editor-inserter__popover"
			onToggle={ onToggle }
			expandOnMobile
			renderToggle={ ( { onToggle: onClick, isOpen } ) => (
				<IconButton
					icon={ addTemplateIcon( { width: 16, height: 16 } ) }
					label={ __( 'Insert Template', 'amp' ) }
					onClick={ onClick }
					className="editor-inserter__amp-inserter"
					aria-haspopup="true"
					aria-expanded={ isOpen }
				/>
			) }
			renderContent={ ( { onClose } ) => {
				const onSelect = ( item ) => {
					const block = ! item ? createBlock( 'amp/amp-story-page' ) : getBlock( item.clientId );
					const skeletonBlock = createSkeletonTemplate( block );
					insertBlock( skeletonBlock );
					onClose();
				};

				return (
					<div className="amp-stories__editor-inserter__menu">
						<div
							className="amp-stories__editor-inserter__results"
							tabIndex="0"
							role="region"
							aria-label={ __( 'Available templates', 'amp' ) }
						>
							<div role="list" className="editor-block-types-list block-editor-block-types-list">
								<div className="editor-block-preview block-editor-block-preview">
									<IconButton
										icon={ pageIcon( { width: 86, height: 96 } ) }
										label={ __( 'Blank Page', 'amp' ) }
										onClick={ () => {
											onSelect( null );
										} }
										className="amp-stories__blank-page-inserter editor-block-preview__content block-editor-block-preview__content editor-styles-wrapper"
									/>
								</div>
								{ cachedStoryTemplates.map( ( item ) => (
									// see https://github.com/ampproject/amp-wp/issues/2165
									<a // eslint-disable-line jsx-a11y/anchor-is-valid
										key={ `template-preview-${ item.id }` }
										role="button"
										tabIndex="0"
										onClick={ () => {
											onSelect( item );
										} }
										onKeyDown={ ( event ) => {
											if ( includes( [ ENTER, SPACE ], event.keyCode ) ) {
												onSelect( item );
											}
										} }
										className="components-button block-editor-block-preview"
									>
										<TemplatePreview
											item={ item }
										/>
									</a>
								) ) }
							</div>
						</div>
					</div>
				);
			} }
		/>
	);
};

TemplateInserter.propTypes = {
	allBlocks: PropTypes.array,
	onToggle: PropTypes.func,
	storyTemplates: PropTypes.array.isRequired,
	getBlock: PropTypes.func.isRequired,
};

export default TemplateInserter;
