/**
 * External dependencies
 */
import { create } from 'react-test-renderer';

/**
 * Internal dependencies
 */
import { StepperBullet } from '..';

describe( 'StepperBullet', () => {
	it( 'matches snapshot when index and active index are 0', () => {
		const wrapper = create( <StepperBullet activePageIndex={ 0 } index={ 0 } /> );
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'matches snapshot when index is 0 and active index is something else', () => {
		const wrapper = create( <StepperBullet activePageIndex={ 1 } index={ 0 } /> );
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'matches snapshot when neither index nor active index are 0 but they are the same', () => {
		const wrapper = create( <StepperBullet activePageIndex={ 1 } index={ 1 } /> );
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );

	it( 'matches snaphnot when neither index nor active index are 0 and they are not the same', () => {
		const wrapper = create( <StepperBullet activePageIndex={ 2 } index={ 1 } /> );
		expect( wrapper.toJSON() ).toMatchSnapshot();
	} );
} );
