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
	constructor() {
		super( ...arguments );

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
		const { isFirst } = this.props;
		if ( ! this.state.shouldLoad ) {
			// Display the spinner for the first template only to avoid overloading.
			return isFirst ? <Spinner /> : null;
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
		id: PropTypes.string.isRequired,
	} ).isRequired,
	setTimeout: PropTypes.func.isRequired,
	isFirst: PropTypes.bool.isRequired,
};

export default withSafeTimeout( TemplatePreview );
