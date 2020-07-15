/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ErrorScreen } from '../error-screen';

/**
 * Catches errors in the application and displays a fallback screen.
 *
 * @see https://reactjs.org/docs/error-boundaries.html
 */
export class ErrorBoundary extends Component {
	static propTypes = {
		children: PropTypes.any,
		exitLink: PropTypes.string,
	}

	constructor( props ) {
		super( props );

		this.state = { error: null };
	}

	componentDidCatch( error ) {
		this.setState( { error } );
	}

	render() {
		const { error } = this.state;

		if ( error ) {
			return (
				<ErrorScreen error={ error } finishLink={ this.props.exitLink } />
			);
		}

		return this.props.children;
	}
}
