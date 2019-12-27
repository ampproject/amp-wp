/**
 * External dependencies
 */
import PropTypes from 'prop-types';
import styled from 'styled-components';

/**
 * Internal dependencies
 */
import InOverlay from '../../components/overlay';

const Container = styled.div`
  position: absolute;
  left: ${ ( { x, width } ) => `${ x + ( ( width - Math.max( width, 165 ) ) / 2 ) }px` };
  top: ${ ( { y, height } ) => `${ y + height + 8 }px` };
  width: ${ ( { width } ) => `${ Math.max( width, 165 ) }px` };
  height: 28px;

  background: ${ ( { theme } ) => theme.colors.bg.v7 };
  border-radius: 4px;

  display: flex;
  flex-direction: row;
  flex-wrap: nowrap;
  align-items: center;
  padding: 0 4px;
`;

const Range = styled.input`
  flex: 1 1;
  margin: 4px;
  min-width: 100px;
  cursor: pointer;
`;

const ResetButton = styled.button`
  flex: 0 0;
  margin-left: 4px;
  height: 20px;
  text-transform: uppercase;
  font-size: 10px;
  color: rgba(255, 255, 255, 0.3);
  background: rgba(255, 255, 255, 0.2);
  border-radius: 4px;
  border: none;
`;

function ScalePanel( { setProperties, width, height, x, y, scale } ) {
	return (
		<InOverlay zIndex={ 11 }>
			<Container x={ x } y={ y } width={ width } height={ height } >
				<Range
					type="range"
					min={ 100 }
					max={ 400 }
					step={ 10 }
					value={ scale }
					onChange={ ( evt ) => setProperties( { scale: evt.target.valueAsNumber } ) }
				/>
				<ResetButton
					onClick={ () => setProperties( { scale: 100 } ) }>
					{ 'Reset' }
				</ResetButton>
			</Container>
		</InOverlay>
	);
}

ScalePanel.propTypes = {
	setProperties: PropTypes.func.isRequired,
	width: PropTypes.number.isRequired,
	height: PropTypes.number.isRequired,
	x: PropTypes.number.isRequired,
	y: PropTypes.number.isRequired,
	scale: PropTypes.number.isRequired,
};

export default ScalePanel;
