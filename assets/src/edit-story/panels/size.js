/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { useEffect, useState } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { Panel, Title, InputGroup, getCommonValue } from './shared';

function SizePanel( { selectedElements, onSetProperties } ) {
	const width = getCommonValue( selectedElements, 'width' );
	const height = getCommonValue( selectedElements, 'height' );
	const keepRatio = getCommonValue( selectedElements, 'keepRatio' );
	const origRatio = getCommonValue( selectedElements, 'origRatio' );
	const isFullbleed = getCommonValue( selectedElements, 'isFullbleed' );
	const [ state, setState ] = useState( { width, height, keepRatio } );
	useEffect( () => {
		setState( { width, height, keepRatio } );
	}, [ width, height, keepRatio ] );
	const handleSubmit = ( evt ) => {
		onSetProperties( state );
		evt.preventDefault();
	};
	return (
		<Panel onSubmit={ handleSubmit }>
			<Title>
				{ 'Size' }
			</Title>
			<InputGroup
				label="Width"
				value={ state.width }
				isMultiple={ width === '' }
				onChange={ ( value ) => {
					const newWidth = isNaN( value ) || value === '' ? '' : parseFloat( value );
					setState( {
						...state,
						width: newWidth,
						// @todo: Move to the reducer once available. This is especially critical
						// because different elements have different aspect ratios.
						height: typeof newWidth === 'number' && keepRatio && typeof origRatio === 'number' ? newWidth / origRatio : height,
					} );
				} }
				postfix="px"
				disabled={ isFullbleed }
			/>
			<InputGroup
				label="Height"
				value={ state.height }
				isMultiple={ height === '' }
				onChange={ ( value ) => {
					const newHeight = isNaN( value ) || value === '' ? '' : parseFloat( value );
					setState( {
						...state,
						height: newHeight,
						// @todo: Move to the reducer once available. This is especially critical
						// because different elements have different aspect ratios.
						width: typeof newHeight === 'number' && keepRatio && typeof origRatio === 'number' ? newHeight * origRatio : width,
					} );
				} }
				postfix="px"
				disabled={ isFullbleed }
			/>
			<InputGroup
				type="checkbox"
				label="Keep ratio"
				value={ state.keepRatio }
				isMultiple={ keepRatio === '' }
				onChange={ ( value ) => {
					if ( value && typeof origRatio === 'number' ) {
						onSetProperties( {
							keepRatio: true,
							// @todo: Move to the reducer once available. This is especially critical
							// because different elements have different aspect ratios.
							width,
							height: width / origRatio,
						} );
					} else {
						onSetProperties( { keepRatio: false } );
					}
				} }
				disabled={ isFullbleed }
			/>
		</Panel>
	);
}

SizePanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default SizePanel;
