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
		exitLinkLabel: PropTypes.string,
		exitLinkUrl: PropTypes.string,
		title: PropTypes.string,
	}

	constructor( props ) {
		super( props );

		this.timeout = null;
		this.state = { error: null };
	}

	componentDidMount() {
		this.mounted = true;
	}

	componentWillUnmount() {
		this.mounted = false;
	}

	componentDidCatch( error ) {
		this.setState( { error } );
	}

	render() {
		const { error } = this.state;
		const { children, exitLinkLabel, exitLinkUrl, title } = this.props;

		if ( error ) {
			return (
				<ErrorScreen
					error={ error }
					finishLinkLabel={ exitLinkLabel }
					finishLinkUrl={ exitLinkUrl }
					title={ title }
				/>
			);
		}

		return children;
	}
}
