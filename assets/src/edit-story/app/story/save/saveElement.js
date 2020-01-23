/**
 * Internal dependencies
 */
import StoryPropTypes from '../../../types';
import { getDefinitionForType } from '../../../elements';
import { getBox } from '../../../units/dimensions';

function SaveElement( { element } ) {
	const { id, type } = element;

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const { Save } = getDefinitionForType( type );

	// eslint-disable-next-line @wordpress/no-unused-vars-before-return
	const { x, y, width, height, rotationAngle } = getBox( element, 100, 100 );

	return (
		<div
			id={ 'el-' + id }
			style={ {
				position: 'absolute',
				left: `${ x }%`,
				top: `${ y }%`,
				width: `${ width }%`,
				height: `${ height }%`,
				transform: rotationAngle ? `rotate(${ rotationAngle }deg)` : null,
			} }>
			<Save element={ element } />
		</div>
	);
}

SaveElement.propTypes = {
	element: StoryPropTypes.element.isRequired,
};

export default SaveElement;
