/**
 * Internal dependencies
 */
import StoryPropTypes from '../../../types';
import SaveElement from './saveElement';

function SavePage( { page } ) {
	const { id } = page;
	return (
		<amp-story-page id={ id }>
			<amp-story-grid-layer template="vertical">
				{ page.elements.map( ( element ) => {
					const { id: elId } = element;
					return (
						<SaveElement key={ 'el-' + elId } element={ element } />
					);
				} ) }
			</amp-story-grid-layer>
		</amp-story-page>
	);
}

SavePage.propTypes = {
	page: StoryPropTypes.page.isRequired,
};

export default SavePage;
