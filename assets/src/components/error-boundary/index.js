/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { Component, createContext } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { ErrorScreen } from '../error-screen';

export const ErrorContext = createContext();

/**
 * Catches errors in the application and displays a fallback screen.
 *
 * @see https://reactjs.org/docs/error-boundaries.html
 */
export class ErrorBoundary extends Component {
	static propTypes = {
		children: PropTypes.any,
		exitLink: PropTypes.string,
		fullScreen: PropTypes.bool,
	}

	constructor( props ) {
		super( props );

		this.timeout = null;
		this.state = { error: null };

		this.clearError = this.clearError.bind( this );
	}

	componentDidMount() {
		this.mounted = true;
	}

	componentWillUnmount() {
		this.mounted = false;
	}

	componentDidUpdate() {
		if ( this.state.error ) {
			global.setTimeout( this.clearError, 20000 );
		}
	}

	componentDidCatch( error ) {
		this.setState( { error } );
	}

	shouldComponentUpdate( nextProps, nextState ) {
		return this.state.error !== nextState.error;
	}

	clearError() {
		if ( ! this.props.fullScreen && this.mounted ) {
			this.setState( { error: null } );
		}
	}

	render() {
		const { error } = this.state;
		const { fullScreen } = this.props;

		if ( error && fullScreen ) {
			return (
				<ErrorScreen error={ error } finishLink={ this.props.exitLink } />
			);
		}

		return (
			<ErrorContext.Provider value={ error }>
				{ this.props.children }
			</ErrorContext.Provider>
		);
	}
}
