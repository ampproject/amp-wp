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
			parentLoaded: false,
		};
	}

	componentDidMount() {
		if ( ! this.state.parentLoaded ) {
			// Set timeout to cause a small latency between loading the templates, otherwise they all try to load instantly and cause a lag.
			this.props.setTimeout( () => {
				this.setState( { parentLoaded: true } );
			}, 100 );
		}
	}

	render() {
		if ( ! this.state.parentLoaded ) {
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
		id: PropTypes.string.isRequired,
	} ).isRequired,
	setTimeout: PropTypes.func.isRequired,
};

export default withSafeTimeout( TemplatePreview );
