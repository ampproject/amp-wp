/**
 * WordPress dependencies
 */
import { render } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { BlockNavigation, EditorCarousel, Inserter, MediaInserter, Shortcuts, StoryControls } from '../components';

/**
 * Add some additional elements needed to render our custom UI controls.
 */
const renderStoryComponents = () => {
	const editorBlockList = document.querySelector( '.block-editor-block-list__layout' );
	const editorBlockNavigation = document.querySelector( '.block-editor-block-navigation' );

	if ( editorBlockList && ! document.getElementById( 'amp-story-editor' ) ) {
		const ampStoryWrapper = document.createElement( 'div' );
		ampStoryWrapper.id = 'amp-story-editor';

		const blockNavigation = document.createElement( 'div' );
		blockNavigation.id = 'amp-story-block-navigation';

		/**
		 * The intended layout is as follows:
		 *
		 * - Post title
		 * - AMP story wrapper element (needed for overflow styling)
		 * - - Story controls
		 * - - Block list
		 * - - Block navigation
		 * - - Carousel controls
		 */
		editorBlockList.parentNode.replaceChild( ampStoryWrapper, editorBlockList );

		if ( ! document.getElementById( 'amp-story-controls' ) ) {
			const storyControls = document.createElement( 'div' );
			storyControls.id = 'amp-story-controls';

			ampStoryWrapper.appendChild( storyControls );

			render(
				<StoryControls />,
				storyControls,
			);
		}

		ampStoryWrapper.appendChild( editorBlockList );

		ampStoryWrapper.appendChild( blockNavigation );

		render(
			<BlockNavigation />,
			blockNavigation,
		);

		if ( ! document.getElementById( 'amp-story-editor-carousel' ) ) {
			const editorCarousel = document.createElement( 'div' );
			editorCarousel.id = 'amp-story-editor-carousel';

			ampStoryWrapper.appendChild( editorCarousel );

			render(
				<EditorCarousel />,
				editorCarousel,
			);
		}
	}

	if ( editorBlockNavigation ) {
		if ( ! document.getElementById( 'amp-story-shortcuts' ) ) {
			const shortcuts = document.createElement( 'div' );
			shortcuts.id = 'amp-story-shortcuts';

			editorBlockNavigation.parentNode.parentNode.insertBefore( shortcuts, editorBlockNavigation.parentNode.nextSibling );

			render(
				<Shortcuts />,
				shortcuts,
			);
		}

		if ( ! document.getElementById( 'amp-story-media-inserter' ) ) {
			const mediaInserter = document.createElement( 'div' );
			mediaInserter.id = 'amp-story-media-inserter';

			editorBlockNavigation.parentNode.parentNode.insertBefore( mediaInserter, editorBlockNavigation.parentNode.nextSibling );

			render(
				<MediaInserter />,
				mediaInserter,
			);
		}

		const customInserter = document.createElement( 'div' );
		customInserter.id = 'amp-story-inserter';

		editorBlockNavigation.parentNode.parentNode.replaceChild( customInserter, editorBlockNavigation.parentNode.parentNode.firstChild );

		render(
			<Inserter position="bottom right" />,
			customInserter,
		);

		editorBlockNavigation.remove();
	}

	// Prevent WritingFlow component from focusing on last text field when clicking below the carousel.
	const writingFlowClickRedirectElement = document.querySelector( '.block-editor-writing-flow__click-redirect' );
	if ( writingFlowClickRedirectElement ) {
		writingFlowClickRedirectElement.remove();
	}
};

export default renderStoryComponents;
