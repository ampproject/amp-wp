/**
 * External dependencies
 */
import PropTypes from 'prop-types';

/**
 * WordPress dependencies
 */
import { withSafeTimeout } from '@wordpress/compose';
import { Spinner } from '@wordpress/components';
import { Component } from '@wordpress/element';

/**
 * Internal dependencies
 */
import { BlockPreview } from '../';

class TemplatePreview extends Component {
	constructor( ...args ) {
		super( ...args );

		this.state = {
			shouldLoad: false,
		};
	}

	componentDidMount() {
		if ( ! this.state.shouldLoad ) {
			// @todo Look into React Concurrent mode to replace this once it gets available.
			// Set timeout to cause a small latency between loading the templates, otherwise they all try to load instantly and cause a lag.
			this.props.setTimeout( () => {
				this.setState( { shouldLoad: true } );
			}, 100 );
		}
	}

	render() {
		if ( ! this.state.shouldLoad ) {
			return <Spinner />;
		}

		const { item } = this.props;

		return (
			<BlockPreview
				name="core/block"
				attributes={ { ref: item.id } }
			/>
		);
	}
}

TemplatePreview.propTypes = {
	item: PropTypes.shape( {
		id: PropTypes.number.isRequired,
	} ).isRequired,
	setTimeout: PropTypes.func.isRequired,
};

export default withSafeTimeout( TemplatePreview );
