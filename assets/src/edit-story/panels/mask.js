/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * Internal dependencies
 */
import { MASKS } from '../masks';
import { Panel, Title, getCommonValue } from './shared';

/* eslint-disable jsx-a11y/no-onchange */
function MaskPanel( { selectedElements, onSetProperties } ) {
	const masks = selectedElements.map( ( { mask } ) => mask );
	const type = masks.includes( undefined ) ? '' : getCommonValue( masks, 'type' );
	const mask = MASKS.find( ( aMask ) => aMask.type === type );

	const onTypeChanged = ( evt ) => {
		const newType = evt.target.value;
		if ( newType === '' ) {
			onSetProperties( { mask: null } );
		} else {
			const newMask = MASKS.find( ( aMask ) => aMask.type === newType );
			onSetProperties( {
				mask: {
					type: newType,
					...newMask.defaultProps,
				},
			} );
		}
	};

	return (
		<Panel onSubmit={ ( event ) => event.preventDefault() }>
			<Title>
				{ 'Mask' }
			</Title>
			<select value={ type } onChange={ onTypeChanged }>
				<option key={ 'none' } value={ '' }>
					{ 'None' }
				</option>
				{ MASKS.map( ( { type: aType, name } ) => (
					<option key={ aType } value={ aType }>
						{ name }
					</option>
				) ) }
			</select>
			<div>
				{ mask && (
					<svg width={ 50 } height={ 50 } viewBox="0 0 1 1">
						<g transform="scale(0.8)" transform-origin="50% 50%">
							<path
								d={ mask.path }
								fill="none"
								stroke="blue"
								strokeWidth={ 2 / 50 } />
						</g>
					</svg>
				) }
			</div>
		</Panel>
	);
}
/* eslint-enable jsx-a11y/no-onchange */

MaskPanel.propTypes = {
	selectedElements: PropTypes.array.isRequired,
	onSetProperties: PropTypes.func.isRequired,
};

export default MaskPanel;
